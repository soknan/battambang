<?php

namespace Battambang\Loan;

use Battambang\Loan\Facades\LookupValueList;
use Input,
    Redirect,
    Request,
    View,
    DB,
    Config;
use Battambang\Cpanel\BaseController;
use UserSession;
use Whoops\Example\Exception;

class DisburseController extends BaseController
{

    public function index()
    {
        $item = array('Action', 'Disburse#','Disburse_Date', 'Center', 'Staff_Name', 'Product', 'Acc_Type','Currency','Client#','Files');
        /*$data['btnAction'] = array('Add New' => route('loan.disburse.add'));*/
        $data['table'] = \Datatable::table()
            ->addColumn($item) // these are the column headings to be shown
            ->setUrl(route('api.disburse')) // this is the route where data will be retrieved
            ->setOptions('aLengthMenu', array(
                array(10, 25, 50, 100),
                array(10, 25, 50, 100)
            ))
            ->setOptions("sScrollY",300)
            ->setOptions("iDisplayLength", 10)// default show entries
            ->render('battambang/cpanel::layout.templates.template');
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.disburse_index'), $data)
        );
    }

    public function create()
    {
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.disburse_product'))
        );
    }

    public function disburseProduct()
    {
        $data = array();
        $tmp = array();
        $center = Center::where('id','=',Input::get('ln_center_id'))->get();

        $product = Product::where('id','=',Input::get('ln_product_id'))->get();
        //var_dump($product[0]['ln_lv_account_type_arr']);exit;
        foreach ($product as $row) {
            $data['ln_product_id'] = " and ln_product.id = '".$row->id."'";

            $data['currency_arr'] = $this->_getProCurrency(json_decode($row->cp_currency_id_arr));
            $data['fund_arr'] = $this->_getProFund(json_decode($row->ln_fund_id_arr));
            $data['account_type_arr'] = LookupValueList::jsonData(json_decode($row->ln_lv_account_type_arr));

            for ($row->min_installment; $row->min_installment <= $row->max_installment; $row->min_installment++) {
                $tmp[$row->min_installment] = $row->min_installment;
            }
            $data['installment'] = $tmp;
            $data['default_installment'] = $row->default_installment;

            for($i=1;$i <= $row->default_installment;$i++) {
                $int_fre[$i]= $i;
            }
            $data['int_fre'] = $int_fre;

            $data['min_interest'] = $row->min_interest;
            $data['max_interest'] = $row->max_interest;
            $data['default_interest'] = $row->default_interest;

            $data['ln_lv_repay_frequency'] = $row->ln_lv_repay_frequency;
            $data['ln_lv_interest_type'] = $row->ln_lv_interest_type;
            $data[] = $row;
        }

        foreach ($center as $row2) {
            $data['ln_staff_id'] = ' and ln_staff.id = ' . $row2->ln_staff_id;
            $data['ln_center_id'] = " and ln_center.id = '".$row2->id."'";

            if($data['ln_lv_repay_frequency']===4){
                $data['ln_lv_meeting'] = array($row2->meeting_monthly);
            }else{
                $data['ln_lv_meeting'] = array($row2->meeting_weekly);
            }

            $data[] = $row2;
        }
        //echo $data['ln_lv_meeting']; exit;
        $data['insPriPercentage'] = $this->_insPriPercentage();

        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.disburse_create'), $data)
        );
    }


    private function _getProFund($arr)
    {
        $data = DB::select("select * from ln_fund where id IN('" . implode("','", $arr) . "') ");
        foreach ($data as $row) {
            $tmp[$row->id] = $row->name;
        }
        return $tmp;
    }

    private function _getProCurrency($arr)
    {
        $data = DB::select("select * from cp_currency where id IN('" . implode("','", $arr) . "') ");
        foreach ($data as $row) {
            $tmp[$row->id] = $row->name;
        }
        return $tmp;
    }

    public function edit($id)
    {
        try {
            $arr = array();
            $data = Disburse::where('id','=',$id)->get();
            foreach ($data as $row) {
                $arr['ln_staff_id'] = ' and ln_staff.id= "'.$row->ln_staff_id.'"';
                $arr['ln_center_id'] = ' and ln_center.id= "'.$row->ln_center_id.'"';
                $arr['ln_product_id'] = ' and ln_product.id= "'.$row->ln_product_id.'"';

                for($i=1;$i <= $row->num_installment;$i++) {
                    $int_fre[$i]= $i;
                }
                $arr['int_fre'] = $int_fre;

                for($j=1;$j <= $row->num_payment;$j++) {
                    $ins_pri_fre[$j]= $j;
                }
                $arr['ins_pri_fre'] = $ins_pri_fre;

                $center = Center::where('id','=',$row->ln_center_id)->get();
                $product = Product::where('id','=',$row->ln_product_id)->get();
                foreach ($product as $row1) {
                    $arr['currency_arr'] = $this->_getProCurrency(json_decode($row1->cp_currency_id_arr));
                    $arr['fund_arr'] = $this->_getProFund(json_decode($row1->ln_fund_id_arr));
                    $arr['account_type_arr'] = LookupValueList::jsonData(json_decode($row1->ln_lv_account_type_arr));

                    for ($row1->min_installment; $row1->min_installment <= $row1->max_installment; $row1->min_installment++) {
                        $tmp[$row1->min_installment] = $row1->min_installment;
                    }
                    $arr['installment'] = $tmp;
                    $arr['default_installment'] = $row1->default_installment;

                    $arr['min_interest'] = $row1->min_interest;
                    $arr['max_interest'] = $row1->max_interest;
                    $arr['default_interest'] = $row1->default_interest;

                    $arr['ln_lv_repay_frequency'] = $row1->ln_lv_repay_frequency;
                    $arr['ln_lv_interest_type'] = $row1->ln_lv_interest_type;
                }

                foreach ($center as $row2) {
                    //$arr['ln_center_id'] = $row2->id;
                    if($arr['ln_lv_repay_frequency']=='4'){
                        $arr['ln_lv_meeting'] = array($row2->meeting_monthly);
                    }else{
                        $arr['ln_lv_meeting'] = array($row2->meeting_weekly);
                    }
                }
                $arr['row']= $row;
            }
            //$arr['row'] = $tmpAll;
            $arr['insPriPercentage'] = $this->_insPriPercentage();

            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.disburse_edit'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.disburse.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function show($id)
    {
        try {
            $arr = array();
            $data = Disburse::where('id','=',$id)->get();
            foreach ($data as $row) {
                $arr['ln_staff_id'] = ' and ln_staff.id= "'.$row->ln_staff_id.'"';
                $arr['ln_center_id'] = ' and ln_center.id= "'.$row->ln_center_id.'"';
                $arr['ln_product_id'] = ' and ln_product.id= "'.$row->ln_product_id.'"';

                for($i=1;$i <= $row->num_installment;$i++) {
                    $int_fre[$i]= $i;
                }
                $arr['int_fre'] = $int_fre;

                for($j=1;$j <= $row->num_payment;$j++) {
                    $ins_pri_fre[$j]= $j;
                }
                $arr['ins_pri_fre'] = $ins_pri_fre;

                $center = Center::where('id','=',$row->ln_center_id)->get();
                $product = Product::where('id','=',$row->ln_product_id)->get();
                foreach ($product as $row1) {
                    $arr['currency_arr'] = $this->_getProCurrency(json_decode($row1->cp_currency_id_arr));
                    $arr['fund_arr'] = $this->_getProFund(json_decode($row1->ln_fund_id_arr));
                    $arr['account_type_arr'] = LookupValueList::jsonData(json_decode($row1->ln_lv_account_type_arr));

                    for ($row1->min_installment; $row1->min_installment <= $row1->max_installment; $row1->min_installment++) {
                        $tmp[$row1->min_installment] = $row1->min_installment;
                    }
                    $arr['installment'] = $tmp;
                    $arr['default_installment'] = $row1->default_installment;

                    $arr['min_interest'] = $row1->min_interest;
                    $arr['max_interest'] = $row1->max_interest;
                    $arr['default_interest'] = $row1->default_interest;

                    $arr['ln_lv_repay_frequency'] = $row1->ln_lv_repay_frequency;
                    $arr['ln_lv_interest_type'] = $row1->ln_lv_interest_type;
                }

                foreach ($center as $row2) {
                    //$arr['ln_center_id'] = $row2->id;
                    if($arr['ln_lv_repay_frequency']=='4'){
                        $arr['ln_lv_meeting'] = array($row2->meeting_monthly);
                    }else{
                        $arr['ln_lv_meeting'] = array($row2->meeting_weekly);
                    }
                }
                $arr['row']= $row;
            }
            //$arr['row'] = $tmpAll;
            $arr['insPriPercentage'] = $this->_insPriPercentage();

            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.disburse_show'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.disburse.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function store()
    {
        $validation = $this->getValidationService('disburse');
        if ($validation->passes()) {
            $disburse = new Disburse();
            $disburse->id = \AutoCode::make('ln_disburse', 'id', UserSession::read()->sub_branch . '-', 6);
            $disburse_id = $disburse->id;
            $this->saveData($disburse);
            // User action
            \Event::fire('user_action.add', array('disburse'));
            return Redirect::route('loan.disburse.add')
                ->with('success', trans('battambang/loan::disburse.create_success')
                    .' '.\HTML::link(route('loan.disburse_client.add',$disburse_id),'Add Disburse Client')
                );

        }
        return Redirect::back()->withInput()->withErrors($validation->getErrors());
    }

    public function update($id)
    {
        try {
            $validation = $this->getValidationService('disburse');
            if ($validation->passes()) {
                $disburse = Disburse::findOrFail($id);
                $this->saveData($disburse,false);
                // User action
                \Event::fire('user_action.edit', array('disburse'));
                return Redirect::route('loan.disburse.edit',$disburse->id)
                    ->with('success', trans('battambang/loan::disburse.update_success'));
            }
            return Redirect::back()->withInput()->withErrors($validation->getErrors());
        } catch (\Exception $e) {
            return Redirect::route('loan.disburse.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function destroy($id)
    {
        try {
            Disburse::find($id)->delete();
            // User action
            \Event::fire('user_action.delete', array('disburse'));
            return Redirect::back()->with('success', trans('battambang/loan::disburse.delete_success'));
        } catch (\Exception $e) {
            return Redirect::route('loan.disburse.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    private function saveData($data,$store = true)
    {
        /*if($store){
            $data->id = \AutoCode::make('ln_disburse', 'id', UserSession::read()->sub_branch . '-', 6);
        }*/
        $data->ln_center_id = Input::get('ln_center_id');
        $data->ln_lv_meeting_schedule = Input::get('ln_lv_meeting_schedule');
        $data->ln_staff_id = Input::get('ln_staff_id');
        $data->ln_product_id = Input::get('ln_product_id');
        $data->disburse_date = \Carbon::createFromFormat('d-m-Y',Input::get('disburse_date'))->toDateString();
        $data->ln_lv_account_type = Input::get('ln_lv_account_type');
        $data->cp_currency_id = Input::get('cp_currency_id');
        $data->num_installment = Input::get('num_installment');
        $data->installment_frequency = Input::get('installment_frequency');
        $data->num_payment = Input::get('num_payment');
        $data->installment_principal_frequency = Input::get('installment_principal_frequency');
        $data->installment_principal_percentage = Input::get('installment_principal_percentage');
        $data->interest_rate = Input::get('interest_rate');
        $data->ln_fund_id = Input::get('ln_fund_id');
        $data->round_schedule = Input::get('ln_lv_round_type');
        $data->cp_office_id = \UserSession::read()->sub_branch;
        if(Input::get('first_due_date')!=null){
            $data->first_due_date = \Carbon::createFromFormat('d-m-Y',Input::get('first_due_date'))->toDateString();
        }else{
            $data->first_due_date = null;
        }

        $attach_file = Input::file('attach_file');
        if (!empty($attach_file)) {
            $destinationPath = public_path() . '/packages/battambang/loan/disburse_files/';
            $filename = $data->id .'.'.$attach_file->getClientOriginalExtension();
            $attach_file->move($destinationPath, $filename);
            $path = \URL::to('/') . '/packages/battambang/loan/disburse_files/' . $filename;
            $data->attach_file = $path;
        } else {
            unset($attach_file);
        }

        $data->save();
    }

    public function attachFile($id){
        $data['row']=Disburse::find($id);
        return $this->renderLayout(
            View::make('battambang/loan::disburse.attach',$data)
        );
    }

    public function updateAttachFile($id){
        try{
            $attach_file = Input::file('attach_file');
            $o_attach_file = Disburse::where('id',$id)->first()->attach_file;
            if (!empty($attach_file)) {
                $ext = $attach_file->getClientOriginalExtension();
                $size = $attach_file->getClientSize();

                if($size == 0) return Redirect::back()->withInput()->with('error','Your files size > 2MB');
                if (!empty($attach_file) and !in_array($ext, array('zip','rar','pdf','doc'))) {
                    return Redirect::back()->withInput()->with('error','Your files extension must be zip, rar,pdf,doc.');
                }
                $destinationPath = public_path() . '/packages/battambang/loan/disburse_files/';
                $filename = $id .'.'.$attach_file->getClientOriginalExtension();
                $attach_file->move($destinationPath, $filename);
                $path = \URL::to('/') . '/packages/battambang/loan/disburse_files/' . $filename;
                $attach_file = $path;
            } else {
                $attach_file = $o_attach_file;
            }

            DB::select("UPDATE ln_disburse SET attach_file = '".$attach_file."' WHERE id = '".$id."'");
            return Redirect::back()->with('success', trans('battambang/loan::disburse.update_success'));
        }catch (Exception $e){
            return Redirect::route('loan.disburse.index')->with('error', $e->getMessage());
        }
    }

    public function getDatatable()
    {
        $item = array('id','disburse_date', 'center_name', 'staff_en_name', 'product_name', 'account_type_name','currency_code');
        $arr = DB::table('view_disburse')->where('cp_office_id','=',\UserSession::read()->sub_branch);

        return \Datatable::query($arr)
            ->addColumn('action', function ($model) {
                return \Action::make()
                    ->edit(route('loan.disburse.edit', $model->id),$this->_checkStatus($model->id))
                    ->delete(route('loan.disburse.destroy', $model->id),'',$this->_checkStatus($model->id))
                    ->show(route('loan.disburse.show', $model->id))
                    ->custom(route('loan.disburse_client.add',$model->id),'Add New Client',$this->_checkDisburse($model->id))
                    ->custom(route('loan.disburse_client.index',$model->id),'Client List')
                    ->custom(route('loan.disburse.attach_file',$model->id),'Add Attach File')
                    ->get();
            })
            ->showColumns($item)
            ->searchColumns($item)
            ->orderColumns($item)
            ->addColumn('Client #', function ($model) {
                $data = DisburseClient::where('ln_disburse_id','=',$model->id)->count();
                $class='danger';
                $title='remove-sign';
                $client=0;
                if($data > 0){
                    $client=$data;
                    if($model->account_type_name == 'Single'){
                        $title='ok-sign';
                        $class='success';
                    }else{
                        if($client>1){
                            $title='ok-sign';
                            $class='success';
                        }else{
                            $title='exclamation-sign';
                            $class='warning';
                        }
                    }
                }
                return '<a class="btn btn-'.$class.' btn-xs" href="#" role="button"><span class="glyphicon glyphicon-'.$title.'"></span> '.$client.'</a>';
            })
            ->addColumn('Files',function($model){
                if(empty($model->attach_file) or is_null($model->attach_file)){
                    return;
                }
                return '<a href='.$model->attach_file.' onClick="window.open(this);return false;">Link</a>';
            })
            ->make();
    }

    private function _insPriPercentage(){
        $arr = array();
        for ($i=10;$i<=100 ; $i+=10) {
            $arr[$i] = $i;
        }
        return $arr;
    }

    private function _checkStatus($disburse){
        $data = DisburseClient::where('ln_disburse_id','=',$disburse)->count();
        if($data > 0){
            return false;
        }
        return true;
    }

    private function _checkDisburse($id){
        $dis = Disburse::where('id','=',$id)->first();
        $disClient = DisburseClient::where('ln_disburse_id','=',$id)->count();
        if($disClient >= 1){
            if($dis->ln_lv_account_type == 2){
                return true;
            }
            return false;
        }
        return true;
    }
}