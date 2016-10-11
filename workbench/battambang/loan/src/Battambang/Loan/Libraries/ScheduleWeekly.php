<?php
namespace Battambang\Loan\Libraries;

use Battambang\Cpanel\WorkDay;
use Battambang\Loan\LookupValue;
use Battambang\Loan\Holiday;
use Carbon;
use DB;

class ScheduleWeekly
{
    public function make($loanAcc, $activatedDate)
    {
        $data = DB::table('view_schedule')->where('ln_disburse_client_id', '=', $loanAcc)->first();
        //var_dump($data); exit;
        // General
        $disburseDate = $data->disburse_date;
        //echo 'Disburse Date: ' . $disburseDate . '<br>';
        $disburseDate = Carbon::createFromFormat('Y-m-d', $disburseDate);
        $temDisburseDate = $disburseDate->copy();

        // Currency
        $currency = $data->cp_currency_id; // 1-KHR, 2-USD, 3-THB
        $loanAmount = $data->ln_disburse_client_amount; // USD
        $temLoanAmount = $loanAmount; // USD
        //echo 'Loan Amount: ' . $loanAmount . '<br>';


        // Repayment schedule
        $repayFrequency = $data->ln_lv_repay_frequency; // 2(3-Weekly, 4-Monthly)
        $numInstallment = $data->num_installment; // 10 weeks
        $installmentFrequency = $data->installment_frequency; // Every 1 week
//        $numPayment = 10; // 10 times
        $numPayment = $data->num_payment; // ... times

        // Interest rate
        $interestType = $data->ln_lv_interest_type; // 4(8-Declining Balance, 9-Flat/Fixed)
        $interestRate = $data->interest_rate / 100; // of (%)
        $interestRateInDay = $interestRate / 7;

        $installPrinFrequency = $data->installment_principal_frequency; // Every 1 times <= $numPayment
        $numPaymentPrin = ceil($numPayment / $installPrinFrequency);
        $installPrinPercentage = $data->installment_principal_percentage / 100; // of (%)
        $installPrinAmount = \Currency::round($currency, ($loanAmount / $numPaymentPrin) * $installPrinPercentage);

        if($interestType==129){
           $tmpRate = 1-pow((1+$interestRate * $installmentFrequency),-($numPaymentPrin));
           $installPrinAmount = \Currency::round($currency,($loanAmount*$interestRate * $installmentFrequency)/$tmpRate);
            if($data->round_schedule =='Y'){
                $installPrinAmount = floor($installPrinAmount);
            }
        }

        $meetingDay = $data->ln_lv_meeting_schedule; // 12-Week(...-None, 27-Mon, 28-Tue, 29-Wed, 30-Thu, 31-Fri, 32-Sat)
//        if (!empty($meetingDay)) {
        if ($meetingDay != '128') {
            $meetingDay = LookupValue::find($meetingDay)->code;
            // Calculate diff meeting day with disburse day
            $diffMeetingDay = $meetingDay - $temDisburseDate->dayOfWeek;
            if ($diffMeetingDay != 0) {
                $temDisburseDate = $temDisburseDate->addDays($diffMeetingDay);
            }
        }
        $holidayRule = $data->ln_lv_holiday_rule; // 3(5-Same, 6-Next, 7-Previous)

        // Fee
        $feeType = $data->ln_lv_fee_type; // 6(12-At disbursement, 13-First Repayment, 14-Installment Principal)
        $feeCalType = $data->ln_lv_calculate_type; // 7(15-Amount, 16-Percentage)
        $feeAmount = $data->ln_fee_amount; // of (%)
        $feePercentageOf = $data->ln_lv_percentage_of; // empty(if $feeCalType=15), 8(17-Loan Amount, 18-Loan Amount+Interest, 19-Interest)

        $feeAtDisburseCharge = 0;
        $feeAtFirstRepaymentCharge = 0;
        $feeAtInstallPrincipalCharge = 0;
        switch ($feeType) {
            case 12:
                if ($feeCalType == 15) { // For amount
                    $feeAtDisburseCharge = $feeAmount;
                } else { // For percentage
                    if ($feePercentageOf == 17) {
                        $feeAtDisburseCharge = \Currency::round($currency, $loanAmount * $feeAmount / 100);
                    }
                }
                break;
        }


        // num installment can closing
        $percentageInstallmentForClosing = $data->percentage_installment;
        $percentageInterestRemainderForClosing = $data->percentage_interest_remainder;
        $numInstallmentForClosing = ceil($numPayment * $percentageInstallmentForClosing / 100);

        //-----------------------------------------------------------------


        $temInstallmentFrequency = $installmentFrequency;
        $temInstallPrinFrequency = $installPrinFrequency;

        $dueDate = array(); // Due date for work day
        $numOfDays = array(); // number of days
        $principalPayment = array();
        $interestPayment = array();
        $feePayment = array();
        $principalBalance = array();
        $schedule = array();
        $tmpP=0;

        $tmpBefRound =0;
        $tmpAftRound = 0;

        $tmpBefRoundPri=0;
        $tmpAftRoundPri=0;
        $tmpBal = $temLoanAmount;
        $tmpDesc = 0;

//        for ($i = 1; $i <= $numPayment; $i++) {
        for ($i = 0; $i <= $numPayment; $i++) {
            if ($i == 0) {
                $dueDate[$i] = $disburseDate->toDateString();
                $numOfDays[$i] = 0;
                $principalPayment[$i] = 0;
                $interestPayment[$i] = 0;
                $feePayment[$i] = $feeAtDisburseCharge;
                $principalBalance[$i] = $loanAmount;
            } else {
                $temDueDate = $temDisburseDate->copy()->addWeeks($temInstallmentFrequency);
                // if set first due date
                if($this->_isDate($data->first_due_date)){
                    $firstDueDate = Carbon::createFromFormat('Y-m-d', $data->first_due_date);
                    if($i==1){
                        $temDueDate = $firstDueDate;
                    }
                    if($i>1 && $data->ln_lv_meeting_schedule==125){
                        $temDueDate = $firstDueDate->copy()->addWeeks($temInstallmentFrequency - $installmentFrequency);
                    }
                    if($i>1 && $data->ln_lv_meeting_schedule!=125){
                        $temDueDate = $temDisburseDate->copy()->addWeeks($temInstallmentFrequency - $installmentFrequency + 1);
                    }
                }


                $dueDate[$i] = $this->holidayCheck($temDueDate->toDateString(), $holidayRule);

                // Calculate num of days
                $numOfDays[$i] = Carbon::createFromFormat('Y-m-d', $dueDate[$i - 1])->diffInDays(Carbon::createFromFormat('Y-m-d', $dueDate[$i]));

                // Calculate interest amount for payment
                $interestPayment[$i] = \Currency::round($currency, ($temLoanAmount * $interestRateInDay * $numOfDays[$i]));

                //Check if fixed interest
                if($interestType==9){
                    $interestPayment[$i] = \Currency::round($currency, ($loanAmount * $interestRate * $installmentFrequency));
                }
                //Check if Interest rat not Mortagag
                if($interestType!=129) {
                    // Calculate install principal for payment
                    if ($i == $temInstallPrinFrequency) {
                        if ($i != $numPayment) {
                            $principalPayment[$i] = $installPrinAmount;
                            $temLoanAmount -= $principalPayment[$i];
                            $temInstallPrinFrequency += $installPrinFrequency;

                            if ($temInstallPrinFrequency > $numPayment) {
                                $temInstallPrinFrequency = $numPayment;
                            }
                        } else {
                            $principalPayment[$i] = $temLoanAmount;
                            $temLoanAmount = 0.00;
                        }
                    } else {
                        $principalPayment[$i] = 0.00;
                    }

                    // Calculate principal balance
                    $principalBalance[$i] = $temLoanAmount;
                }else{
                    //NORMAL
                    $interestPayment[$i] = \Currency::round($currency, ($temLoanAmount * $interestRateInDay * $numOfDays[$i]));
                    // MPNT
                    //$interestPayment[$i] = \Currency::round($currency,$temLoanAmount * $interestRate * $installmentFrequency);
                    // Calculate install principal for payment
                    if ($i == $temInstallPrinFrequency) {
                        if ($i != $numPayment) {
                            $principalPayment[$i] = $installPrinAmount - $interestPayment[$i];
                            $temLoanAmount -= $principalPayment[$i];
                            $temInstallPrinFrequency += $installPrinFrequency;

                            if ($temInstallPrinFrequency > $numPayment) {
                                $temInstallPrinFrequency = $numPayment;
                            }
                            // interest > total payment
                            if($interestPayment[$i] > $installPrinAmount){
                                $interestPayment[$i] = 0;
                            }
                        } else {
                            //$principalPayment[$i] = $temLoanAmount;
                            $principalPayment[$i] = $loanAmount - $tmpP;
                            //$interestPayment[$i] =  $installPrinAmount-$principalPayment[$i];
                            $temLoanAmount = 0.00;
                        }
                    } else {
                        $principalPayment[$i] = 0.00;
                    }

                    // Calculate principal balance
                    $principalBalance[$i] = $temLoanAmount;

                    $principalPayment[$i] = \Currency::round($currency,$principalPayment[$i]);
                    $interestPayment[$i] = \Currency::round($currency,$interestPayment[$i]);
                    $principalBalance[$i] = \Currency::round($currency,$principalBalance[$i]);
                    $tmpP+=$principalPayment[$i];
                }
                // Check installmentFrequency
                $temInstallmentFrequency += $installmentFrequency;
                while ($temInstallmentFrequency > $numInstallment) {
                    $temInstallmentFrequency -= 1;
                }

                // Calculate fee payment
                $feePayment[$i] = 0.00;
            }

            // Check num installment for closing
            $closing = ($i == $numInstallmentForClosing) ? 'closing' : '';

            $schedule[] = array(
                'due_date' => $dueDate[$i],
                'num_day' => $numOfDays[$i],
                'principal' => $principalPayment[$i],
                'interest' => $interestPayment[$i],
                'fee' => $feePayment[$i],
                'balance' => $principalBalance[$i],
                'ln_disburse_client_id' => $data->ln_disburse_client_id,
                'closing' => $closing,
            );
        }

        return $schedule;
    }

    private function holidayCheck($date, $holidayRule)
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);
        $newDate = $date->toDateString();
        switch ($holidayRule) {
            case 5: // Same
                $newDate = $newDate;
                break;
            case 6: // Next
                // Get holidayInWeek
                $holidayInDay = $this->holidayInDay($date->toDateString());

                // Check next
                $newDate = $this->toNext($date->toDateString(), $holidayInDay);

                // Check previous if next is false
                if ($newDate == false) {
                    $newDate = $this->toPrevious($date->toDateString(), $holidayInDay);
                    if ($newDate == false) {
                        $newDate = $newDate;
                    }
                }
                // End check next for previous is false
                break;
            case 7: // Previous
                // Get holidayInWeek
                $holidayInDay = $this->holidayInDay($date->toDateString());

                // Check previous
                $newDate = $this->toPrevious($date->toDateString(), $holidayInDay);
                // Check next if previous is false
                if ($newDate == false) {
                    $newDate = $this->toNext($date->toDateString(), $holidayInDay);
                    if ($newDate == false) {
                        $newDate = $newDate;
                    }
                }
                // End check next if previous is false
                break;
        }

        return $newDate;
    }

    private function toNext($date, $holidayInDay)
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);

        $temDate = $date->copy();
        for ($i = $date->day; $i <= $date->endOfWeek()->day; $i++) {
            if (!in_array($temDate->day, $holidayInDay)) {
                return $temDate->toDateString();
            }
            $temDate = $temDate->addDay();
        }
        return false;
        /*$date = Carbon::createFromFormat('Y-m-d', $date);

        // Work Day (validate with activatedDate)
        $workDay = $this->_getWorkDay($date);

        $temDate = $date->copy();
        // Check work day: MF-Mon to Fri, MS-Mon to Sat
        $maxDayOfWeek = 5; // Workday until Fri
        if ($workDay->work_week == 'MS') {
            $maxDayOfWeek = 6; // Workday until Sat
        }
        if ($workDay->work_week == 'MSD') {
            $maxDayOfWeek = 7; // Workday until Sat
        }
        for ($i = $date->dayOfWeek; $i <= $maxDayOfWeek; $i++) {
            if($temDate->day<=$holidayInDay){
                return $temDate->toDateString();
            }
            $temDate = $temDate->addDay();
        }
        return false;*/
    }

    private function toPrevious($date, $holidayInDay)
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);

        $temDate = $date->copy();
        for ($i = $date->dayOfWeek; $i >= 1; $i--) { // $i > 1-Monday
            if (!in_array($temDate->day, $holidayInDay)) {
                return $temDate->toDateString();
            }
            $temDate = $temDate->subDay();
        }
        return false;
    }

    private function holidayInDay($date)
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);

        // Work Day (validate with activatedDate)
        $workDay = $this->_getWorkDay($date);

        // Check work day: MF-Mon to Fri, MS-Mon to Sat
        $startWorkDay = $date->copy()->startOfWeek();
        $endWorkDay = $date->copy()->endOfWeek();
        $holiday = array();
        $holiday[] = $date->copy()->endOfWeek()->day;
        if ($workDay->work_week == 'MF') {
            $endWorkDay = $endWorkDay->subDays(2);
            $holiday[] = $date->copy()->endOfWeek()->subDays(1)->day;
        }

        if ($workDay->work_week == 'MS') {
            $endWorkDay = $endWorkDay->subDays(1);
            $holiday[] = $date->copy()->endOfWeek()->day;
        }

        if ($workDay->work_week == 'MSD') {
            $endWorkDay = $endWorkDay;
            $holiday[] = $date->copy()->endOfWeek()->day;
        }

        $getHoliday = Holiday::whereBetween('holiday_date', array($startWorkDay->toDateString(), $endWorkDay->toDateString()))->get();
        foreach ($getHoliday as $value) {
            $holiday[] = Carbon::createFromFormat('Y-m-d', $value->holiday_date)->day;
        }
        sort($holiday);
        return $holiday;
    }

    private function _getWorkDay($activatedDate)
    {
        $getData = WorkDay::where('activated_at', '<=', $activatedDate)
            ->orderBy('activated_at', 'DESC')
            ->limit(1)
            ->first();

        return $getData;
    }

    private function _isDate($val)
    {
        if (in_array($val, array('', '0000-00-00'))) {
            return false;
        } else {
            return true;
        }
    }
}
