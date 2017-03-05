<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 12/20/13
 * Time: 4:20 PM
 */

namespace Battambang\Loan;

use Battambang\Cpanel\LookupValue;
use Battambang\Cpanel\WorkDay;
use Input,
    Redirect,
    View,
    DB,
    Config;
use Battambang\Cpanel\BaseController;

class CenterController extends BaseController
{

    public function index()
    {
        //return \LookupValueList::getLocation();
        $item = array('Action', 'ID', 'Joining Date', 'Name', 'Meeting Weekly', 'Meeting Monthly', 'Last Name', 'First Name');
//        $data['btnAction'] = array('Add New' => route('loan.center.create'));
        $data['table'] = \Datatable::table()
            ->addColumn($item)// these are the column headings to be shown
            ->setUrl(route('api.center'))// this is the route where data will be retrieved
            ->setOptions('aLengthMenu', array(
                array(10, 25, 50, 100),
                array(10, 25, 50, 100)
            ))
            ->setOptions("sScrollY",300)
            ->setOptions("iDisplayLength", 10)// default show entries
            ->render('battambang/cpanel::layout.templates.template');
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.center_index'), $data)
        );
    }

    public function create()
    {
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.center_create'), $this->_meetingSchedule())
        );
    }

    public function edit($id)
    {
        try {
            $meetingSchedule = $this->_meetingSchedule();
            $arr['work_week'] = $meetingSchedule['work_week'];
            $arr['work_month'] = $meetingSchedule['work_month'];

            $arr['row'] = Center::findOrFail($id);
            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.center_edit'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.center.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function show($id)
    {
        try {
            $arr['row'] = Center::findOrFail($id);
            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.center_show'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.center.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function store()
    {
        $validation = $this->getValidationService('center');
        if ($validation->passes()) {
//            $data = $this->getData();
//            $data['id'] = \AutoCode::make('ln_center', 'id', '', 2);
//            $data['cp_office_id'] = \UserSession::read()->branch;
//            Center::insert($data);

            $data = new Center();
            $this->saveData($data);
// User action
            \Event::fire('user_action.add', array('center'));
            return Redirect::back()
                ->with('success', trans('battambang/loan::center.create_success'));

        }
        return Redirect::back()->withInput()->withErrors($validation->getErrors());
    }

    public function update($id)
    {
        try {
            $validation = $this->getValidationService('center');
            if ($validation->passes()) {
//                $data = $this->getData();
//                Center::where('id', '=', $id)->update($data);

                $data = Center::findOrFail($id);
                $this->saveData($data, false);
// User action
                \Event::fire('user_action.edit', array('center'));
                return Redirect::back()
                    ->with('success', trans('battambang/loan::center.update_success'));
            }
            return Redirect::back()->withInput()->withErrors($validation->getErrors());
        } catch (\Exception $e) {
            return Redirect::route('loan.center.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function destroy($id)
    {
        try {
//            Center::find($id)->delete();

            $data = Center::findOrFail($id);
            $data->delete();
// User action
            \Event::fire('user_action.delete', array('center'));
            return Redirect::back()->with('success', trans('battambang/loan::center.delete_success'));
        } catch (\Exception $e) {
            return Redirect::route('loan.center.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    private function saveData($data, $store = true)
    {
        if ($store) {
            $data->id = \AutoCode::make('ln_center', 'id', \UserSession::read()->sub_branch . '-', 3);
        }
        $data->name = Input::get('name');
        $data->meeting_weekly = Input::get('meeting_weekly');
        $data->meeting_monthly = Input::get('meeting_monthly');
        $data->joining_date = \Carbon::createFromFormat('d-m-Y', Input::get('joining_date'))->toDateString();
        $data->ln_lv_geography = Input::get('ln_lv_geography');
        $data->cp_location_id = Input::get('cp_location_id');
        $data->address = Input::get('address');
        $data->des = Input::get('des');
        $data->ln_staff_id = Input::get('ln_staff_id');
        $data->cp_office_id = Input::get('cp_office_id');
        $data->save();


    }

//    private function getData()
//    {
//        return array(
//            'name' => Input::get('name'),
//            'meeting_weekly' => Input::get('meeting_weekly'),
//            'meeting_monthly' => Input::get('meeting_monthly'),
//            'joining_date' => Input::get('joining_date'),
//            'ln_lv_geography' => Input::get('ln_lv_geography'),
//            'cp_location_id' => Input::get('cp_location_id'),
//            'address' => Input::get('address'),
//            'telephone'=>Input::get('telephone'),
//            'ln_staff_id' => Input::get('ln_staff_id'),
//
//        );
//    }

    public function getDatatable()
    {
        $item = array('id', 'joining_date', 'center_name', 'meeting_weekly_name', 'meeting_monthly_name', 'staff_kh_last_name', 'staff_kh_first_name');
        $arr = DB::table('view_center')
            ->where('office_id', '=', \UserSession::read()->sub_branch);

        return \Datatable::query($arr)
            ->addColumn('action', function ($model) {
                return \Action::make()
                    ->edit(route('loan.center.edit', $model->id))
                    ->delete(route('loan.center.delete', $model->id), '', $this->_checkStatus($model->id))
                    ->get();
            })
            ->showColumns($item)
            ->searchColumns($item)
            ->orderColumns($item)
            ->make();
    }

    private function _checkStatus($id)
    {
        $data = Disburse::where('ln_center_id', '=', $id)->count();
        if ($data > 0) {
            return false;
        }
        return true;
    }

    private function _meetingSchedule()
    {
        $arr = [];
        $workday = WorkDay::orderBy('activated_at', 'DESC')->limit(1)->first();
        $tmp_week = $workday->work_week;
        $tmp_month = $workday->work_month;

        $arr['work_week'] = \LookupValueList::getBy('meeting weekly');
        if ($tmp_week == 'MF'){unset($arr['work_week'][32]); unset($arr['work_week'][118]);}
        if ($tmp_week == 'MS') unset($arr['work_week'][118]);

        $work_month = \LookupValueList::getBy('meeting monthly');

        foreach ($work_month as $key => $row) {
            if ($row <= $tmp_month) {
                $arr['work_month'][$key] = $row;
            }
        }
        return $arr;
    }

}