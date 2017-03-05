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

class WriteOffController extends BaseController
{

    public function index()
    {
        $item = array('Action', 'ACC Code','WOL Date','WOL Pri','WOL int');

        $data['table'] = \Datatable::table()
            ->addColumn($item) // these are the column headings to be shown
            ->setUrl(route('api.write_off')) // this is the route where data will be retrieved
            ->setOptions('aLengthMenu', array(
                array(10, 25, 50, 100),
                array(10, 25, 50, 100)
            ))
            ->setOptions("sScrollY",300)
            ->setOptions("iDisplayLength", 10)// default show entries
            ->render('battambang/cpanel::layout.templates.template');
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.write_off_index'), $data)
        );
    }

    public function create()
    {
//        $data['disburseClient'] = $this->_getLoanAccount();
        return $this->renderLayout(
//            View::make(Config::get('battambang/loan::views.write_off_create'), $data)
            View::make(Config::get('battambang/loan::views.write_off_create'))
        );
    }

    public function edit($id)
    {
        try {
//            $data['disburseClient'] = $this->_getLoanAccount();
            $data['row'] = Perform::where('id', '=', $id)->first();
            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.write_off_edit'), $data)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.repayment.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function show($id)
    {
        try {
            $arr['row'] = Perform::findOrFail($id);
            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.repayment_show'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.repayment.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function store()
    {
        $validation = $this->getValidationService('write_off');
        $perform = new LoanPerformance();
        $msg = '';
        $perform_date = \Carbon::createFromFormat('d-m-Y',Input::get('writeoff_date'))->toDateString() ;
        if ($validation->passes()) {
            $data = $perform->get(Input::get('ln_disburse_client_id'), $perform_date);
            $totalArrears = $data->_arrears['cur']['principal'] + $data->_arrears['cur']['interest'];
            $currency = Currency::where('id','=',$data->_disburse->cp_currency_id)->first();

            if($data->_perform_type=='writeoff'){
                $error = 'Already Perform It.!';
                return Redirect::back()->withInput()->with('error',$error);
            }
            if($data->_maturity_date > $perform_date){
                $error = 'Your Perform Date must bigger than maturity date! '.\Carbon::createFromFormat('Y-m-d',$data->_maturity_date)->format('d-M-Y');
                return Redirect::back()->withInput()->with('error',$error);
            }
            /*if( $data->_new_due['product_status']!=5){
                $error = 'Not much with number of days to write-off (365 days after loss)';
                return Redirect::back()->withInput()->with('error',$error);
            }*/

            if (Input::has('confirm')) {
                $msg = 'Due Date = <strong>' . $data->_due['date'] . '</strong> ,</br> '
                    . 'Pri Amount = <strong>' . $data->_arrears['cur']['principal'] .'</strong>, '
                    . 'Int Amount = <strong>' . $data->_arrears['cur']['interest'] .'</strong>.</br>'
                    . 'Total Amount = <strong>' . ($data->_arrears['cur']['principal'] + $data->_arrears['cur']['interest']) .' '.$currency->code. '</strong> , '
                    . 'Penalty Amount = <strong>' . $data->_arrears['cur']['penalty'] . '</strong>.
                <P>Note : ' . $data->error . '</P>';

                return Redirect::back()
                    ->withInput()
                    ->with('info', $msg);
            }

            $ref = Input::file('writeoff_ref');
            //$refPath = \URL::to('/') . '/packages/battambang/loan/write_off_ref/';
            if (!empty($ref)) {
                $destinationPath = public_path() . '/packages/battambang/loan/write_off_ref/';
                $filename = $data->_disburse_client_id.'-'.$data->_activated_at.'.'.$ref->getClientOriginalExtension();
                $ref->move($destinationPath, $filename);
                //$refPath = \URL::to('/') . '/packages/battambang/loan/write_off_ref/' . $filename;
            }

            $data->_perform_type='writeoff';
            $data->_new_due['product_status']=5;
            $data->_new_due['product_status_date']=$perform_date;
            $data->_current_product_status=5;
            $data->_current_product_status_date= $perform_date;

            $data->_repayment['cur']['date'] = '';
            $data->_repayment['cur']['voucher_id'] = '';
            $data->_repayment['cur']['status'] = '';
            $data->_repayment['cur']['principal'] = '';
            $data->_repayment['cur']['interest'] = '';
            $data->_repayment['cur']['fee'] = '';
            $data->_repayment['cur']['penalty'] = '';
            $data->_repayment['cur']['type'] = '';
            $perform->save();
            // User action
            \Event::fire('user_action.add', array('writeoff'));
            return Redirect::back()
                ->with('success', trans('battambang/loan::write_off.create_success'));
        }
        return Redirect::back()->withInput()->withErrors($validation->getErrors());
    }

    public function update($id)
    {
        try {
            $validation = $this->getValidationService('write_off');
            $perform = new LoanPerformance();
            $msg = '';
            $perform_date = \Carbon::createFromFormat('d-m-Y',Input::get('writeoff_date'))->toDateString() ;
            if ($validation->passes()) {
                $data = $perform->get(Input::get('ln_disburse_client_id'), $perform_date);
                $totalArrears = $data->_arrears['cur']['principal'] + $data->_arrears['cur']['interest'];
                $currency = Currency::where('id','=',$data->_disburse->cp_currency_id)->first();

                if($data->_perform_type=='writeoff' and $data->_id != $id){
                    $error = 'Already Perform It.!';
                    return Redirect::back()->withInput()->with('error',$error);
                }
                if($data->_maturity_date > $perform_date){
                    $error = 'Your Perform Date must bigger than maturity date! '.\Carbon::createFromFormat('Y-m-d',$data->_maturity_date)->format('d-M-Y');
                    return Redirect::back()->withInput()->with('error',$error);
                }
                if( $data->_new_due['product_status']!=5){
                    $error = 'Not much with number of days';
                    return Redirect::back()->withInput()->with('error',$error);
                }

                if (Input::has('confirm')) {
                    $msg = 'Due Date = <strong>' . $data->_due['date'] . '</strong> ,</br> '
                        . 'Pri Amount = <strong>' . $data->_arrears['cur']['principal'] .'</strong>, '
                        . 'Int Amount = <strong>' . $data->_arrears['cur']['interest'] .'</strong>.</br>'
                        . 'Total Amount = <strong>' . ($data->_arrears['cur']['principal'] + $data->_arrears['cur']['interest']) .' '.$currency->code. '</strong> , '
                        . 'Penalty Amount = <strong>' . $data->_arrears['cur']['penalty'] . '</strong>.
                <P>Note : ' . $data->error . '</P>';

                    return Redirect::back()
                        ->withInput()
                        ->with('info', $msg);
                }

                Perform::where('id','=',$id)->delete();
                $ref = Input::file('writeoff_ref');
                //$refPath = \URL::to('/') . '/packages/battambang/loan/write_off_ref/';
                if (!empty($ref)) {
                    $destinationPath = public_path() . '/packages/battambang/loan/write_off_ref/';
                    $filename = $data->_disburse_client_id.'-'.$data->_activated_at.'.'.$ref->getClientOriginalExtension();
                    $ref->move($destinationPath, $filename);
                    //$refPath = \URL::to('/') . '/packages/battambang/loan/write_off_ref/' . $filename;
                }

                $data->_perform_type='writeoff';
                $data->_new_due['product_status']=5;
                $data->_new_due['product_status_date']=$perform_date;
                $data->_current_product_status=5;
                $data->_current_product_status_date= $perform_date;
                $perform->save();
                // User action
                \Event::fire('user_action.edit', array('writeoff'));
                return Redirect::back()
                    ->with('success', trans('battambang/loan::write_off.update_success'));
            }
            return Redirect::back()->withInput()->withErrors($validation->getErrors());
        } catch (\Exception $e) {
            return Redirect::route('loan.write_off.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function destroy($id)
    {
        try {
            $data = Perform::where('ln_disburse_client_id','=',$id)->where('perform_type','=','writeoff');
            $data->delete();
            // User action
            \Event::fire('user_action.delete', array('writeoff'));
            return Redirect::back()->with('success', trans('battambang/loan::write_off.delete_success'));
        } catch (\Exception $e) {
            return Redirect::route('loan.write_off.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    /*private function saveData($data)
    {
        $data->num_day = Input::get('num_day');
        $data->activated_at = Input::get('activated_at');

        $data->save();
    }*/

    public function getDatatable()
    {
        $item = array('ln_disburse_client_id','new_due_product_status_date','arrears_principal','arrears_interest');
        $arr = DB::table('view_writeoff')
            ->where('cp_office_id','=',\UserSession::read()->sub_branch);
            /*->where('perform_type','=','writeoff')->where('repayment_type','=','');*/

        return \Datatable::query($arr)
            ->addColumn('action', function ($model) {
                return \Action::make()
                    /*->edit(route('loan.write_off.edit', $model->ln_disburse_client_id))*/
                    ->delete(route('loan.write_off.destroy', $model->ln_disburse_client_id))
                    ->get();
            })
            ->showColumns($item)
            ->searchColumns($item)
            ->orderColumns($item)
            ->make();
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