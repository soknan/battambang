<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2/12/14
 * Time: 2:38 PM
 */

namespace Battambang\Cpanel;


use Battambang\Cpanel\BaseController;
use Battambang\Cpanel\Company;
use Battambang\Cpanel\Facades\GetLists;
use Battambang\Cpanel\Facades\UserSession;
use Battambang\Cpanel\Libraries\Report;
use DB;
use Input;

class RptUserActionController extends BaseController
{
    public function index()
    {
        $data['userLst'] = User::lists('username','id');
        return $this->renderLayout(
            \View::make('battambang/cpanel::rpt_user_action.index',$data)
        );
    }

    public function report()
    {
        $data = array();
        $com = Company::all()->first();
        $data['company_name'] = $com->en_name;


        $data['date_from'] = \Carbon::createFromFormat('d-m-Y',Input::get('date_from'))->toDateString();
        $data['date_to'] = \Carbon::createFromFormat('d-m-Y',\Input::get('date_to'))->toDateString();
        $data['cp_office']= \Input::get('cp_office_id');
        $data['event'] = \Input::get('event');
        $data['user'] = \Input::get('user');


        if($data['date_from'] > $data['date_to']){
            return \Redirect::back()->withInput()->with('error', 'Date From > Date to');
        }

        $condition = ' 1=1 ';
        $condition.= " AND a.created_at BETWEEN
                        STR_TO_DATE('".$data['date_from']." " . " 00:00:00" . "','%Y-%m-%d %H:%i:%s')
                        AND STR_TO_DATE('".$data['date_to']." " . " 23:59:59" . "','%Y-%m-%d %H:%i:%s') ";
        if ($data['cp_office'] != 'all') {
            $condition .= " AND a.cp_office_id  IN('" . implode("','",$data['cp_office']) . "')";
            $tmp_office='';
            foreach ($data['cp_office'] as $office) {
                $tmp_office .=$office.' '.GetLists::getBranchOfficeBy($office).', ';
            }

            $data['cp_office'] = $tmp_office;
        }

        if($data['event']!='all'){
            $condition.=" AND a.event = '".$data['event']."' ";
        }

        if($data['user']!='all'){
            $condition.=" AND a.cp_user_id = '".$data['user']."' ";
        }

        if(\Auth::user()->id!='1'){
            $condition.=" AND a.cp_user_id <> 1 ";
        }

        $data['result'] = DB::select("select a.cp_office_id,f.en_name,a.cp_user_id,u.username,a.`event`,a.page
,a.package_type,STR_TO_DATE(a.created_at,'%Y-%m-%d %H:%i:%s') created_at,a.detail
 FROM cp_user_action a
INNER JOIN cp_office f on f.id = a.cp_office_id
INNER JOIN cp_user u on u.id = a.cp_user_id  where $condition ORDER by a.created_at desc");
// User action
        \Event::fire('user_action.report', array('rpt_user_action'));
        if (count($data['result']) <= 0) {
            return \Redirect::back()->withInput(Input::except('cp_office_id'))->with('error', 'No Data Found !.');
        }

       //var_dump($data['result']);
       //exit;

        \Report::setReportName('Rpt User Action')
            ->setDateFrom($data['date_from'])
        ->setDateTo($data['date_to']);
        return \Report::make('rpt_user_action/source', $data,'rpt_user_action');

    }

    /*private function _reportHistory()
    {
        $dir = public_path('reports/loan');
        //$files1 = scandir($dir);
        $files2 = scandir($dir, 1);
        $st = '<ul>';
        //$i=0;

        for ($i = 0; $i < count($files2) - 2; $i++) {
            if ($i >= 5) return $st .= '</ul>';
            $st .= '<li><a href="' . \URL::to('reports/loan' . $files2[$i]) . '">' . $files2[$i] . '</a></lil>';

        }
        return $st .= '</ul>';
    }*/
} 