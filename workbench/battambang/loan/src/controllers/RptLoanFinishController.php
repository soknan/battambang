<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2/12/14
 * Time: 2:38 PM
 */

namespace Battambang\Loan;


use Battambang\Cpanel\BaseController;
use Battambang\Cpanel\Company;
use Battambang\Cpanel\Facades\GetLists;
use Battambang\Cpanel\Facades\UserSession;
use Battambang\Cpanel\Libraries\Report;
use DB;
use Input;
use Battambang\Loan\Libraries\LoanPerformance;

class RptLoanFinishController extends BaseController
{
    public function index()
    {
        //$data['reportHistory'] = $this->_reportHistory();
        return $this->renderLayout(
            \View::make('battambang/loan::rpt_loan_finish.index')
        );
    }

    public function report()
    {
        $data = array();
        $com = Company::all()->first();
        $data['company_name'] = $com->en_name;

        $data['account_type'] = Input::get('account_type');
        $data['date_from'] = \Carbon::createFromFormat('d-m-Y',Input::get('date_from'))->toDateString();
        $data['date_to'] = \Carbon::createFromFormat('d-m-Y',\Input::get('date_to'))->toDateString();
        $data['cp_office']= \Input::get('cp_office_id');
        $data['ln_staff'] = \Input::get('ln_staff_id');
        $data['cp_currency'] = \Input::get('cp_currency_id');
        $data['ln_fund'] = \Input::get('ln_fund_id');
        $data['ln_product'] = \Input::get('ln_product_id');
        $data['repay_frequency'] = \Input::get('ln_lv_repay_frequency');
        $data['classify'] = \Input::get('classify');
        $data['location_cat'] = \Input::get('location_cat');
        $data['cp_location'] = \Input::get('cp_location_id');
        $data['exchange_rate_id'] = \Input::get('exchange_rate');

        if($data['date_from'] > $data['date_to']){
            return \Redirect::back()->withInput()->with('error', 'Date From > Date to');
        }

        /*if(strlen($data['cp_location'])!=8 and $data['cp_location']!='all'){
            return \Redirect::back()->withInput()->with('error','Please choose Village on Location !');
        }*/
        $ex = Exchange::where('id', '=', $data['exchange_rate_id'])->first();
        $data['exchange_rate'] = '1.00 $ = ' . $ex->khr_usd . ' ៛ , 1.00 B = ' . $ex->khr_thb . '៛';

        $condition = ' 1=1 ';
        $condition.= " AND ln_perform.activated_at BETWEEN
                        STR_TO_DATE('".$data['date_from']." " . " 00:00:00" . "','%Y-%m-%d %H:%i:%s')
                        AND STR_TO_DATE('".$data['date_to']." " . " 23:59:59" . "','%Y-%m-%d %H:%i:%s') ";
        $condition.=" And repayment_type = 'closing'";
        if($data['account_type']!='all'){
            $condition.=" AND ln_lv_account_type = '".$data['account_type']."'";
        }
        if($data['classify']!='all'){
            $condition.=" AND current_product_status = '".$data['classify']."'";
            $c = ProductStatus::where('id','=',$data['classify'])->first();
            $data['classify'] = $c->code;
        }
        if ($data['cp_office'] != 'all') {
            $condition .= " AND ln_client.cp_office_id  IN('" . implode("','",$data['cp_office']) . "')";
            $tmp_office='';
            foreach ($data['cp_office'] as $office) {
                $tmp_office .=$office.' '.GetLists::getBranchOfficeBy($office).', ';
            }

            $data['cp_office'] = $tmp_office;
        }
        if ($data['ln_staff'] != 'all') {
            $condition .= " AND  ln_disburse.ln_staff_id = '" . $data['ln_staff'] . "' ";
            $staff = Staff::where('id','=',$data['ln_staff'])->first();
            $data['ln_staff'] = $staff->id.' '.$staff->kh_last_name .' '. $staff->kh_first_name;
        }
        if ($data['cp_currency'] != 'all') {
            $condition .= " AND ln_disburse.cp_currency_id = '" . $data['cp_currency'] . " '";
            $data['cp_currency'] = Currency::where('id', '=', $data['cp_currency'])->first()->code;
        }
        if ($data['ln_fund'] != 'all') {
            $condition .= " AND ln_disburse.ln_fund_id = '" . $data['ln_fund'] . "'";
            $data['ln_fund'] = Fund::where('id', '=', $data['ln_fund'])->first()->name;
        }
        if ($data['ln_product'] != 'all') {
            $condition .= " AND ln_disburse.ln_product_id = '" . $data['ln_product'] . "'";
            $data['ln_product'] = Product::where('id', '=', $data['ln_product'])->first()->name;
        }
        if ($data['repay_frequency'] != 'all') {
            $condition .= " AND  ln_lv_repay_frequency = '" . $data['repay_frequency'] . "'";
            $data['repay_frequency'] = LookupValue::where('id', '=', $data['repay_frequency'])->first()->name;
        }
        if ($data['location_cat'] != 0) {
            $subLocation = substr($data['cp_location'], 0, ($data['location_cat'] * 2));
            $condition .= " AND cp_location_id like '" . $subLocation . "%'";
            $data['cp_location'] = array_get(\LookupValueList::getLocation($data['location_cat'], array($subLocation)), $subLocation);
        }else{
            $data['cp_location'] = 'All';
        }

        $data['result'] = DB::select("
        SELECT *,
        ln_disburse_client.id as ln_disburse_client_id,
concat(`ln_client`.`kh_last_name`,' ',`ln_client`.`kh_first_name`) AS `client_name`,
concat(st.`kh_last_name`,' ',st.`kh_first_name`) AS `kh_staff_name`,
account_type.`code` as account_type
FROM
ln_disburse_client
INNER JOIN ln_disburse ON ln_disburse_client.ln_disburse_id = ln_disburse.id
INNER JOIN ln_client ON ln_client.id = ln_disburse_client.ln_client_id
INNER JOIN ln_lookup_value account_type on account_type.id = ln_disburse.ln_lv_account_type
INNER JOIN ln_product ON ln_product.id = ln_disburse.ln_product_id
INNER JOIN ln_center ON ln_center.id = ln_disburse.ln_center_id
left join ln_staff st on st.id = ln_center.ln_staff_id
INNER join ln_perform on ln_perform.ln_disburse_client_id = ln_disburse_client.id
        where $condition order by ln_perform.activated_at DESC
        ");
// User action
        \Event::fire('user_action.report', array('rpt_loan_close'));
        if (count($data['result']) <= 0) {
            return \Redirect::back()->withInput(Input::except('cp_office_id'))->with('error', 'No Data Found !.');
        }
       //var_dump($data['result']);
       //exit;

        \Report::setReportName('Loan_Closing')
            ->setDateFrom($data['date_from'])
        ->setDateTo($data['date_to']);
        return \Report::make('rpt_loan_finish/source', $data,'loan_closing');

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