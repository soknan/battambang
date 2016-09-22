<?php

namespace Battambang\Loan;

use Battambang\Cpanel\BaseController;
use Battambang\Loan\Libraries\LoanPerformance;
use Battambang\Loan\Libraries\RepaymentSchedule;
use Battambang\Loan\Libraries\ScheduleGenerate;
use Config;
use DB;
use Former\Form\Form;
use Input;
use Redirect;
use UserSession;
use View;
use Whoops\Example\Exception;

class DisburseClientController extends BaseController
{

    public function index($disburse)
    {
        $item = array('Action', 'Loan_Acc #', 'Client_ID','Client_Name', 'Center', 'Product', 'Amount', 'Currency', 'Voucher_ID', 'Cycle');
        /*$data['btnAction'] = array('Add New' => route('loan.disburse_client.add', array($disburse)));*/
        $data['table'] = \Datatable::table()
            ->addColumn($item) // these are the column headings to be shown
            ->setUrl(route('api.disburse_client', $disburse)) // this is the route where data will be retrieved
            ->setOptions('aLengthMenu', array(
                array(10, 25, 50, 100),
                array(10, 25, 50, 100)
            ))
            ->setOptions("sScrollY",300)
            ->setOptions("iDisplayLength", 10)// default show entries
            ->render('battambang/cpanel::layout.templates.template');
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.disburse_client_index'), $data)
        );
    }

    public function create($disburse)
    {
        $dis = Disburse::where('id','=',$disburse)->first();
        $disClient = DisburseClient::where('ln_disburse_id','=',$disburse)->count();
        if($disClient >= 1){
            if($dis->ln_lv_account_type == 1){
                if(!Input::has('submit')){
                    return Redirect::route('loan.disburse_client.index',$disburse)->with('error','Your Current Account Type is Single.');
                }
            }
        }

        $data = array();
        $dis = $disburse;
        $client = Input::get('ln_client_id');
        /*if(\Session::has('disburse_id')){
            $dis = \Session::get('disburse_id');
            $client = \Session::get('client_id');
        }*/

        $data = $this->_getDisburseClientLast($client);
        $data['client'] = $this->_getClientList(' and id = "' . $client . '"');
        $data['disburse_id'] = $dis;
        //Get Disburse
        $dis = DB::table('view_disburse')->where('id', '=', $dis)->get();
        foreach ($dis as $row) {
            $data['dis'] = $row;
        }

        //Get Product
        $pro = DB::table('view_product')->where('id', '=', $data['dis']->product_id)->get();
        foreach ($pro as $row2) {
            $data['pro'] = $row2;
        }

        $getLoanAmountEx=$this->loanAmountEx($data['dis']->currency_code, $data['pro']->min_amount, $data['pro']->max_amount, $data['pro']->default_amount);
        $data['pro']->default_amount = $getLoanAmountEx['default'];
        $data['pro']->min_amount = $getLoanAmountEx['min'];
        $data['pro']->max_amount = $getLoanAmountEx['max'];
        $data['pro']->append_amount = $getLoanAmountEx['append'];

        \Session::forget('disburse_id');
        \Session::forget('client_id');

        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.disburse_client_create'), $data)
        );
    }

    public function disburseClient($disburse)
    {
        $data = array();
        $dis = Disburse::where('id','=',$disburse)->first();
        $disClient = DisburseClient::where('ln_disburse_id','=',$disburse)->count();
        if($disClient >= 1){
            if($dis->ln_lv_account_type == 1){
                return Redirect::back()->with('error','Your Current Account Type is Single.');
            }
        }
        $data['disburse_id'] = $disburse;

        //var_dump($this->_getClientGroup($disburse));exit;
        if(count($this->_getClientGroup($disburse)>0)){
            $data['client'] = $this->_getClientList(" and id NOT IN('".implode("','", $this->_getClientGroup($disburse))."') order by id desc");
        }else{
            $data['client'] = $this->_getClientList(" order by id DESC");
        }

        //Get Disburse
        $dis = DB::table('view_disburse')->where('id', '=', $disburse)->get();
        foreach ($dis as $row) {
            $data['dis'] = $row;
        }

        //Get Product
        $pro = DB::table('view_product')->where('id', '=', $data['dis']->product_id)->get();
        foreach ($pro as $row2) {
            $data['pro'] = $row2;
        }
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.disburse_client_add'), $data)
        );
    }

    public function show($id){
        $data['row'] = array();
        $data['client'] = array();
        try {
            $data['row'] = DisburseClient::findOrFail($id);
            //list($sub_branch,$year,$cur,$code) = explode('-',$data['row']->voucher_id);
            $data['row']->voucher_id = substr($data['row']->voucher_id, -6);
            $data['client'] = $this->_getClientList(" and id = '".$data['row']->ln_client_id."' ");
            //Get Disburse
            $dis = DB::table('view_disburse')->where('id', '=', $data['row']->ln_disburse_id)->get();
            foreach ($dis as $row) {
                $tmp[] = $row;
            }
            $data['dis'] = $row;
            //Get Product
            $pro = DB::table('ln_product')->where('id', '=', $data['dis']->product_id)->get();
            foreach ($pro as $row2) {
                $data['pro'] = $row2;
            }

            $getLoanAmountEx=$this->loanAmountEx($data['dis']->currency_code, $data['pro']->min_amount, $data['pro']->max_amount, $data['pro']->default_amount);
            $data['pro']->default_amount = $getLoanAmountEx['default'];
            $data['pro']->min_amount = $getLoanAmountEx['min'];
            $data['pro']->max_amount = $getLoanAmountEx['max'];
            $data['pro']->append_amount = $getLoanAmountEx['append'];

            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.disburse_client_show'), $data)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.disburse_client.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function edit($id)
    {
        $data['row'] = array();
        $data['client'] = array();
        try {
            $data['row'] = DisburseClient::findOrFail($id);
            //list($sub_branch,$year,$cur,$code) = explode('-',$data['row']->voucher_id);
            $data['row']->voucher_id = substr($data['row']->voucher_id, -6);
            $data['client'] = $this->_getClientList(" and id = '".$data['row']->ln_client_id."' ");
            //Get Disburse
            $dis = DB::table('view_disburse')->where('id', '=', $data['row']->ln_disburse_id)->get();
            foreach ($dis as $row) {
                $tmp[] = $row;
            }
            $data['dis'] = $row;
            //Get Product
            $pro = DB::table('view_product')->where('id', '=', $data['dis']->product_id)->get();
            foreach ($pro as $row2) {
                $data['pro'] = $row2;
            }

            $getLoanAmountEx=$this->loanAmountEx($data['dis']->currency_code, $data['pro']->min_amount, $data['pro']->max_amount, $data['pro']->default_amount);
            $data['pro']->default_amount = $getLoanAmountEx['default'];
            $data['pro']->min_amount = $getLoanAmountEx['min'];
            $data['pro']->max_amount = $getLoanAmountEx['max'];
            $data['pro']->append_amount = $getLoanAmountEx['append'];

            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.disburse_client_edit'), $data)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.disburse_client.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function store()
    {
        $validation = $this->getValidationService('disburse_client');
        /*\Session::set('disburse_id',Input::get('ln_disburse_id'));
        \Session::set('client_id',Input::get('ln_client_id'));*/

        if ($validation->passes()) {
            $disburseClient = new DisburseClient();

            // by Theara: change loan account id = disburse id '-' client id that substr(str, 5, 4).
            $disburseClient->id = Input::get('ln_disburse_id'). '-'.substr(Input::get('ln_client_id'), 5, 4);
//            $disburseClient->id = \AutoCode::make('ln_disburse_client', 'id', Input::get('ln_disburse_id') . '-', 4);
            $id = $disburseClient->id;
            $this->saveData($disburseClient);

            $disburseDate = Disburse::where('id', '=', $disburseClient->ln_disburse_id)->first();
            $schedule = \ScheduleGenerate::make($id, $disburseDate->disburse_date);

            $repay = new RepaymentSchedule();
            $perform = new LoanPerformance();

            $repay->save($schedule, $id, $disburseDate->disburse_date);

            $perform->_disburse_client_id = $id;
            $perform->_activated_at = $repay->_activated_at;
            $perform->_maturity_date = $repay->_maturity_date;
            $perform->_activated_num_installment = $repay->_activated_num_installment;
            $perform->_project_interest = $repay->_project_interest;
            $perform->_num_borrow_day = $repay->_num_borrow_day;
            $perform->_due['date'] = $repay->_activated_at;
            $perform->_can_closing = $repay->_can_closing;

            $perform->_arrears['cur']['fee']= $repay->_fee;
            $perform->_new_due['product_status'] = 1;
            $perform->_new_due['product_status_date'] = $repay->_activated_at;
            $perform->_current_product_status = 1;
            $perform->_current_product_status_date = $repay->_activated_at;
            $perform->_current_product_status_principal = $repay->_balance_principal;

            $perform->_balance_principal = $repay->_balance_principal;
            $perform->_balance_interest = $repay->_project_interest;

            $perform->getNext();
            //$perform->_due_closing['interest_closing'] = $perform->_getPenaltyClosing($perform->_balance_interest - $perform->_next_due['interest']);
            //$perform->_due_closing['principal_closing'] = $perform->_balance_principal;
            $perform->_perform_type = 'disburse';

            $perform->save();

            // User action
            \Event::fire('user_action.add', array('disburse_client'));

            // Check first due date and annunity int > total
            if($disburseDate->round_schedule=='Y'){
                $arr = DB::select('select * from(
                    select sum(sd.interest) as total_int
                    ,sum(case when s.index =1 then sd.principal + sd.interest else 0 end) as total
                    from ln_schedule s
                    inner join ln_schedule_dt sd on sd.ln_schedule_id = s.id
                    where s.ln_disburse_client_id = "'.$id.'")p where p.total_int > p.total');
                if(count($arr)>0){
                    return Redirect::route('loan.disburse_client.index', $disburseClient->ln_disburse_id)
                        ->with('info','Now your total interest is bigger than total payment !')
                        ->with('success', trans('battambang/loan::disburse_client.create_success')
                            . \Former::open(route('loan.rpt_schedule.report'))
                            . \Former::text_hidden('ln_disburse_client_id',$id)
                            . \Former::text_hidden('view_at',date('d-m-Y'))
                            . \Former::primary_submit('Print Schedule') . \Former::close());
                }
            }

            if($disburseDate->ln_lv_account_type == 1){
                return Redirect::route('loan.disburse_client.index', $disburseClient->ln_disburse_id)
                    ->with('info','Now your current account type is single, so can not add more client !')
                    ->with('success', trans('battambang/loan::disburse_client.create_success')
                        . \Former::open(route('loan.rpt_schedule.report'))
                        . \Former::text_hidden('ln_disburse_client_id',$id)
                        . \Former::text_hidden('view_at',date('d-m-Y'))
                        . \Former::primary_submit('Print Schedule') . \Former::close());
            }else{
                return Redirect::route('loan.disburse_client.add', $disburseClient->ln_disburse_id)
                    ->with('success', trans('battambang/loan::disburse_client.create_success')
                        . \Former::open(route('loan.rpt_schedule.report'))
                        . \Former::text_hidden('ln_disburse_client_id',$id)
                        . \Former::text_hidden('view_at',date('d-m-Y'))
                        . \Former::primary_submit('Print Schedule') . \Former::close());
            }


        }
        return Redirect::back()->withInput()->withErrors($validation->getErrors());

       /* return Redirect::route('loan.disburse_client.create',\Session::get('disburse_id'))
            ->withInput()->withErrors($validation->getErrors());*/

    }

    public function update($id)
    {
        //echo date('Y-m-d',strtotime(''));
        //exit;
        try {
            $validation = $this->getValidationService('disburse_client');
            if ($validation->passes()) {
                //Delete relation
                DisburseClient::find($id)->delete();
                Perform::where('ln_disburse_client_id','=',$id)->delete();
                $tmp= Schedule::where('ln_disburse_client_id','=',$id)->get();
                foreach($tmp as $key=>$val){
                    ScheduleDt::where('ln_schedule_id','=',$val->id)->delete();
                }
                Schedule::where('ln_disburse_client_id','=',$id)->delete();
                //end delete
                $disburseClient = new DisburseClient();
                $disburseClient->id = Input::get('ln_disburse_id'). '-'.substr(Input::get('ln_client_id'), 5, 4);
                $id = $disburseClient->id;
                $this->saveData($disburseClient);

                $disburseDate = Disburse::where('id', '=', $disburseClient->ln_disburse_id)->first();
                $schedule = \ScheduleGenerate::make($id, $disburseDate->disburse_date);

                $repay = new RepaymentSchedule();
                $perform = new LoanPerformance();

                $repay->save($schedule, $id, $disburseDate->disburse_date);

                $perform->_disburse_client_id = $id;
                $perform->_activated_at = $repay->_activated_at;
                $perform->_maturity_date = $repay->_maturity_date;
                $perform->_activated_num_installment = $repay->_activated_num_installment;
                $perform->_project_interest = $repay->_project_interest;
                $perform->_num_borrow_day = $repay->_num_borrow_day;
                $perform->_due['date'] = $repay->_activated_at;
                $perform->_can_closing = $repay->_can_closing;

                $perform->_arrears['cur']['fee']= $repay->_fee;
                $perform->_new_due['product_status'] = 1;
                $perform->_new_due['product_status_date'] = $repay->_activated_at;
                $perform->_current_product_status = 1;
                $perform->_current_product_status_date = $repay->_activated_at;
                $perform->_current_product_status_principal = $repay->_balance_principal;

                $perform->_balance_principal = $repay->_balance_principal;
                $perform->_balance_interest = $repay->_project_interest;

                $perform->getNext();
                $perform->_perform_type = 'disburse';

                $perform->save();
// User action
                \Event::fire('user_action.edit', array('disburse_client'));

             // Check first due date and annunity int > total
                if($disburseDate->round_schedule=='Y'){
                    $arr = DB::select('select * from(
                    select sum(sd.interest) as total_int
                    ,sum(case when s.index =1 then sd.principal + sd.interest else 0 end) as total
                    from ln_schedule s
                    inner join ln_schedule_dt sd on sd.ln_schedule_id = s.id
                    where s.ln_disburse_client_id = "'.$id.'")p where p.total_int < p.total');
                    if(count($arr)>0){
                        return Redirect::route('loan.disburse_client.edit', array($id, $disburseClient->ln_disburse_id))
                            ->with('info','Now your total interest is bigger than total payment !')
                            ->with('success',
                                trans('battambang/loan::disburse_client.update_success')
                                . \Former::open(route('loan.rpt_schedule.report'))
                                . \Former::text_hidden('ln_disburse_client_id',$id)
                                . \Former::text_hidden('view_at',date('d-m-Y'))
                                . \Former::primary_submit('Print Schedule') . \Former::close()
                            );
                    }

                }

                return Redirect::route('loan.disburse_client.edit', array($id, $disburseClient->ln_disburse_id))
                    ->with('success',
                        trans('battambang/loan::disburse_client.update_success')
                        . \Former::open(route('loan.rpt_schedule.report'))
                        . \Former::text_hidden('ln_disburse_client_id',$id)
                        . \Former::text_hidden('view_at',date('d-m-Y'))
                        . \Former::primary_submit('Print Schedule') . \Former::close()
                    );
            }
            return Redirect::back()->withInput()->withErrors($validation->getErrors());
        } catch (\Exception $e) {
            return Redirect::route('loan.disburse_client.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function destroy($id)
    {
        try {
            DisburseClient::find($id)->delete();
            Perform::where('ln_disburse_client_id','=',$id)->delete();
            $tmp= Schedule::where('ln_disburse_client_id','=',$id)->get();
            foreach($tmp as $key=>$val){
                ScheduleDt::where('ln_schedule_id','=',$val->id)->delete();
            }
            Schedule::where('ln_disburse_client_id','=',$id)->delete();
            // User action
            \Event::fire('user_action.delete', array('disburse_client'));
            return Redirect::back()->with('success', trans('battambang/loan::disburse_client.delete_success'));
        } catch (\Exception $e) {
           return Redirect::route('loan.disburse_client.index')->with('error', trans('battambang/cpanel::db_error.fail'));
       }
    }

    private function saveData($data, $store = true)
    {
        /* if($store){
             $data->id = \AutoCode::make('ln_disburse_client', 'id', UserSession::read()->sub_branch . '-' . date('Y') . '-', 6);
         }*/
        $data->ln_disburse_id = Input::get('ln_disburse_id');
        $data->amount = Input::get('amount');
        $data->voucher_id = \UserSession::read()->sub_branch
            . '-' . date('Y') . '-' . \Input::get('currency_id'). '-' . sprintf('%06d', Input::get('voucher_id'));
        $data->cycle = Input::get('cycle');
        $data->ln_lv_history = Input::get('ln_lv_history');
        $data->ln_lv_purpose = Input::get('ln_lv_purpose');
        $data->purpose_des = Input::get('purpose_des');
        $data->ln_lv_activity = Input::get('ln_lv_activity');
        $data->ln_lv_collateral_type = Input::get('ln_lv_collateral_type');
        $data->collateral_des = Input::get('collateral_des');
        $data->ln_lv_security = Input::get('ln_lv_security');
        $data->ln_client_id = Input::get('ln_client_id');
        $data->ln_lv_id_type = Input::get('ln_lv_id_type');
        $data->id_num = Input::get('id_num');
        $data->expire_date = '';
        if(Input::get('expire_date')!=''){
            $data->expire_date = \Carbon::createFromFormat('d-m-Y',Input::get('expire_date'))->toDateString();
        }
        $data->ln_lv_marital_status = Input::get('ln_lv_marital_status');
        $data->family_member = Input::get('family_member');
        $data->num_dependent = Input::get('num_dependent');
        $data->ln_lv_education = Input::get('ln_lv_education');
        $data->ln_lv_business = Input::get('ln_lv_business');
        $data->ln_lv_poverty_status = Input::get('ln_lv_poverty_status');
        $data->income_amount = Input::get('income_amount');
        $data->ln_lv_handicap = Input::get('ln_lv_handicap');
        $data->address = Input::get('address');
        $data->ln_lv_contact_type = Input::get('ln_lv_contact_type');
        $data->contact_num = Input::get('contact_num');
        $data->email = Input::get('email');

        $data->pay_down = Input::get('pay_down');
        $data->discount = Input::get('discount');
        $data->pre_amount = Input::get('pre_amount');
        $data->save();
    }

    public function getDatatable($disburse)
    {
        $item = array('id', 'client_id','client_kh_name', 'center_name', 'product_name', 'amount', 'currency_code', 'voucher_id', 'cycle');
        $arr = DB::table('view_disburse_client')->where('ln_disburse_id', '=', $disburse)->where('id','like',\UserSession::read()->sub_branch.'%');;

        return \Datatable::query($arr)
            ->addColumn('action', function ($model) {
                $model->voucher_id = substr($model->voucher_id, -6);
                return \Action::make()
                    ->edit(route('loan.disburse_client.edit', array($model->id, $model->ln_disburse_id)),$this->_checkAction($model->id))
                    ->delete(route('loan.disburse_client.destroy', $model->id),'',$this->_checkAction($model->id))
                    ->show(route('loan.disburse_client.show',array($model->id, $model->ln_disburse_id)))
                    ->get();
            })
            ->showColumns($item)
            ->searchColumns($item)
            ->orderColumns($item)
            /*->addColumn('photo', function ($model) {
                return '<img src = "' . $model->client_photo . '" width = "60px" > ';
            })*/
            ->make();
    }

    private function _checkAction($id)
    {
        $data = Perform::where('ln_disburse_client_id', '=', $id)
            ->where('perform_type','=','repayment')
            ->limit(1)
            ->first();
        if (!$data) {
            return true;
        }
        return false;
    }

    public  function _getClientList($more='')
    {
        $data = DB::select('select * from ln_client where 1=1 and id like "'.UserSession::read()->sub_branch.'%"'. $more);
        $arr = array();
        if (count($data) > 0) {
            foreach ($data as $row) {
                $arr[$row->id] =$row->id.' : '.$row->en_last_name . ' ' . $row->en_first_name.' | '.$row->kh_last_name . ' ' . $row->kh_first_name;
            }
        }
        return $arr;
    }

    public  function _getClientGroup($id){
        $data = DisburseClient::where('ln_disburse_id','=',$id)->get();
        $arr = array();
        foreach ($data as $row) {
            $arr[$row->ln_client_id] = $row->ln_client_id;
        }

        return $arr;
    }

    private function _getDisburseClientLast($id)
    {
        $data = array();
        $data['amount'] = '';
        $data['ln_lv_id_type'] = '';
        $data['id_num'] = '';
        $data['expire_date'] = '';
        $data['ln_lv_marital_status'] = '';
        $data['family_member'] = '';
        $data['num_dependent'] = '';
        $data['ln_lv_education'] = '';
        $data['ln_lv_business'] = '';
        $data['ln_lv_poverty_status'] = '';
        $data['income_amount'] = '';
        $data['ln_lv_handicap'] = '';
        $data['address'] = '';
        $data['ln_lv_contact_type'] = '';
        $data['contact_num'] = '';
        $data['email'] = '';
        $data['cycle'] = 1;

        $arr = DisburseClient::where('ln_client_id', '=', $id)->orderBy('id', 'desc')->limit(1)->get();

        if ($arr->count() > 0) {
            foreach ($arr as $row) {
                $data['amount'] = $row->amount;
                $data['cycle'] = $row->cycle + 1;
                $data['ln_lv_id_type'] = $row->ln_lv_id_type;
                $data['id_num'] = $row->id_num;
                $row->expire_date=='0000-00-00'?$data['expire_date']='': $data['expire_date']=\Carbon::createFromFormat('Y-m-d',$row->expire_date)->format('d-m-Y') ;
                //$data['expire_date'] = $row->expire_date;
                $data['ln_lv_marital_status'] = $row->ln_lv_marital_status;
                $data['family_member'] = $row->family_member;
                $data['num_dependent'] = $row->num_dependent;
                $data['ln_lv_education'] = $row->ln_lv_education;
                $data['ln_lv_business'] = $row->ln_lv_business;
                $data['ln_lv_poverty_status'] = $row->ln_lv_poverty_status;
                $data['income_amount'] = $row->income_amount;
                $data['ln_lv_handicap'] = $row->ln_lv_handicap;
                $data['address'] = $row->address;
                $data['ln_lv_contact_type'] = $row->ln_lv_contact_type;
                $data['contact_num'] = $row->contact_num;
                $data['email'] = $row->email;
            }
        }
        return $data;
    }

    // by Theara: Calculate exchange for loan amount in product
    private function loanAmountEx($currency, $min, $max, $default){
        $usd=1;
        $khr=4000;
        $thb=30;
        $unit=1000;
        $data=array();
        switch ($currency) {
            case 'KHR':
                $data['min'] = ($min*$khr)/$usd;
                $data['max'] = ($max*$khr)/$usd;
                $data['default'] = ($default*$khr)/$usd;
                $data['append'] = number_format($data['min']/$unit, 0,'.',',') . ' - ' . number_format($data['max']/$unit, 0,'.',',') . ' ' . $currency .' (*'.$unit.')';
                break;
            case 'USD':
                $data['min'] = $min;
                $data['max'] = $max;
                $data['default'] = $default;
                $data['append'] = number_format($data['min'], 2,'.',',') . ' - ' . number_format($data['max'], 2,'.',',') . ' ' . $currency;
                break;
            case 'THB':
                $data['min'] = ($min*$thb)/$usd;
                $data['max'] = ($max*$thb)/$usd;
                $data['default'] = ($default*$thb)/$usd;
                $data['append'] = number_format($data['min'], 2,'.',',') . ' - ' . number_format($data['max'], 2,'.',',') . ' ' . $currency;
                break;
        }

        return $data;
    }

}