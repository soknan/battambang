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

class RptProductActivityController extends BaseController
{
    public function index()
    {
        //$data['reportHistory'] = $this->_reportHistory();
        return $this->renderLayout(
            \View::make('battambang/loan::rpt_product_activity.index')
        );
    }

    public function report()
    {
        $data = array();

        $com = Company::all()->first();
        $data['company_name'] = $com->en_name;

        $data['as_date'] = \Carbon::createFromFormat('d-m-Y',Input::get('as_date'))->toDateString();
        $data['first_date'] = \Carbon::createFromFormat('d-m-Y',Input::get('as_date'))->firstOfMonth();
        //echo (date('Y-m-d',strtotime($data['first_date']))); exit;
        $data['cp_office']= \Input::get('cp_office_id');
        $data['ln_staff'] = \Input::get('ln_staff_id');
        $data['cp_currency'] = \Input::get('cp_currency_id');
        $data['ln_fund'] = \Input::get('ln_fund_id');
        $data['ln_product'] = \Input::get('ln_product_id');
        $data['repay_frequency'] = \Input::get('ln_lv_repay_frequency');

        $data['location_cat'] = Input::get('location_cat');
        $data['cp_location'] = \Input::get('cp_location_id');
        $data['exchange_rate_id'] = \Input::get('exchange_rate');

        /*if(strlen($data['cp_location'])!=8 and $data['cp_location']!='all'){
            return \Redirect::back()->withInput()->with('error','Please choose Village on Location !');
        }*/
        $ex = Exchange::where('id', '=', $data['exchange_rate_id'])->first();
        $data['exchange_rate'] = '1.00 $ = ' . $ex->khr_usd . ' ៛ , 1.00 B = ' . $ex->khr_thb . '៛';

        $condition = ' 1=1 ';
        $date = " AND p.activated_at <= STR_TO_DATE('".$data['as_date']." 00:00:00" . "','%Y-%m-%d %H:%i:%s') ";
        //$condition.=" AND repayment_type != 'closing' or current_product_status!=5 ";
        /*if($data['classify']!='all'){
            $condition.=" AND ln_perform.current_product_status = '".$data['classify']."'";
        }*/
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
            //$condition .= " AND ln_disburse.cp_currency_id = '" . $data['cp_currency'] . " '";
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
        $sql = DB::select("
        SELECT *,
        ln_disburse_client.id as ln_disburse_client_id,
concat(`ln_client`.`kh_last_name`,' ',`ln_client`.`kh_first_name`) AS `client_name`,
account_type.`code` as account_type from ln_perform p
INNER JOIN ln_disburse_client on ln_disburse_client.id = p.ln_disburse_client_id
INNER JOIN ln_disburse ON ln_disburse_client.ln_disburse_id = ln_disburse.id
INNER JOIN ln_client ON ln_client.id = ln_disburse_client.ln_client_id
INNER JOIN ln_lookup_value account_type on account_type.id = ln_disburse.ln_lv_account_type
INNER JOIN ln_product ON ln_product.id = ln_disburse.ln_product_id
INNER JOIN ln_center ON ln_center.id = ln_disburse.ln_center_id
where $condition $date
group by ln_disburse_client.id
order by ln_disburse.disburse_date DESC
        ");
// User action
        \Event::fire('user_action.report', array('rpt_productivity'));
        if (count($sql) <= 0) {
            return \Redirect::back()->withInput(Input::except('cp_office_id'))->with('error', 'No Data Found !.');
        }
        $perform = array();
        foreach ($sql as $row) {
            $loanPerform = new LoanPerformance();
            $perform[]= $loanPerform->get($row->ln_disburse_client_id,$data['as_date']);
        }
//var_dump($perform); exit;
        $tmp = array();
        $ccy = "";
        $con_bal = array();
        $con_int = array();
        $con_pri = array();
        $con_pen = array();
        $con_dis = array();
        $con_fee = array();
        $con_arr = array();
        $con_arr_int = array();
        $con_par= array();
        $con_acc = array();
        $con_par_n = array();
        $all_cycle = array();
        $old_cycle = array();

        foreach($perform as $row){
            if($row->_disburse->disburse_date <= $data["as_date"]) {
                if (!isset($tmp[$row->_disburse->ln_staff_id])) {
                    $tmp[$row->_disburse->ln_staff_id] = array();

                    $con_dis[$row->_disburse->ln_staff_id] = array();
                    $con_dis[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_dis[$row->_disburse->ln_staff_id]->total = 0;

                    $con_pri[$row->_disburse->ln_staff_id] = array();
                    $con_pri[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_pri[$row->_disburse->ln_staff_id]->total = 0;

                    $con_int[$row->_disburse->ln_staff_id] = array();
                    $con_int[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_int[$row->_disburse->ln_staff_id]->total = 0;

                    $con_pen[$row->_disburse->ln_staff_id] = array();
                    $con_pen[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_pen[$row->_disburse->ln_staff_id]->total = 0;

                    $con_fee[$row->_disburse->ln_staff_id] = array();
                    $con_fee[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_fee[$row->_disburse->ln_staff_id]->total = 0;

                    $con_bal[$row->_disburse->ln_staff_id] = array();
                    $con_bal[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_bal[$row->_disburse->ln_staff_id]->total = 0;

                    $con_arr[$row->_disburse->ln_staff_id] = array();
                    $con_arr[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_arr[$row->_disburse->ln_staff_id]->total = 0;

                    $con_arr_int[$row->_disburse->ln_staff_id] = array();
                    $con_arr_int[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_arr_int[$row->_disburse->ln_staff_id]->total = 0;

                    $con_par[$row->_disburse->ln_staff_id] = array();
                    $con_par[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_par[$row->_disburse->ln_staff_id]->total = 0;

                    $con_par_n[$row->_disburse->ln_staff_id] = array();
                    $con_par_n[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_par_n[$row->_disburse->ln_staff_id]->total = 0;

                    $con_acc[$row->_disburse->ln_staff_id] = array();
                    $con_acc[$row->_disburse->ln_staff_id] = new \stdClass();
                    $con_acc[$row->_disburse->ln_staff_id]->total = 0;

                }

                /*if (!isset($tmp[$row->_disburse->ln_staff_id][$row->_disburse->ln_client_id])) {
                    $tmp[$row->_disburse->ln_staff_id][$row->_disburse->ln_client_id] = array();
                    $all_cycle[$row->_disburse->ln_staff_id][$row->_disburse->ln_client_id] = array();
                    $all_cycle[$row->_disburse->ln_staff_id][$row->_disburse->ln_client_id] = new \stdClass();
                    $all_cycle[$row->_disburse->ln_staff_id][$row->_disburse->ln_client_id]->total = 0;

                }*/
                    //$all_cycle[$row->_disburse->ln_staff_id][$row->_disburse->ln_client_id]->total;

                $tmp[$row->_disburse->ln_staff_id][$row->_disburse->ln_client_id] = $row;

                switch ($data['cp_currency']){
                    case 'USD':
                        $ccy = "toUSD";
                        break;
                    case 'KHR':
                        $ccy = "toKHR";
                        break;
                    case 'THB':
                        $ccy = "toTHB";
                        break;
                }


                $con_dis[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$this->_sumDis($row->_disburse_client_id,$data['first_date'],$data['as_date']),$data['exchange_rate_id']);
                $con_pri[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$this->_sumColPri($row->_disburse_client_id,$data['first_date'],$data['as_date']), $data['exchange_rate_id']);
                $con_int[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$this->_sumColInt($row->_disburse_client_id,$data['first_date'],$data['as_date']), $data['exchange_rate_id']);
                $con_pen[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$this->_sumColPen($row->_disburse_client_id,$data['first_date'],$data['as_date']), $data['exchange_rate_id']);
                $con_fee[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$this->_sumFee($row->_disburse_client_id,$data['first_date'],$data['as_date']), $data['exchange_rate_id']);

                 //if($row->_perform_type!='writeoff'){
                    $con_bal[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$row->_balance_principal, $data['exchange_rate_id']);
                     if(!in_array($row->_current_product_status,array(1))){
                         $con_par[$row->_disburse->ln_staff_id]->total += \Currency::$ccy($row->_disburse->cp_currency_id,$row->_balance_principal, $data['exchange_rate_id']);
                     }
                 //}

                $total = $row->_arrears['cur']['principal'] + $row->_arrears['cur']['interest'];
                if($row->_arrears['cur']['num_day']>0 and $total>0){
                    $con_arr[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$row->_arrears['cur']['principal'], $data['exchange_rate_id']);
                    $con_arr_int[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$row->_arrears['cur']['interest'], $data['exchange_rate_id']);
                    $con_par_n[$row->_disburse->ln_staff_id]->total+= \Currency::$ccy($row->_disburse->cp_currency_id,$row->_arrears['cur']['principal'], $data['exchange_rate_id']);
                }

                $con_acc[$row->_disburse->ln_staff_id]->total+= $row->_disburse->num_account;

            }
        }

        //var_dump($perform);
        //exit
        $data['con_bal'] = $con_bal;
        $data['con_int'] = $con_int;
        $data['con_pri'] = $con_pri;
        $data['con_pen'] = $con_pen;
        $data['con_dis'] = $con_dis;
        $data['con_fee'] = $con_fee;
        $data['con_arr'] = $con_arr;
        $data['con_arr_int'] = $con_arr_int;
        $data['con_par'] = $con_par;
        $data['con_par_n'] = $con_par_n;
        $data['con_acc'] = $con_acc;
        //$data['all_cycle'] = $all_cycle;
        //$data['old_cycle'] = $old_cycle;
        $data['result']= $tmp;
        //var_dump($data['c_dis']); exit;
        if (count($data['result']) <= 0) {
            return \Redirect::back()->withInput(Input::except('cp_office_id'))->with('error', 'No Data Found !.');
        }

        \Report::setReportName('Productivity')
            ->setDateFrom($data['as_date']);
        return \Report::make('rpt_product_activity/source', $data,'productivity');

    }

    public function _countAllClient($id,$to){
        $data = 0;
        $data = Disburse::join('ln_disburse_client','ln_disburse_client.ln_disburse_id','=','ln_disburse.id')
            ->whereRaw(" ln_disburse.ln_staff_id = '".$id."'
            and ln_disburse_client.id not in(select p.ln_disburse_client_id from ln_perform p WHERE p.repayment_type ='closing')
             and ln_disburse.disburse_date <=STR_TO_DATE('".$to." " . " 23:59:59" . "','%Y-%m-%d %H:%i:%s') ")
            ->selectRaw("count(ln_disburse_client.ln_client_id) as dis")
            ->first();
        return $data->dis;
    }

    public function _countNewClient($id,$from,$to){
        $data = 0;
        $data = Disburse::join('ln_disburse_client','ln_disburse_client.ln_disburse_id','=','ln_disburse.id')
            ->whereRaw(" ln_disburse.ln_staff_id = '".$id."'
             and ln_disburse_client.cycle = 1 and ln_disburse.disburse_date BETWEEN
                        STR_TO_DATE('".$from." " . " 00:00:00" . "','%Y-%m-%d %H:%i:%s')
                        AND STR_TO_DATE('".$to." " . " 23:59:59" . "','%Y-%m-%d %H:%i:%s') ")
            ->selectRaw("count(ln_disburse_client.ln_client_id) as dis")
            ->first();
        return $data->dis;
    }

    public function _countOldClient($id,$from,$to){
        $data = 0;
        $data = Disburse::join('ln_disburse_client','ln_disburse_client.ln_disburse_id','=','ln_disburse.id')
            ->whereRaw(" ln_disburse.ln_staff_id = '".$id."'
             and ln_disburse_client.cycle > 1 and ln_disburse.disburse_date BETWEEN
                        STR_TO_DATE('".$from." " . " 00:00:00" . "','%Y-%m-%d %H:%i:%s')
                        AND STR_TO_DATE('".$to." " . " 23:59:59" . "','%Y-%m-%d %H:%i:%s') ")

            ->selectRaw("count(ln_disburse_client.ln_client_id) as dis")
            ->first();
        return $data->dis;
    }

    private function _sumDis($id,$from,$to){
        $data = 0;
        $data = DisburseClient::whereRaw(" ln_disburse_client.id = '".$id."'
             and disburse_date BETWEEN
                        STR_TO_DATE('".$from." " . " 00:00:00" . "','%Y-%m-%d %H:%i:%s')
                        AND STR_TO_DATE('".$to." " . " 23:59:59" . "','%Y-%m-%d %H:%i:%s') ")
            ->join('ln_disburse','ln_disburse.id','=','ln_disburse_client.ln_disburse_id')
            ->selectRaw("sum(ln_disburse_client.amount) as dis")
            ->first();
        return $data->dis;
    }

    private function _sumFee($id,$from,$to){
        $data = 0;
        $data = Perform::whereRaw(" ln_disburse_client_id = '".$id."'
            and perform_type='repayment' and repayment_type='fee'
            and activated_at BETWEEN STR_TO_DATE('".$from." 00:00:00" . "','%Y-%m-%d %H:%i:%s')
            AND STR_TO_DATE('".$to." 00:00:00" . "','%Y-%m-%d %H:%i:%s') ")
            ->selectRaw("sum(repayment_fee) as col_fee")
            ->orderBy('id', 'DESC')->limit(1)->first();
        return $data->col_fee;
    }

    private function _sumColPri($id,$from,$to){
        $data = 0;
        $data = Perform::whereRaw(" ln_disburse_client_id = '".$id."'
            and perform_type='repayment' and repayment_type!='fee'
            and activated_at BETWEEN STR_TO_DATE('".$from." 00:00:00" . "','%Y-%m-%d %H:%i:%s')
            AND STR_TO_DATE('".$to." 00:00:00" . "','%Y-%m-%d %H:%i:%s') ")
            ->selectRaw("sum(repayment_principal) as col_pri")
            ->first();
        return $data->col_pri;
    }

    private function _sumColInt($id,$from,$to){
        $data = 0;
        $data = Perform::whereRaw(" ln_disburse_client_id = '".$id."'
            and perform_type='repayment' and repayment_type!='fee'
            and activated_at BETWEEN STR_TO_DATE('".$from." 00:00:00" . "','%Y-%m-%d %H:%i:%s')
            AND  STR_TO_DATE('".$to." 00:00:00" . "','%Y-%m-%d %H:%i:%s') ")
            ->selectRaw("sum(repayment_interest) as col_int")
            ->limit(1)->first();
        return $data->col_int;
    }

    private function _sumColPen($id,$from,$to){
        $data = 0;
        $data = Perform::whereRaw(" ln_disburse_client_id = '".$id."'
        and perform_type='repayment' and repayment_type!='fee'
            and activated_at BETWEEN STR_TO_DATE('".$from." 00:00:00" . "','%Y-%m-%d %H:%i:%s')
            AND STR_TO_DATE('".$to." 00:00:00" . "','%Y-%m-%d %H:%i:%s') ")
            ->selectRaw("sum(repayment_penalty) as col_pen")
            ->limit(1)->first();
        return $data->col_pen;
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