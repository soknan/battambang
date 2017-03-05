<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2/18/14
 * Time: 4:58 PM
 */

namespace Battambang\Loan\Libraries;

use Battambang\Loan\Schedule;
use Battambang\Loan\ScheduleDt;

class RepaymentSchedule {

    public $_activated_at;
    public $_project_interest;
    public $_num_borrow_day;
    public $_maturity_date;
    public $_fee;
    public $_can_closing;
    public $_activated_num_installment;
    public $_balance_principal;


    public function save($scheduleData,$dis_client,$date){
        Schedule::where('ln_disburse_client_id', '=', $dis_client)->delete();
        foreach ($scheduleData as $key => $value) {
            $schedule = new Schedule();
            $scheduleDt = new ScheduleDt();

            $this->_num_borrow_day += $value['num_day'];
            $this->_project_interest += $value['interest'];
            $this->_maturity_date = $value['due_date'];
            $this->_fee += $value['fee'];
            $this->_activated_at = $date;
            $this->_activated_num_installment = 0;


            if ($value['closing'] == 'closing') $this->_can_closing = $key;
            //write to schedule
            $schedule->id = \AutoCode::make('ln_schedule', 'id', \UserSession::read()->sub_branch . '-', 10);
            $schedule->index = $key;
            $schedule->due_date = $value['due_date'];
            $schedule->num_day = $value['num_day'];
            $schedule->ln_disburse_client_id = $value['ln_disburse_client_id'];
            $schedule->cp_office_id = \UserSession::read()->sub_branch;
            //write to schedule detail
            $scheduleDt->id = \AutoCode::make('ln_schedule_dt', 'id', \UserSession::read()->sub_branch . '-', 10);
            $scheduleDt->activated_at = $date;
            $scheduleDt->principal = $value['principal'];
            $scheduleDt->interest = $value['interest'];
            $scheduleDt->fee = $value['fee'];
            $scheduleDt->balance = $value['balance'];
            $scheduleDt->ln_schedule_id = $schedule->id;
            $scheduleDt->cp_office_id = \UserSession::read()->sub_branch;

            if($key==0){
                $this->_balance_principal = $value['balance'];
            }

            $schedule->save();
            $scheduleDt->save();
        }
    }
} 