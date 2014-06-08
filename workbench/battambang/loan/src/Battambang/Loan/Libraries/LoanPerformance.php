<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2/17/14
 * Time: 10:28 AM
 */

namespace Battambang\Loan\Libraries;

use Battambang\Cpanel\Libraries\Currency;
use Battambang\Cpanel\Libraries\UserSession;
use Battambang\Loan\Disburse;
use Battambang\Loan\DisburseClient;
use Battambang\Loan\Penalty;
use Battambang\Loan\PenaltyClosing;
use Battambang\Loan\Perform;
use Battambang\Loan\Schedule;
use Battambang\Loan\ScheduleDt;
use Carbon;

class LoanPerformance
{
    public $_id;
    public $_disburse_client_id;
    public $_last_perform_date;
    public $_num_borrow_day;
    public $_project_interest;
    public $_maturity_date;
    public $_can_closing;
    public $_activated_at;
    public $_perform_type;
    public $_activated_num_installment;
    public $_due;
    public $_new_due;
    public $_due_closing;
    public $_next_due;
    public $_arrears;
    public $_repayment;
    public $_current_product_status=1;
    public $_current_product_status_date;
    public $_current_product_status_principal;
    public $_balance_principal;
    public $_balance_interest;
    public $_disburse;
    public $_accru_int;
    public $error='';

    public function __construct()
    {
        $this->_accru_int=0;
        $this->_activated_num_installment =0;
        $this->_last_perform_date = '';
        $this->_num_borrow_day = 0;
        $this->_project_interest = 0;
        $this->_maturity_date = '';
        $this->_can_closing = 0;
        $this->_activated_at = '';
        $this->_perform_type = '';

        $this->_due = array(
            'date' => '',
            'num_day'=>0,
            'principal' => 0,
            'interest' => 0,
            'fee' => 0,
            'penalty' => 0);
        $this->_last_due = array(
            'date' => '',
            'num_day'=>0,
            'principal' => 0,
            'interest' => 0,
            'fee' => 0,
            'penalty' => 0);

        $this->_new_due = array(
            'date' => '',
            'num_day' => 0,
            'num_installment' => 0,
            'product_status' => 1,
            'product_status_date' => '',
            'principal' => 0,
            'interest' => 0,
            'fee' => 0,
            'penalty' => 0,
        );

        $this->_due_closing = array(
            'interest_closing' => 0,
            'principal_closing' => 0
        );

        $this->_next_due = array(
            'date' => '',
            'principal' => 0,
            'interest' => 0,
            'fee' => 0,
            'penalty' => 0);

        $this->_arrears = array(
            'last' => array(
                'date' => '',
                'num_day' => 0,
                'num_installment' => 0,
                'principal' => 0,
                'interest' => 0,
                'fee' => 0,
                'penalty' => 0),
            'cur' => array(
                'date' => '',
                'num_day' => 0,
                'num_installment' => 0,
                'principal' => 0,
                'interest' => 0,
                'fee' => 0,
                'penalty' => 0)
        );

        $this->_repayment = array(
            'last' => array(
                'date' => '',
                'voucher_id'=>'',
                'status' => 1,
                'num_installment'=>0,
                'type' => '',
                'principal' => 0,
                'interest' => 0,
                'fee' => 0,
                'penalty' => 0),
            'cur' => array(
                'date' => '',
                'voucher_id'=>'',
                'status' => 1,
                'num_installment'=>0,
                'type' => '',
                'principal' => 0,
                'interest' => 0,
                'fee' => 0,
                'penalty' => 0)
        );

        //$this->_current_product_status = 1;
    }

    public function save()
    {
        $perform = new Perform();
        $perform->id = \AutoCode::make('ln_perform', 'id', \UserSession::read()->sub_branch . '-', 10);
        $perform->ln_disburse_client_id = $this->_disburse_client_id;
        $perform->activated_at = $this->_activated_at;
        $perform->activated_num_installment = $this->_activated_num_installment;
        $perform->perform_type = $this->_perform_type;
        $perform->project_interest = $this->_project_interest;
        $perform->num_borrowing_day = $this->_num_borrow_day;
        $perform->maturity_date = $this->_maturity_date;
        $perform->num_installment_can_closing = $this->_can_closing;

        $perform->arrears_date = $this->_arrears['cur']['date'];
        $perform->arrears_num_day = $this->_arrears['cur']['num_day'];
        $perform->arrears_num_installment = $this->_arrears['cur']['num_installment'];
        $perform->arrears_principal = $this->_arrears['cur']['principal'];
        $perform->arrears_interest = $this->_arrears['cur']['interest'];
        $perform->arrears_fee = $this->_arrears['cur']['fee'];
        $perform->arrears_penalty = $this->_arrears['cur']['penalty'];

        $perform->last_arrears_date = $this->_arrears['last']['date'];
        $perform->last_arrears_num_day = $this->_arrears['last']['num_day'];
        $perform->last_arrears_num_installment = $this->_arrears['last']['num_installment'];
        $perform->last_arrears_principal = $this->_arrears['last']['principal'];
        $perform->last_arrears_interest = $this->_arrears['last']['interest'];
        $perform->last_arrears_fee = $this->_arrears['last']['fee'];
        $perform->last_arrears_penalty = $this->_arrears['last']['penalty'];

        $perform->new_due_date = $this->_new_due['date'];
        $perform->new_due_num_day = $this->_new_due['num_day'];

        $perform->new_due_product_status = $this->_new_due['product_status'];
        $perform->new_due_product_status_date = $this->_new_due['product_status_date'];

        $perform->new_due_num_installment = $this->_new_due['num_installment'];
        $perform->new_due_principal = $this->_new_due['principal'];
        $perform->new_due_interest = $this->_new_due['interest'];
        $perform->new_due_fee = $this->_new_due['fee'];
        $perform->new_due_penalty = $this->_new_due['penalty'];

        $perform->new_due_interest_closing = $this->_due_closing['interest_closing'];
        $perform->new_due_principal_closing = $this->_due_closing['principal_closing'];

        $perform->due_date = $this->_due['date'];
        $perform->due_num_day = $this->_due['num_day'];
        $perform->due_principal = $this->_due['principal'];
        $perform->due_interest = $this->_due['interest'];
        $perform->due_fee = $this->_due['fee'];
        $perform->due_penalty = $this->_due['penalty'];

        $perform->next_due_date = $this->_next_due['date'];
        $perform->next_due_principal = $this->_next_due['principal'];
        $perform->next_due_interest = $this->_next_due['interest'];
        $perform->next_due_fee = $this->_next_due['fee'];
        $perform->next_due_penalty = $this->_next_due['penalty'];

        $perform->repayment_date = $this->_repayment['cur']['date'];
        $perform->repayment_voucher_id = $this->_repayment['cur']['voucher_id'];
        $perform->repayment_status = $this->_repayment['cur']['status'];
        $perform->repayment_principal = $this->_repayment['cur']['principal'];
        $perform->repayment_interest = $this->_repayment['cur']['interest'];
        $perform->repayment_fee = $this->_repayment['cur']['fee'];
        $perform->repayment_penalty = $this->_repayment['cur']['penalty'];
        $perform->repayment_type = $this->_repayment['cur']['type'];

        $perform->last_repayment_date = $this->_repayment['last']['date'];
        $perform->last_repayment_voucher_id = $this->_repayment['last']['voucher_id'];
        $perform->last_repayment_status = $this->_repayment['last']['status'];
        $perform->last_repayment_principal = $this->_repayment['last']['principal'];
        $perform->last_repayment_interest = $this->_repayment['last']['interest'];
        $perform->last_repayment_fee = $this->_repayment['last']['fee'];
        $perform->last_repayment_penalty = $this->_repayment['last']['penalty'];
        $perform->last_repayment_type = $this->_repayment['last']['type'];

        $perform->current_product_status = $this->_current_product_status;
        $perform->current_product_status_date = $this->_current_product_status_date;
        $perform->current_product_status_principal = $this->_current_product_status_principal;

        $perform->balance_principal = $this->_balance_principal;
        $perform->balance_interest = $this->_balance_interest;

        $perform->save();
    }

    public function get($disClient, $activatedAt)
    {
        $this->_disburse_client_id = $disClient;
        $this->_activated_at = $activatedAt;
        $this->_disburse = $this->_getDisburse();

        $data = Perform::where('ln_disburse_client_id', '=', $this->_disburse_client_id)
            ->where('activated_at', '<=',$this->_activated_at)
            ->orderBy('id', 'DESC')->limit(1)->get();

        if (count($data) > 0) {
            foreach ($data as $row) {
                $this->_id = $row->id;
                $this->_disburse_client_id = $row->ln_disburse_client_id;
                $this->_activated_num_installment = $row->activated_num_installment;
                $this->_perform_type = $row->perform_type;
                $this->_project_interest = $row->project_interest;
                $this->_num_borrow_day = $row->num_borrowing_day;
                $this->_maturity_date = $row->maturity_date;
                $this->_can_closing = $row->num_installment_can_closing;

                $this->_arrears['last']['date'] = $row->last_arrears_date;
                $this->_arrears['last']['num_day'] = $row->last_arrears_num_day;
                $this->_arrears['last']['num_installment'] = $row->last_arrears_num_installment;
                $this->_arrears['last']['principal'] = $row->last_arrears_principal;
                $this->_arrears['last']['interest'] = $row->last_arrears_interest;
                $this->_arrears['last']['fee'] = $row->last_arrears_fee;
                $this->_arrears['last']['penalty'] = $row->last_arrears_penalty;

                $this->_arrears['cur']['date'] = $row->arrears_date;
                $this->_arrears['cur']['num_day'] = $row->arrears_num_day;
                $this->_arrears['cur']['num_installment'] = $row->arrears_num_installment;
                $this->_arrears['cur']['principal'] = $row->arrears_principal;
                $this->_arrears['cur']['interest'] = $row->arrears_interest;
                $this->_arrears['cur']['fee'] = $row->arrears_fee;
                $this->_arrears['cur']['penalty'] = $row->arrears_penalty;

                $this->_new_due['date'] = $row->new_due_date;
                $this->_new_due['num_day'] = $row->new_due_num_day;

                $this->_new_due['product_status'] = $row->new_due_product_status;
                $this->_new_due['product_status_date'] = $row->new_due_product_status_date;

                $this->_new_due['num_installment'] = $row->new_due_num_installment;
                $this->_new_due['principal'] = $row->new_due_principal;
                $this->_new_due['interest'] = $row->new_due_interest;
                $this->_new_due['fee'] = $row->new_due_fee;
                $this->_new_due['penalty'] = $row->new_due_penalty;

                $this->_due_closing['interest_closing'] = $row->new_due_interest_closing;
                $this->_due_closing['principal_closing'] = $row->new_due_principal_closing;

                $this->_due['date'] = $row->due_date;
                $this->_due['num_day'] = $row->due_num_day;
                $this->_due['principal'] = $row->due_principal;
                $this->_due['interest'] = $row->due_interest;
                $this->_due['fee'] = $row->due_fee;
                $this->_due['penalty'] = $row->due_penalty;

                $this->_next_due['date'] = $row->next_due_date;
                $this->_next_due['principal'] = $row->next_due_principal;
                $this->_next_due['interest'] = $row->next_due_interest;
                $this->_next_due['fee'] = $row->next_due_fee;
                $this->_next_due['penalty'] = $row->next_due_penalty;

                $this->_repayment['cur']['date'] = $row->repayment_date;
                $this->_repayment['cur']['voucher_id'] = $row->repayment_voucher_id;
                $this->_repayment['cur']['status'] = $row->repayment_status;
                $this->_repayment['cur']['principal'] = $row->repayment_principal;
                $this->_repayment['cur']['interest'] = $row->repayment_interest;
                $this->_repayment['cur']['fee'] = $row->repayment_fee;
                $this->_repayment['cur']['penalty'] = $row->repayment_penalty;
                $this->_repayment['cur']['type'] = $row->repayment_type;

                $this->_repayment['last']['date'] = $row->last_repayment_date;
                $this->_repayment['last']['voucher_id'] = $row->repayment_voucher_id;
                $this->_repayment['last']['status'] = $row->last_repayment_status;
                $this->_repayment['last']['principal'] = $row->last_repayment_principal;
                $this->_repayment['last']['interest'] = $row->last_repayment_interest;
                $this->_repayment['last']['fee'] = $row->last_repayment_fee;
                $this->_repayment['last']['penalty'] = $row->last_repayment_penalty;
                $this->_repayment['last']['type'] = $row->last_repayment_type;

                $this->_current_product_status = $row->current_product_status;
                $this->_current_product_status_date = $row->current_product_status_date;
                $this->_current_product_status_principal = $row->current_product_status_principal;

                $this->_balance_principal = $row->balance_principal;
                $this->_balance_interest = $row->balance_interest;

                //Fee
                if($this->_arrears['cur']['fee'] >0){

                    return $this;
                }

                //Return Write Off
                if($this->_perform_type == 'writeoff'){
                    return $this;
                }

                if($this->_repayment['cur']['type'] == 'closing' or $this->_repayment['cur']['type'] == 'penalty'){
                    if($this->_arrears['cur']['penalty'] == 0){
                        $this->error ='Your Current Account Already Closing on '.$row->activated_at;
                        $this->_repayment['cur']['type'] = 'normal';
                        //$this->_due['date'] = '';
                        $this->_due['num_day'] = 0;
                        $this->_due['principal'] = 0;
                        $this->_due['interest'] = 0;
                        $this->_due['fee'] = 0;
                        $this->_due['penalty'] = 0;

                        $this->_new_due['date'] = '';
                        $this->_new_due['num_day'] = 0;
                        $this->_new_due['num_installment'] = 0;
                        $this->_new_due['principal'] = 0;
                        $this->_new_due['interest'] = 0;
                        $this->_new_due['fee'] = 0;
                        $this->_new_due['penalty'] = 0;
                        //$this->__construct();
                        return $this;
                    }else{
                        $this->error ='Your Current Account Already Closing, But you still have penalty. You must choose Penalty status.';
                        $this->_repayment['cur']['type'] = 'penalty';
                        //$this->_due['date'] = '';
                        $this->_due['num_day'] = 0;
                        $this->_due['principal'] = 0;
                        $this->_due['interest'] = 0;
                        $this->_due['fee'] = 0;
                        $this->_due['penalty'] = 0;

                        $this->_new_due['date'] = '';
                        $this->_new_due['num_day'] = 0;
                        $this->_new_due['num_installment'] = 0;
                        $this->_new_due['principal'] = 0;
                        $this->_new_due['interest'] = 0;
                        $this->_new_due['fee'] = 0;
                        $this->_new_due['penalty'] = 0;
                        return $this;
                    }
                }

                if ($this->_isEqualDate($this->_activated_at, $row->activated_at)) {
                    if($this->_isDate($this->_arrears['cur']['date'])){

                        $this->error = 'Your Current Account has Arrears on '.$this->_arrears['cur']['date'].'';
                        $this->_arrears['last']['date'] = $this->_arrears['cur']['date'];
                        $this->_arrears['last']['num_day'] = $this->_arrears['cur']['num_day'];
                        $this->_arrears['last']['num_installment'] = $this->_arrears['cur']['num_installment'];
                        $this->_arrears['last']['principal'] = $this->_arrears['cur']['principal'];
                        $this->_arrears['last']['interest'] = $this->_arrears['cur']['interest'];
                        $this->_arrears['last']['fee'] = $this->_arrears['cur']['fee'];
                        $this->_arrears['last']['penalty'] = $this->_arrears['cur']['penalty'];

                        $this->_due['date'] = '';
                        $this->_due['num_day'] = 0;
                        $this->_due['principal'] = 0;
                        $this->_due['interest'] = 0;
                        $this->_due['fee'] = 0;
                        $this->_due['penalty'] = 0;

                        $this->_new_due['date'] = '';
                        $this->_new_due['num_day'] = 0;
                        $this->_new_due['num_installment'] = 0;
                        $this->_new_due['principal'] = 0;
                        $this->_new_due['interest'] = 0;
                        $this->_new_due['fee'] = 0;
                        $this->_new_due['penalty'] = 0;

                        /*$this->_arrears['cur']['date'] = '';
                        $this->_arrears['cur']['num_day'] = 0;
                        $this->_arrears['cur']['num_installment'] =0;
                        $this->_arrears['cur']['principal'] = 0;
                        $this->_arrears['cur']['interest'] = 0;
                        $this->_arrears['cur']['fee'] = 0;
                        $this->_arrears['cur']['penalty'] = 0;*/
                        return $this;
                    }
                    //$this->__construct();
                    //$this->_activated_at = $row->activated_at;

                    $this->error = 'You are already Perform It on
                    '.date('d-M-Y',strtotime($row->activated_at)).'
                    . Your Next Perform is on
                    '.date('d-M-Y',strtotime($this->_next_due['date']));
                    $this->_due['num_day'] = 0;
                    $this->_due['principal'] = 0;
                    $this->_due['interest'] = 0;
                    $this->_due['fee'] = 0;
                    $this->_due['penalty'] = 0;

                    $this->_arrears['cur']['date'] = '';
                    $this->_arrears['cur']['num_day'] = 0;
                    $this->_arrears['cur']['num_installment'] = 0;
                    $this->_arrears['cur']['principal'] = 0;
                    $this->_arrears['cur']['interest'] = 0;
                    $this->_arrears['cur']['fee'] = 0;
                    $this->_arrears['cur']['penalty'] = 0;

                    return $this;
                }

                if(!$this->_isEqualDate($this->_activated_at, $row->activated_at)){
                    $this->_last_perform_date = $row->activated_at;
                    $this->_new_due['principal'] = 0;
                    $this->_new_due['interest'] = 0;
                    $this->_new_due['penalty'] = 0;
                    $this->_new_due['num_day']=0;
                    $this->_new_due['num_installment']=0;

                    $this->getPerform();
                }
            }

        }
        return $this;
    }

    private function _getDisburse()
    {
        $data = \DB::select('SELECT *,
CONCAT(ln_client.kh_last_name," ",ln_client.kh_first_name) as client_name,
account_type.`code` as account_type,
COUNT(ln_disburse_client.id) as num_account,
COUNT(ln_lv_account_type) as num_account_type,
COUNT(ln_lv_gender) as num_gender,
COUNT(cp_location_id) as num_location
FROM ln_disburse_client INNER JOIN
ln_disburse on ln_disburse_client.ln_disburse_id = ln_disburse.id
INNER JOIN ln_product on ln_product.id = ln_disburse.ln_product_id
INNER JOIN ln_client on ln_client.id = ln_disburse_client.ln_client_id
INNER JOIN ln_lookup_value account_type on account_type.id = ln_disburse.ln_lv_account_type
INNER JOIN ln_center ON ln_center.id = ln_disburse.ln_center_id
WHERE ln_disburse_client.id = "'.$this->_disburse_client_id.'" ');
        $arr = new \stdClass();
        foreach ($data as $row) {
            $arr = $row;
        }
        return $arr;
    }

    public  function _isEqualDate($date1, $date2)
    {
        $date = new Carbon();
        $first = $date->createFromFormat('Y-m-d',$date1);
        $second = $date->createFromFormat('Y-m-d',$date2);
        if($this->_disburse->ln_lv_repay_frequency == 3){
            if($this->_disburse->installment_frequency != 1){
                $difWeek = ceil($first->endOfWeek()->diffInDays($second->endOfWeek()) / 7);
                if( $difWeek != $this->_disburse->installment_frequency){
                    $first->endOfWeek();
                    $second->endOfWeek();
                }else{
                    $first = $first->addWeek($this->_disburse->installment_frequency)->endOfWeek();
                    $second = $second->endOfWeek();
                }
            }else{
                $first = $first->endOfWeek();
                $second = $second->endOfWeek();
            }
        }else{
            if($this->_disburse->installment_frequency != 1){
                $difWeek = ceil($first->endOfMonth()->diffInDays($second->endOfMonth()) / 28);
                if( $difWeek != $this->_disburse->installment_frequency){
                    $first->endOfMonth();
                    $second->endOfMonth();
                }else{
                    $first = $first->addMonth($this->_disburse->installment_frequency)->endOfMonth();
                    $second = $second->endOfMonth();
                }
            }else{
                $first = $first->endOfMonth();
                $second = $second->endOfMonth();
            }
        }
        return $second->eq($first);
    }

    private  function _endOfDate($active_date)
    {
        $date = new Carbon();
        if($this->_disburse->ln_lv_repay_frequency == 3){
            $end = $date->createFromFormat('Y-m-d', $active_date)->endOfWeek();
        }else{
            $end = $date->createFromFormat('Y-m-d', $active_date)->endOfMonth();
        }
        return $end->toDateString();
    }

    private function _isDate($val)
    {
        if (in_array($val, array('', '0000-00-00'))) {
            return false;
        } else {
            return true;
        }
    }

    public function getPerform()
    {
        $amount =0;
        $prin = 0;
        $int =0;
        $pen =0;
        $num =0;

        $this->_arrears['last']['date'] = '';
        $this->_arrears['last']['num_day'] = 0;
        $this->_arrears['last']['num_installment'] =0;
        $this->_arrears['last']['principal'] = 0;
        $this->_arrears['last']['interest'] = 0;
        $this->_arrears['last']['fee'] = 0;
        $this->_arrears['last']['penalty'] = 0;

        if ($this->_isDate($this->_arrears['cur']['date'])) {
            $this->_arrears['last']['date'] = $this->_arrears['cur']['date'];
            $this->_arrears['last']['num_day'] = $this->_arrears['cur']['num_day'];
            $this->_arrears['last']['num_installment'] = $this->_arrears['cur']['num_installment'];
            $this->_arrears['last']['principal'] = $this->_arrears['cur']['principal'];
            $this->_arrears['last']['interest'] = $this->_arrears['cur']['interest'];
            $this->_arrears['last']['fee'] = $this->_arrears['cur']['fee'];
            $this->_arrears['last']['penalty'] = $this->_arrears['cur']['penalty'];
        }

        if($this->_isDate($this->_due['date'])){
            $this->_last_due['date'] = $this->_due['date'];
            $this->_last_due['num_day'] = $this->_due['num_day'];
            $this->_last_due['principal'] = $this->_due['principal'];
            $this->_last_due['interest'] = $this->_due['interest'];
        }

        $data = $this->_getSchedule($this->_endOfDate($this->_last_perform_date), $this->_endOfDate($this->_activated_at));
        $lnumDay=0;
        if ($data->count() > 0) {
            foreach ($data as $key => $row) {
                $this->_activated_num_installment = $row->index;
                if ($this->_isEqualDate($row->due_date, $this->_activated_at)) {
                    $this->_due['date'] = $row->due_date;
                    $this->_due['num_day'] = $this->_countDate($row->due_date,$this->_activated_at);
                    $this->_due['principal'] = $row->principal;
                    $this->_due['interest'] = $row->interest;
                    $this->_due['fee'] = $row->fee;
                    $this->_due['penalty'] = $this->_getPenalty($this->_due['num_day'],$this->_due['principal'] + $this->_due['interest'],$this->_due['num_day']);

                }
                if ($key == 0) {
                    $this->_new_due['date'] = $row->due_date;
                    $this->_new_due['num_installment'] = $row->index;
                    if($this->_isDate($this->_arrears['last']['date'])){
                        $lnumDay = $this->_countDate($this->_arrears['last']['date'], $row->due_date);
                        if($this->_activated_at < $row->due_date){
                            $lnumDay = $this->_countDate($this->_arrears['last']['date'], $this->_activated_at);
                        }
                        $amount = $this->_arrears['last']['principal'] + $this->_arrears['last']['interest'];

                        $pen = $this->_getPenalty($num, $amount,$lnumDay);
                    }
                }

                if($this->_activated_at >= $row->due_date){
                    $tmpStart = $row->due_date;
                    $tmpEnd = $this->_activated_at;

                    $curDate = $this->_countDate($tmpStart,$tmpEnd);
                    $num += $curDate;
                    $amount = $row->principal + $row->interest;
                    $pen += $this->_getPenalty($num,$amount,$curDate);
                }
                $prin += $row->principal;
                $int += $row->interest;
            }

            /*if($this->_last_due['num_day'] < 0){
                $pen = $pen - $this->_getPenalty(abs($this->_last_due['num_day']),$this->_last_due['principal'] + $this->_last_due['interest']);
            }*/

            $this->_new_due['principal'] = $prin;
            $this->_new_due['interest'] = $int;
            //$this->_new_due['num_day'] = $num;
            $this->_new_due['num_day'] = $this->_countDate($this->_new_due['date'],$this->_activated_at) + $lnumDay ;
            $this->_new_due['penalty'] = $pen;
            if($this->_new_due['num_day']>0){
                $this->_new_due['product_status'] = $this->_getProductStatus($this->_new_due['num_day'])->id;
                $this->_new_due['product_status_date'] = $this->_getProductStatusDate($this->_new_due['num_day']);
            }

            $this->_arrears['cur']['date'] = $this->_new_due['date'];
            $this->_arrears['cur']['num_day'] = $this->_new_due['num_day'] + $this->_arrears['last']['num_day'];
            $this->_arrears['cur']['num_installment'] = $this->_new_due['num_installment'];
            $this->_arrears['cur']['principal'] = $this->_new_due['principal'] + $this->_arrears['last']['principal'];
            $this->_arrears['cur']['interest'] = $this->_new_due['interest'] + $this->_arrears['last']['interest'];
            $this->_arrears['cur']['fee'] = $this->_new_due['fee'] + $this->_arrears['last']['fee'];
            $this->_arrears['cur']['penalty'] = $this->_new_due['penalty'] + $this->_arrears['last']['penalty'];

            //Maturity Date is over
            if ($this->_endOfDate($this->_activated_at) > $this->_endOfDate($this->_maturity_date)) {
                //$this->_due['date'] = '';
                $this->_due['principal'] = 0;
                $this->_due['interest'] = 0;
                $this->_due['fee'] = 0;

                $this->_next_due['date'] = '';
                $this->_next_due['principal'] = 0;
                $this->_next_due['interest'] = 0;
                $this->_next_due['fee'] = 0;

                $this->error = 'Now you are over maturity date '.$this->_maturity_date.'';
            }
            //On Maturity Date
            if ($this->_isEqualDate($this->_activated_at, $this->_maturity_date)) {
                $this->_next_due['date'] = '';
                $this->_next_due['principal'] = 0;
                $this->_next_due['interest'] = 0;
                $this->_next_due['fee'] = 0;

                $this->error = 'Now you are on maturity date '.$this->_maturity_date.'';
            }
            $this->getNext();
            $this->_due_closing['interest_closing'] = $this->_getPenaltyClosing($this->_balance_interest - $this->_new_due['interest']);
            //$this->_due_closing['interest_closing'] = $this->_getPenaltyClosing($this->_balance_interest);
            $this->_due_closing['principal_closing'] = $this->_balance_principal - $this->_new_due['principal'];
            //Accrued interest
            $rate_type = 30;
            if($this->_disburse->ln_lv_repay_frequency == 3){
                $rate_type = 7;
            }
            $int_rate = $this->_disburse->interest_rate / $rate_type / 100;
            if($this->_activated_at > $this->_due['date']){
                $this->_accru_int = \Currency::round($this->_disburse->cp_currency_id,($this->_due_closing['principal_closing'] * $this->_due['num_day'] * $int_rate));
            }
            if(\DateTime::createFromFormat('Y-m-d',$this->_activated_at) >= \DateTime::createFromFormat('Y-m-d',$this->_maturity_date)){
                $this->_accru_int = 0;
            }
        }
    }

    private function _getSchedule($lastDate, $performDate)
    {
        $data = Schedule::join('ln_schedule_dt', 'ln_schedule.id', '=', 'ln_schedule_dt.ln_schedule_id')
            ->where('ln_disburse_client_id', '=', $this->_disburse_client_id)
            ->where('due_date', '>', $lastDate)
            ->where('due_date', '<=', $performDate)
            ->get();
        return $data;
    }

    public function _countDate($day1, $day2)
    {
        if($day1=='') return $data = 0;
        $day1 = Carbon::createFromFormat('Y-m-d', $day1);
        $day2 = Carbon::createFromFormat('Y-m-d', $day2);
        $data = $day1->diffInDays($day2);
        if($day1 > $day2){
            $data = -$data;
        }
        return $data;
    }

    public function _getPenalty($numDay, $amount,$curNum)
    {
        $data = Penalty::where('id', '=', $this->_disburse->ln_penalty_id)->limit(1)->orderBy('id','desc')->first();
        $penalty = 0;
        if ($numDay > $data->grace_period) {
            $penalty = \Currency::round($this->_disburse->cp_currency_id,(($amount * $curNum * $data->amount) / 100));
        }
        return $penalty;
    }

    private function _getProductStatus($num_day)
    {
        if($num_day == 0) $num_day = 1;
        if($num_day  > 10000) $num_day = 10000;
        $data = \DB::table('ln_product_status')
            ->where('f_num_day', '<=', $num_day)
            ->where('t_num_day', '>=', $num_day)
            ->first();
        return $data;
    }

    private function _getProductStatusDate($num_day)
    {
        if($num_day == 0) $num_day = 1;
        if($num_day  > 10000) $num_day = 361;
        $data = $this->_getProductStatus($num_day);
        $overDate = $num_day - $data->f_num_day;
        return Carbon::createFromFormat('Y-m-d', $this->_activated_at)
            ->subDays($overDate)
            ->toDateString();
    }

    private function _getRepaymentStatus($num_day){
        if($num_day == 0) $num_day = 1;
        if($num_day  > 10000) $num_day = 10000;
        $data = \DB::table('ln_payment_status')
            ->where('f_num_day', '<=', $num_day)
            ->where('t_num_day', '>=', $num_day)
            ->first();
        return $data;
    }

    public function getNext()
    {
        $data = Schedule::join('ln_schedule_dt', 'ln_schedule.id', '=', 'ln_schedule_dt.ln_schedule_id')
            ->where('ln_disburse_client_id', '=', $this->_disburse_client_id)
            ->where('index', '=', $this->_activated_num_installment + 1)->get();
        foreach ($data as $row) {
            $this->_next_due['date'] = $row->due_date;
            $this->_next_due['principal'] = $row->principal;
            $this->_next_due['interest'] = $row->interest;
            $this->_next_due['fee'] = $row->fee;
        }
    }

    public function _getPenaltyClosing($interest)
    {
        $data = PenaltyClosing::where('id', '=', $this->_disburse->ln_penalty_closing_id)->first();
        $amt = 0;
        if ($this->_can_closing > $this->_activated_num_installment) {
            $amt = \Currency::round($this->_disburse->cp_currency_id,($interest * $data->percentage_interest_remainder) / 100);
        }
        /*$amt = 0;
        if($this->_can_closing > $this->_activated_num_installment){
            $acu_int= ($this->_balance_principal * $this->_due['num_day'] * $this->_disburse->interest_rate) / 100;
            $amt = \Currency::round($this->_disburse->cp_currency_id,((($interest - $acu_int) * $data->percentage_interest_remainder) /100));
        }*/
        return $amt;
    }

    public function repay($principal = 0, $penalty = 0, $option='',$voucher)
    {
        $prin =0;
        $int = 0;
        $total =0;
        $this->_perform_type = 'repayment';

        if ($this->_isDate($this->_repayment['cur']['date'])) {
            $this->_repayment['last']['date'] = $this->_repayment['cur']['date'];
            $this->_repayment['last']['voucher_id'] = $this->_repayment['cur']['voucher_id'];
            $this->_repayment['last']['status'] = $this->_repayment['cur']['status'];
            $this->_repayment['last']['principal'] = $this->_repayment['cur']['principal'];
            $this->_repayment['last']['interest'] = $this->_repayment['cur']['interest'];
            $this->_repayment['last']['penalty'] = $this->_repayment['cur']['penalty'];
            $this->_repayment['last']['type'] = $this->_repayment['cur']['type'];
        }

        $total = $this->_arrears['cur']['principal'] + $this->_arrears['cur']['interest'];

        switch ($option) {
            case 'normal':
                $arrearsPrin=0;
                $arrearsInt =0;
                $arrearsIndex =1;
                $arrearsDate ='';
                if((float)$principal == (float)$total){
                    if($this->_isEqualDate($this->_activated_at,$this->_maturity_date) or $this->_activated_at >= $this->_maturity_date){
                        $option = 'closing';
                    }
                }
                if($this->_new_due['product_status'] == 5){
                    $wof_pri=0;
                    $wof_int=0;
                    $wof_pen =0;

                    $wof_pen= $penalty - $this->_arrears['cur']['penalty'];
                    $wof_int = $principal - $this->_arrears['cur']['interest'];
                    if($wof_int > 0){
                        $wof_pri = $wof_int - $this->_arrears['cur']['principal'];
                    }else{
                        $wof_int = 0;
                        $wof_pri = $this->_arrears['cur']['principal'];
                        $this->_arrears['cur']['date'] = $this->_activated_at;
                    }

                    if($penalty >0){
                        $this->_arrears['cur']['date'] = '';
                    }
                    $this->_repayment['cur']['date'] = $this->_activated_at;
                    $this->_repayment['cur']['principal']= $wof_int;
                    $this->_repayment['cur']['interest']= $principal;
                    $this->_repayment['cur']['penalty']= $penalty;


                    $this->_arrears['cur']['principal'] = abs($wof_pri);
                    $this->_arrears['cur']['interest'] = abs($wof_int);
                    $this->_arrears['cur']['penalty'] = abs($wof_pen);

                    break;
                }

                if($this->_isDate($this->_arrears['last']['date'])){
                    $c = '>=';
                    $this->_last_perform_date = $this->_arrears['last']['date'];
                }else{
                    $c = '>';
                    $this->_last_perform_date = $this->_endOfDate($this->_last_perform_date);
                }

                $sch = \DB::select('SELECT * FROM ln_schedule INNER JOIN ln_schedule_dt on ln_schedule.id = ln_schedule_dt.ln_schedule_id
                                    WHERE ln_schedule.ln_disburse_client_id = "'.$this->_disburse_client_id.'"
                                    AND Date(ln_schedule.due_date) '.$c.' "'.$this->_last_perform_date.'"
                                    AND Date(ln_schedule.due_date) <= "'.$this->_endOfDate($this->_activated_at).'" and ln_schedule.index >0 ');

                $cPrin = 0;
                $cInt = 0;

                foreach ($sch as $j=> $value) {
                    if($j >= 1){
                        $cPrin += $value->principal;
                        $cInt += $value->interest;
                    }

                }

                $tmpPrin =0;
                $i=0;
                foreach ($sch as $key=>$row) {
                    if($principal !=0){
                        if($key == 0){
                            if($this->_isDate($this->_arrears['last']['date'])){
                                $tmp_p = $this->_arrears['cur']['principal'] - $cPrin;
                                $tmp_i = $this->_arrears['cur']['interest'] - $cInt;

                                $row->principal = $tmp_p;
                                $row->interest = $tmp_i;
                                if($tmp_i <=0){
                                    $row->principal = $tmp_p;
                                    $row->interest = 0;
                                }
                            }
                        }

                        $tmpInt = $principal - $row->interest;
                        $tmpPrin = $tmpInt - $row->principal;
                        if($tmpInt >= 0){
                            $int += $row->interest;
                            if((int)$tmpPrin >= 0){
                                $prin += $row->principal;
                                $arrearsDate = $row->due_date;
                            }else{
                                $arrearsDate = $row->due_date;
                                $prin += $tmpInt;
                                $arrearsPrin = abs($tmpPrin);
                                $arrearsIndex = $row->index;
                            }
                        }else{
                            $int += $principal;
                            if($prin == 0 ) $prin = 0;
                            $arrearsDate = $row->due_date;
                            $arrearsInt += abs($tmpInt);
                            $arrearsPrin += $row->principal;
                            $arrearsIndex = $row->index;
                            $tmpPrin = 0;
                        }
                        $i++;
                    }
                    else{
                        if(count($sch) != $i and $i=0){
                            $arrearsPrin += $row->principal;
                            $arrearsInt += $row->interest;
                        }
                    }
                    if($tmpPrin >0){
                        $principal = $tmpPrin;
                    }else{
                        $principal = 0;
                    }
                }

                //$this->_arrears['cur']['penalty'] = 0;
                if($this->_arrears['cur']['penalty'] - $penalty > 0){
                    $this->_arrears['cur']['penalty'] = $this->_arrears['cur']['penalty'] - $penalty;
                }else{
                    $this->_arrears['cur']['penalty'] = 0;
                }
                $this->_arrears['cur']['principal'] = $arrearsPrin;
                $this->_arrears['cur']['interest'] = $arrearsInt;
                $this->_arrears['cur']['date'] = $arrearsDate;
                $this->_arrears['cur']['num_day'] = $this->_countDate($arrearsDate,$this->_activated_at);
                $this->_arrears['cur']['num_installment'] = $arrearsIndex;

                $this->_repayment['cur']['date'] = $this->_activated_at;
                $this->_repayment['cur']['voucher_id'] = \UserSession::read()->sub_branch
                    . '-' . date('Y') . '-' . $this->_disburse->cp_currency_id . '-' . sprintf('%06d', $voucher);
                //$this->_repayment['cur']['status'] = $this->_arrears['cur']['num_day'] + $this->_repayment['last']['status'];
                $this->_repayment['cur']['principal'] = abs($prin);
                $this->_repayment['cur']['interest'] = $int;
                $this->_repayment['cur']['penalty'] = $penalty;
                $this->_repayment['cur']['type'] = $option;

                $this->_balance_principal = $this->_balance_principal - $this->_repayment['cur']['principal'];
                $this->_balance_interest = $this->_balance_interest - $this->_repayment['cur']['interest'];
                if($this->_arrears['cur']['num_day'] >0){
                    $this->_repayment['cur']['status'] = $this->_getRepaymentStatus($this->_arrears['cur']['num_day'])->id;

                    $this->_current_product_status = $this->_getProductStatus($this->_arrears['cur']['num_day'])->id;
                    $this->_current_product_status_date = $this->_getProductStatusDate($this->_arrears['cur']['num_day']);
                    if($this->_current_product_status ==5){
                        $this->_current_product_status=4;
                    }
                }
                $this->_current_product_status_principal = $this->_balance_principal;

                if(($this->_arrears['cur']['principal'] + $this->_arrears['cur']['interest'] + $this->_arrears['cur']['penalty']) == 0 ){
                    $this->_arrears['cur']['date'] = '';
                    $this->_arrears['cur']['num_day'] = 0;
                    $this->_arrears['cur']['num_installment'] = 0;
                    $this->_arrears['cur']['principal'] = 0;
                    $this->_arrears['cur']['interest'] = 0;
                    $this->_arrears['cur']['fee'] = 0;
                }
                break;

            case 'closing':
                //$this->_arrears['cur']['principal'] = $this->_arrears['cur']['principal'] + $this->_due_closing['principal_closing'];
                //$this->_arrears['cur']['interest'] = $this->_arrears['cur']['interest'] + $this->_due_closing['interest_closing'];

                if((float)$penalty < (float)$this->_arrears['cur']['penalty']){
                    $this->_arrears['cur']['penalty'] = $this->_arrears['cur']['penalty'] - $penalty;
                }else{
                    $this->_arrears['cur']['date'] ='';
                    $this->_arrears['cur']['penalty'] =0;
                }

                $this->_repayment['cur']['date'] = $this->_activated_at;
                $this->_repayment['cur']['voucher_id'] = \UserSession::read()->sub_branch
                    . '-' . date('Y') . '-' . $this->_disburse->cp_currency_id . '-' . sprintf('%06d', $voucher);
                //$this->_repayment['cur']['status'] = $this->_arrears['cur']['status'];
                $this->_repayment['cur']['status'] = $this->_new_due['product_status'];
                $this->_repayment['cur']['principal'] = $this->_arrears['cur']['principal'];
                $this->_repayment['cur']['interest'] = $this->_arrears['cur']['interest'];
                $this->_repayment['cur']['penalty'] = $penalty;
                $this->_repayment['cur']['type'] = $option;

                $this->_balance_principal = $this->_balance_principal - $this->_arrears['cur']['principal'];
                $this->_balance_interest = $this->_balance_interest - $this->_arrears['cur']['interest'];

                $this->_current_product_status = $this->_new_due['product_status'];
                $this->_current_product_status_date = $this->_new_due['product_status_date'];
                $this->_current_product_status_principal = $this->_balance_principal;

                if($this->_current_product_status ==5){
                    $this->_current_product_status=4;
                }

                $this->_arrears['cur']['principal'] = 0;
                $this->_arrears['cur']['interest'] = 0;
                $this->_arrears['cur']['fee'] = 0;

                if($this->_arrears['cur']['penalty'] == 0){
                    $this->_arrears['cur']['date'] = '';
                    $this->_arrears['cur']['num_day'] = 0;
                    $this->_arrears['cur']['num_installment'] = 0;
                    $this->_arrears['cur']['principal'] = 0;
                    $this->_arrears['cur']['interest'] = 0;
                    $this->_arrears['cur']['fee'] = 0;
                }
                $this->_next_due['date'] = '';
                $this->_next_due['principal'] = 0;
                $this->_next_due['interest'] = 0;
                $this->_next_due['fee'] = 0;

                break;

            case 'penalty':

                $this->_repayment['cur']['date'] = $this->_activated_at;
                $this->_repayment['cur']['voucher_id'] = \UserSession::read()->sub_branch
                    . '-' . date('Y') . '-' . $this->_disburse->cp_currency_id . '-' . sprintf('%06d', $voucher);
                $this->_repayment['cur']['status'] = $this->_new_due['product_status'];
                $this->_repayment['cur']['principal'] = $this->_arrears['cur']['principal'];
                $this->_repayment['cur']['interest'] = $this->_arrears['cur']['interest'];
                $this->_repayment['cur']['penalty'] = $penalty;
                $this->_repayment['cur']['type'] = $option;

                if(bccomp($this->_arrears['cur']['penalty'],$penalty,10)== 1){
                    $this->_arrears['cur']['penalty'] = $this->_arrears['cur']['penalty'] - $penalty;
                }else{
                    $this->_arrears['cur']['date'] = '';
                    $this->_arrears['cur']['penalty'] = 0;
                }

                break;
            case 'fee':
                $this->_repayment['cur']['date'] = $this->_activated_at;
                $this->_repayment['cur']['voucher_id'] = \UserSession::read()->sub_branch
                    . '-' . date('Y') . '-' . $this->_disburse->cp_currency_id . '-' . sprintf('%06d', $voucher);
                $this->_repayment['cur']['status'] = $this->_new_due['product_status'];
                $this->_repayment['cur']['principal'] = 0;
                $this->_repayment['cur']['interest'] = 0;
                $this->_repayment['cur']['penalty'] = $penalty;
                $this->_repayment['cur']['type'] = $option;
                $this->_repayment['cur']['fee'] = $principal;
        }
    }

    public function addWriteOff(){
        if($this->_arrears['cur']['num_day']){

        }
    }

    public function delete($id)
    {
        Perform::where('id', '=', $id)
            ->delete();
    }

    public function deleteDisburse($id){
        Perform::where('ln_disburse_client_id', '=', $id)
            ->delete();
    }

    public function _getLastPerform($id){
        $data = Perform::where('ln_disburse_client_id','=',$id)->orderBy('id','desc')->limit(1)->first();
        return $data;
    }


} 