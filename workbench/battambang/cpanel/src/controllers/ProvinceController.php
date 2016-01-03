<?php
namespace Battambang\Cpanel;

use Battambang\Cpanel\Validators\provinceValidator;
use Input,
    Redirect,
    View,
    DB,
    Config,
    Action;
use Battambang\Cpanel\Location;

class ProvinceController extends BaseController
{

    public function index()
    {
        $item = array('Action', 'ID', 'Kh Name','En Name');
//        $data['btnAction'] = array('Add New' => route('cpanel.province.create'));
        $data['table'] = \Datatable::table()
            ->addColumn($item) // these are the column headings to be shown
            ->setUrl(route('api.province')) // this is the route where data will be retrieved
            ->setOptions('aLengthMenu', array(
                array(10, 25, 50, 100),
                array(10, 25, 50, 100)
            ))
            ->setOptions("sScrollY",300)
            ->setOptions("iDisplayLength", 10)// default show entries
            ->render('battambang/cpanel::layout.templates.template');
        return $this->renderLayout(
            View::make(Config::get('battambang/cpanel::views.province_index'), $data)
        );
    }

    public function create()
    {
        return $this->renderLayout(
            View::make(Config::get('battambang/cpanel::views.province_create'))
        );
    }

    public function edit($id)
    {
        try {
            $arr['row'] = DB::table('cp_location')->where('id','=',$id)->first();
            return $this->renderLayout(
                View::make(Config::get('battambang/cpanel::views.province_edit'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('cpanel.province.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function show($id)
    {
        try {
            $arr['row'] = Location::findOrFail($id);
            return $this->renderLayout(
                View::make(Config::get('battambang/cpanel::views.province_show'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('cpanel.province.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function store()
    {
        $validator = ProvinceValidator::make();

        if ($validator->passes()) {

            $data = new Location();
            $this->saveData($data);
            \Event::fire('user_action.add', array('province'));

            return Redirect::back()
                ->with('success', trans('battambang/cpanel::msg.create_success'));
        }
        return Redirect::back()->withInput()->withErrors($validator->errors());
    }

    public function update($id)
    {
        try {
            $validator = ProvinceValidator::make();
            if ($validator->passes()) {

                $data = Location::findOrFail($id);
                $this->saveData($data, false);
                \Event::fire('user_action.edit', array('province'));

                return Redirect::back()
                    ->with('success', trans('battambang/cpanel::msg.update_success'));
            }
            return Redirect::back()->withInput()->withErrors($validator->errors());
        } catch (\Exception $e) {
            return Redirect::route('cpanel.province.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function destroy($id)
    {
        try {

            $data = Location::findOrFail($id);
            $data->delete();
            \Event::fire('user_action.delete', array('province'));

            return Redirect::back()->with('success', trans('battambang/cpanel::msg.delete_success'));
        } catch (\Exception $e) {
            return Redirect::route('cpanel.province.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    private function saveData($data, $store = true)
    {
        if ($store) {
            $data->id = Input::get('id');
        }
        $data->en_name = Input::get('en_name');
        $data->kh_name = Input::get('kh_name');
        $data->cp_location_id = '';
        $data->save();
    }

    public function getDatatable()
    {
        $item = array('id', 'kh_name','en_name');
        $arr = DB::table('cp_location')->where('cp_location_id','=','')->orderBy('id');

        return \Datatable::query($arr)
            ->addColumn(
                'action',
                function ($model) {
                    //return str_limit($model->kh_name, 3);
                    return Action::make()
                        ->edit(route('cpanel.province.edit', $model->id))
                        ->delete(route('cpanel.province.destroy', $model->id), $model->id)
                        /*->show(route('cpanel.province.show', $model->id))*/
                        ->get();
                }
            )
            ->showColumns($item)
            ->searchColumns($item)
            ->orderColumns($item)
            ->make();
    }

}