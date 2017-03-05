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

class ClientController extends BaseController
{

    public function index()
    {
        $item = array('Action', 'Client ID', 'Name(EN)', 'Name(Kh)','Gender','Date of Birth','Photo');
//        $data['btnAction'] = array('Add New' => route('loan.client.create'));
        $data['table'] = \Datatable::table()
            ->addColumn($item) // these are the column headings to be shown
            ->setUrl(route('api.client')) // this is the route where data will be retrieved
            ->setOptions('aLengthMenu', array(
                array(10, 25, 50, 100),
                array(10, 25, 50, 100)
            ))
            ->setOptions("sScrollY",300)
            ->setOptions("iDisplayLength", 10)// default show entries
            ->render('battambang/cpanel::layout.templates.template');
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.client_index'), $data)
        );
    }

    public function create()
    {
        return $this->renderLayout(
            View::make(Config::get('battambang/loan::views.client_create'))
        );
    }

    public function edit($id)
    {
        try {
            $arr['row'] = ClientLoan::findOrFail($id);
            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.client_edit'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.client.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function show($id)
    {
        try {
            $arr['row'] = ClientLoan::findOrFail($id);
            return $this->renderLayout(
                View::make(Config::get('battambang/loan::views.client_show'), $arr)
            );
        } catch (\Exception $e) {
            return Redirect::route('loan.client.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    public function store()
    {
        //return 'aa';
        $validation = $this->getValidationService('client');
        if ($validation->passes()) {
//            $data = $this->getData();
//            $data['id'] = \AutoCode::make('ln_client', 'id', '', 1);
//            $data['cp_office_id'] = UserSession::read()->branch;
//            if (empty($data['attach_photo'])) {
//                $data['attach_photo'] = \URL::to('/') . '/packages/battambang/cpanel/img/cp_noimage.jpg';
//            } else {
//                $destinationPath = public_path() . '/packages/battambang/loan/client_photo/';
//                $filename = UserSession::read()->branch . '-' . Input::file('attach_photo')->getClientOriginalName();
//                Input::file('attach_photo')->move($destinationPath, $filename);
//                $data['attach_photo'] = \URL::to('/') . '/packages/battambang/loan/client_photo/' . $filename;
//            }
//            ClientModel::insert($data);
            $data = new ClientLoan();
            $photo = Input::file('attach_photo');
            $photoPath = \URL::to('/') . '/packages/battambang/cpanel/img/cp_noimage.jpg';
            if (!empty($photo)) {
                $destinationPath = public_path() . '/packages/battambang/loan/client_photo/';
                $filename = UserSession::read()->sub_branch . '-' .
                    Input::get('en_last_name').Input::get('en_first_name').'-'.Input::get('dob').'.'. $photo->getClientOriginalExtension();
                $photo->move($destinationPath, $filename);
                $photoPath = \URL::to('/') . '/packages/battambang/loan/client_photo/' . $filename;
            }

            $this->saveData($data, $photoPath);
// User action
            \Event::fire('user_action.add', array('client'));
            return Redirect::back()
                ->with('success', trans('battambang/loan::client.create_success'));

        }
        return Redirect::back()->withInput()->withErrors($validation->getErrors());
    }

    public function update($id)
    {
        //try {
            $validation = $this->getValidationService('client');
            if ($validation->passes()) {
//                $data = $this->getData();
//                if (!empty($data['attach_photo'])) {
//                    $destinationPath = public_path() . '/packages/battambang/loan/client_photo/';
//                    $filename = UserSession::read()->branch . '-' . Input::file('attach_photo')->getClientOriginalName();
//                    Input::file('attach_photo')->move($destinationPath, $filename);
//                    $data['attach_photo'] = \URL::to('/') . '/packages/battambang/loan/client_photo/' . $filename;
//                } else {
//                    unset($data['attach_photo']);
//                }
//                ClientModel::where('id', '=', $id)->update($data);
                $data = ClientLoan::findOrFail($id);
                $photo = Input::file('attach_photo');
                $photoPath = $data->attach_photo;
                if (!empty($photo)) {
                    $destinationPath = public_path() . '/packages/battambang/loan/client_photo/';
                    $filename = UserSession::read()->sub_branch . '-' .
                        Input::get('en_last_name').Input::get('en_first_name').'-'.Input::get('dob').'.'. $photo->getClientOriginalExtension();
                    $photo->move($destinationPath, $filename);
                    $photoPath = \URL::to('/') . '/packages/battambang/loan/client_photo/' . $filename;
                }

                $this->saveData($data, $photoPath, false);
// User action
                \Event::fire('user_action.edit', array('client'));
                return Redirect::back()
                    ->with('success', trans('battambang/loan::client.update_success'));
            }
            return Redirect::back()->withInput()->withErrors($validation->getErrors());
        //} catch (\Exception $e) {
        //    return Redirect::route('loan.client.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        //}
    }

    public function destroy($id)
    {
        try {
//            ClientModel::find($id)->delete();

            $data = ClientLoan::findOrFail($id);
            $data->delete();
            // User action
            \Event::fire('user_action.delete', array('client'));
            return Redirect::back()->with('success', trans('battambang/loan::client.delete_success'));
        } catch (\Exception $e) {
            return Redirect::route('loan.client.index')->with('error', trans('battambang/cpanel::db_error.fail'));
        }
    }

    private function saveData($data, $photoPath, $store = true)
    {
        if ($store) {
            $data->id = \AutoCode::make('ln_client', 'id', UserSession::read()->sub_branch . '-', 4);
            $data->cp_office_id = \UserSession::read()->sub_branch;
        }
        $data->en_first_name = Input::get('en_first_name');
        $data->en_last_name = Input::get('en_last_name');
        $data->en_nick_name = Input::get('en_nick_name');
        $data->kh_first_name = Input::get('kh_first_name');
        $data->kh_last_name = Input::get('kh_last_name');
        $data->kh_nick_name = Input::get('kh_nick_name');
        $data->ln_lv_gender = Input::get('ln_lv_gender');
        $data->dob = \Carbon::createFromFormat('d-m-Y',Input::get('dob'))->toDateString() ;
        $data->place_birth = Input::get('place_birth');
        $data->ln_lv_nationality = Input::get('ln_lv_nationality');
        $data->attach_photo = $photoPath;
        $data->save();
    }

    public function getDatatable()
    {
        $item = array('id', 'en_name', 'kh_name','gender_code','dob');
        $arr = DB::table('view_client')->where('cp_office_id','=',\UserSession::read()->sub_branch);

        return \Datatable::query($arr)
            ->addColumn('action', function ($model) {
                return \Action::make()
                    ->edit(route('loan.client.edit', $model->id))
                    ->delete(route('loan.client.destroy', $model->id),'',$this->_checkStatus($model->id))
                    ->get();
            })
            ->showColumns($item)
            ->addColumn('photo', function ($model) {
                return '<img src = "' . $model->attach_photo . '" width = "60px" > ';
            })
            ->searchColumns($item)
            ->orderColumns($item)
            ->make();
    }

    private function _checkStatus($id){
        $data = DisburseClient::where('ln_client_id','=',$id)->count();
        if($data >0){
            return false;
        }
        return true;
    }

    public function showModal(){
        return \Response::json(array('success' => true, 'payload' => \View::make('battambang/loan::client.client_modal')));
    }
}