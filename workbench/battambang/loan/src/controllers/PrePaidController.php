<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 12/20/13
 * Time: 4:20 PM
 */

namespace Battambang\Loan;

use Input,
    Redirect,
    Request,
    View,
    DB,
    Config;
use Battambang\Cpanel\BaseController;
use UserSession;
use Battambang\Loan\Libraries\LoanPerformance;

class PrePaidController extends BaseController
{

    public function index()
    {
        $item = array('Action','Voucher Code','ACC Code','Date','Amount Pre-Paid','Amount Paid','Balance');

        $data['table'] = \Datatable::table()
            ->addColumn($item) // these are the column headings to be shown
            ->setUrl(route('api.pre_paid')) // this is the route where data will be retrieved
            ->setOptions('aLengthMenu', array(
                array(10, 25, 50, 100),
                array(10, 25, 50, 100)
            ))
            ->setOptions("sScrollY",300)
            ->setOptions("iDisplayLength", 10)// default show entries
            ->render('battambang/cpanel::layout.templates.template');
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.pre_paid_index'), $data)
        );
    }

    public function create()
    {
//        $data['disburseClient'] = $this->_getLoanAccount();
        return $this->renderLayout(
//            View::make(Config::get('battambang/loan::views.pre_paid_create'), $data)
            View::make(Config::get('battambang/loan::views.pre_paid_create'))
        );
    }

    public function edit($id)
    {
        try {
//            $data['disburseClient'] = $this->_getLoanAccount();
            $data['row'] = PrePaid::where('id', '=', $id)->first();
            $data['row']->voucher_code = substr($data['row']->voucher_code,-6);
            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.pre_paid_edit'), $data)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.pre_paid.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function show($id)
    {
        try {
            $arr['row'] = PrePaid::findOrFail($id);
            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.pre_paid_show'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.pre_paid.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function store()
    {
        $validation = $this->getValidationService('pre_paid');
        if ($validation->passes()) {
            if($this->_checkBal(Input::get('ln_disburse_client_id')) < Input::get('amount_pre_paid')){
                return Redirect::back()
                    ->with('error','Your amount pre-paid is bigger than your ending balance('.number_format($this->_checkBal(Input::get('ln_disburse_client_id'),2)).').');
            }

            $data = new PrePaid();
            $this->saveData($data);
            // User action
            \Event::fire('user_action.add', array('pre_paid'));
            return Redirect::back()
                ->with('success', trans('battambang/loan::pre_paid.create_success'));
        }
        return Redirect::back()->withInput()->withErrors($validation->getErrors());
    }

    public function update($id)
    {
        //try {
            $validation = $this->getValidationService('pre_paid');
            if ($validation->passes()) {
                if($this->_checkBal(Input::get('ln_disburse_client_id'))< Input::get('amount_pre_paid')){
                    return Redirect::back()
                        ->with('error','Your amount pre-paid is bigger than your ending balance('.number_format($this->_checkBal(Input::get('ln_disburse_client_id'),2)).').');
                }

                $data = PrePaid::findOrFail($id);
                $this->saveData($data,false);
                // User action
                \Event::fire('user_action.edit', array('pre_paid'));
                return Redirect::back()
                    ->with('success', trans('battambang/loan::pre_paid.update_success'));
            }
            return Redirect::back()->withInput()->withErrors($validation->getErrors());
        //} catch (\Exception $e) {
        //    return Redirect::route('loan.pre_paid.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        //}
    }

    public function destroy($id)
    {
        try {
            $data = PrePaid::where('id','=',$id);
            $data->delete();
            // User action
            \Event::fire('user_action.delete', array('pre_paid'));
            return Redirect::back()->with('success', trans('battambang/loan::pre_paid.delete_success'));
        } catch (\Exception $e) {
            return Redirect::route('loan.pre_paid.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    private function saveData($data,$store = true)
    {
        if ($store==true) {
            $data->id = \AutoCode::make('ln_pre_paid', 'id', UserSession::read()->sub_branch . '-', 5);

        }
        $data->activated_at = \Carbon::createFromFormat('d-m-Y',Input::get('date'))->toDateString();
        $data->ln_disburse_client_id = Input::get('ln_disburse_client_id');
        $data->amount_pre_paid = Input::get('amount_pre_paid');
        $data->bal = Input::get('amount_pre_paid');
        $data->cp_office_id = \UserSession::read()->sub_branch;
        $ccy = Disburse::where('id',substr(Input::get('ln_disburse_client_id'),0,11))->first();

        $data->voucher_code = \UserSession::read()->sub_branch
        . '-' . date('Y') . '-' . $ccy->cp_currency_id . '-' . sprintf('%06d', Input::get('voucher_code'));

        if($store){
            if($this->_existsAcc(Input::get('ln_disburse_client_id'))!=null){
                $data->bal = $this->_existsAcc(Input::get('ln_disburse_client_id'))->bal +Input::get('amount_pre_paid');
            }
        }else{
            if($this->_existsAcc(Input::get('ln_disburse_client_id'))!=null) {
                $data->bal = $this->_existsAcc(Input::get('ln_disburse_client_id'))->bal - $this->_existsAcc(Input::get('ln_disburse_client_id'))->amount_pre_paid + Input::get('amount_pre_paid');
            }
        }

        $data->save();
    }

    public function getDatatable()
    {
        $item = array('voucher_code','ln_disburse_client_id','activated_at','amount_pre_paid','amount_paid','bal');
        $arr = DB::table('view_pre_paid');
            //->whereRaw(' substr(ln_disburse_client_id,4) like "'.\UserSession::read()->sub_branch.'"');
            /*->where('perform_type','=','writeoff')->where('pre_paid_type','=','');*/

        return \Datatable::query($arr)
            ->addColumn('action', function ($model) {
                $model->voucher_code = substr($model->voucher_code,-6);
                if($model->amount_pre_paid==null) $model->amount_pre_paid = 0;
                return \Action::make()
                    ->edit(route('loan.pre_paid.edit', $model->id),$this->_checkAction($model->id,$model->ln_disburse_client_id))
                    ->delete(route('loan.pre_paid.destroy', $model->id),'',$this->_checkAction($model->id,$model->ln_disburse_client_id))
                    ->get();
            })
            ->showColumns($item)
            ->searchColumns($item)
            ->orderColumns($item)
            ->make();
    }

    private function _checkAction($id,$acc)
    {
        $data = PrePaid::where('ln_disburse_client_id', '=', $acc)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();
        /*$data1 = PrePaid::orderBy('id', 'desc')
            ->limit(1)
            ->first();*/

        if ($data->amount_paid == null and $data->id == $id) {
            return true;
        }
        return false;
    }

    private function _existsAcc($id){
        $bal = 0;
        $data = PrePaid::where('ln_disburse_client_id','=',$id)
            ->orderBy('id','desc')->limit(1)
            ->first();

        return $data;
    }

    private function _checkBal($id){
        $bal = 0;
        $data = Perform::where('ln_disburse_client_id','=',$id)
            ->orderBy('activated_at','desc')->limit(1)
            ->first();
        //if($data!=null) $bal = $data->bal;
        if($data!=null) $bal = $data->balance_principal + $data->balance_interest;

        return $bal;
    }

/*    private function _getLoanAccount()
    {
        $perform = array('');
        foreach (Perform::all() as $row) {
            $perform[] = $row->ln_disburse_client_id;
        }
        $data = DB::table('view_disburse_client')->whereIn('id',$perform)->orderBy('id', 'desc')->get();
        $arr = array();
        foreach ($data as $row) {
            $arr[$row->id] = $row->id . ' || ' . $row->client_kh_name . ' || ' . date('d-M-Y', strtotime($row->disburse_date));
        }
        return $arr;
    }*/

}