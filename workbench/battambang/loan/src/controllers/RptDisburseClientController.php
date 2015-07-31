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

class RptDisburseClientController extends BaseController
{
    public function index()
    {
        return $this->renderLayout(
            \View::make('battambang/loan::rpt_disburse_client.index')
        );
    }

    public function report()
    {
        $data = array();

        $com = Company::all()->first();
        $data['company_name'] = $com->en_name;

        $data['date_from'] = \Carbon::createFromFormat('d-m-Y',Input::get('from'))->toDateString();
        $data['date_to'] = \Carbon::createFromFormat('d-m-Y',\Input::get('to'))->toDateString();
        $data['cp_office']= \Input::get('cp_office_id');
        $data['ln_staff'] = \Input::get('ln_staff_id');
        $data['cp_currency'] = \Input::get('cp_currency_id');
        $data['ln_fund'] = \Input::get('ln_fund_id');
        $data['ln_product'] = \Input::get('ln_product_id');
        $data['repay_frequency'] = \Input::get('ln_lv_repay_frequency');
        $data['cycle'] = \Input::get('cycle');
        $data['location_cat'] = Input::get('location_cat');
        $data['cp_location'] = \Input::get('cp_location_id');
        $data['exchange_rate_id'] = \Input::get('exchange_rate');

        if($data['date_from'] > $data['date_to']){
            return \Redirect::back()->withInput()->with('error', 'Date From > Date to');
        }

        /*if(strlen($data['cp_location'])!=8 and $data['cp_location']!='all'){
            return \Redirect::back()->withInput()->with('error','Please choose Village on Location !');
        }*/
        $ex = Exchange::where('id','=',$data['exchange_rate_id'])->first();
        $data['exchange_rate'] = '1.00 $ = '.$ex->khr_usd .' ៛ , 1.00 B = '.$ex->khr_thb .'៛';

        $condition = ' 1=1 ';
        $condition.= " AND d.disburse_date BETWEEN
                        STR_TO_DATE('".$data['date_from']." " . " 00:00:00" . "','%Y-%m-%d %H:%i:%s')
                        AND STR_TO_DATE('".$data['date_to']." " . " 23:59:59" . "','%Y-%m-%d %H:%i:%s') ";

        if($data['cycle']!=''){
            $condition.=" AND cycle = '".$data['cycle']."'";
        }else{
            $data['cycle'] = 'all';
        }
        if ($data['cp_office'] != 'all') {
            $condition .= " AND c.cp_office_id  IN('" . implode("','",$data['cp_office']) . "')";
            $tmp_office='';
            foreach ($data['cp_office'] as $office) {
                $tmp_office .=$office.' '.GetLists::getBranchOfficeBy($office).', ';
            }

            $data['cp_office'] = $tmp_office;
        }
        if($data['ln_staff'] !='all'){
            $condition.=" AND  d.ln_staff_id = '".$data['ln_staff']."' ";
            $staff = Staff::where('id','=',$data['ln_staff'])->first();
            $data['ln_staff'] = $staff->id.' '.$staff->kh_last_name .' '. $staff->kh_first_name;
        }
        if($data['cp_currency'] !='all'){
            $condition.=" AND d.cp_currency_id = '".$data['cp_currency']." '";
            $data['cp_currency'] = Currency::where('id','=',$data['cp_currency'])->first()->code;
        }
        if($data['ln_fund'] !='all'){
            $condition.=" AND d.ln_fund_id = '".$data['ln_fund']."'";
            $data['ln_fund'] = Fund::where('id','=',$data['ln_fund'])->first()->name;
        }
        if($data['ln_product'] !='all'){
            $condition.=" AND d.ln_product_id = '".$data['ln_product']."'";
            $data['ln_product'] = Product::where('id','=',$data['ln_product'])->first()->name;
        }
        if($data['repay_frequency'] !='all'){
            $condition.=" AND  pr.ln_lv_repay_frequency = '".$data['repay_frequency']."'";
            $data['repay_frequency'] = LookupValue::where('id','=',$data['repay_frequency'])->first()->name;
        }
        if ($data['location_cat'] != 0) {
            $subLocation = substr($data['cp_location'], 0, ($data['location_cat'] * 2));
            $condition .= " AND cn.cp_location_id like '" . $subLocation . "%'";
            $data['cp_location'] = array_get(\LookupValueList::getLocation($data['location_cat'], array($subLocation)), $subLocation);
        }else{
            $data['cp_location'] = 'All';
        }

        /*$data['result'] = DB::select("select ln_disburse_client.*,ln_disburse.*,
        `ln_disburse_client`.`id` AS `id`,
        `ln_disburse_client`.`voucher_id` AS `voucher_id`,
        `ln_disburse_client`.`ln_disburse_id` AS `ln_disburse_id`,
        concat(`ln_client`.`kh_last_name`,' ',`ln_client`.`kh_first_name`) AS `client_name`,
        `ln_disburse`.`cp_currency_id` AS `cp_currency_id`,
        `ln_disburse`.`disburse_date` AS `disburse_date`,
        max(`ln_schedule`.`due_date`) AS `maturity_date`,
        `ln_disburse`.`num_installment` AS `num_installment`,
        `ln_disburse`.`interest_rate` AS `interest_rate`,
        `ln_disburse_client`.`cycle` AS `cycle`,
        `ln_disburse_client`.`amount` AS `amount`,
        `ln_schedule_dt`.`fee` AS `fee`,
        ln_perform.project_interest AS `project_interest`,
        `account_type`.`code` AS `account_type`,
        (`ln_disburse_client`.`amount` + `ln_schedule_dt`.`fee` + ln_perform.project_interest) AS `total_due`
        from (((((`ln_disburse_client` join `ln_disburse` on((`ln_disburse_client`.`ln_disburse_id` = `ln_disburse`.`id`)))
        INNER JOIN `ln_client` on((`ln_disburse_client`.`ln_client_id` = `ln_client`.`id`)))
        join `ln_schedule` on((`ln_disburse_client`.`id` = `ln_schedule`.`ln_disburse_client_id`)))
        join `ln_schedule_dt` on((`ln_schedule`.`id` = `ln_schedule_dt`.`ln_schedule_id`)))
        join `ln_lookup_value` `account_type` on((`account_type`.`id` = `ln_disburse`.`ln_lv_account_type`)))
        INNER JOIN ln_center ON ln_center.id = ln_disburse.ln_center_id
        INNER JOIN ln_product ON ln_product.id = ln_disburse.ln_product_id
        inner join ln_staff on ln_staff.id = ln_disburse.ln_staff_id
        INNEr JOIN ln_perform on ln_perform.ln_disburse_client_id = ln_disburse_client.id
        WHERE $condition
        group by `ln_disburse_client`.`id` ORDER by ln_disburse.disburse_date DESC"
        );*/
        $data['result'] = DB::select("select d.*,dc.*,dc.id as id,
dc.voucher_id as voucher_id,
dc.ln_disburse_id as ln_disburse_id,
concat(c.`kh_last_name`,' ',c.`kh_first_name`) AS `kh_client_name`,
concat(st.`kh_last_name`,' ',st.`kh_first_name`) AS `kh_staff_name`,
d.cp_currency_id AS `cp_currency_id`,
d.`disburse_date` AS `disburse_date`,
p.maturity_date as maturity_date,
d.num_installment as num_installment,
dc.cycle as cycle,
dc.amount as amount,
p.project_interest as project_interest,
account_type.`code` AS `account_type`,
(dc.`amount` + sum(p.repayment_fee) + p.project_interest) AS `total_due`,
sum(p.repayment_fee) as fee from ln_disburse_client dc
LEFT JOIN ln_disburse d on dc.ln_disburse_id = d.id
LEFT JOIN ln_client c on c.id = dc.ln_client_id
left join ln_perform p on p.ln_disburse_client_id = dc.id
left join ln_lookup_value  account_type on account_type.id  = d.ln_lv_account_type
left join ln_product pr on pr.id = d.ln_product_id
left join ln_center cn on cn.id = d.ln_center_id
left join ln_staff st on st.id = cn.ln_staff_id
where $condition
GROUP BY dc.id ORDER BY d.disburse_date desc");
        // User action
        \Event::fire('user_action.report', array('rpt_disburse_client'));
        if(count($data['result']) <=0){
            return \Redirect::back()->withInput(Input::except('cp_office_id'))->with('error','No Data Found !.');
        }
        //var_dump($sql); exit;

        \Report::setReportName('Loan_Disbursement')
            ->setDateFrom($data['date_from'])
            ->setDateTo($data['date_to']);
        return \Report::make('rpt_disburse_client/source', $data,'disburse_clients');

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
