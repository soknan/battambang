<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 1/29/14
 * Time: 2:10 PM
 */

namespace Battambang\Loan\Libraries;

use Battambang\Loan\ClientLoan;
use Battambang\Loan\Currency;
use Battambang\Loan\Exchange;
use Battambang\Loan\Fee;
use Battambang\Loan\Fund;
use Battambang\Loan\Lookup;
use Battambang\Loan\Penalty;
use Battambang\Loan\PenaltyClosing;
use Battambang\Loan\Perform;
use Battambang\Loan\Product;
use Battambang\Loan\Staff;
use DB;

class LookupValueList
{

    public function getBy($code, $more = '')
    {
        $arr = array();
        $code = str_replace(' ', '', strtolower($code));
        $data = DB::select(
            "SELECT
                            ln_lookup_value.id,
                            ln_lookup_value.`code` as 'code',
                            ln_lookup_value.`name`,
                            ln_lookup.`name`  as 'lookup_name',
                            ln_lookup.type
                            FROM
                            ln_lookup
                            INNER JOIN ln_lookup_value ON ln_lookup_value.ln_lookup_id = ln_lookup.id
                            WHERE  lower(replace(ln_lookup.`name`,' ','')) = '" . $code . "' $more "
        );
        if(trim($code)!='accounttype') $arr[''] = '--Select One--';
        foreach ($data as $row) {
            $arr[$row->id] = $row->name;
        }

        return $arr;
    }

    public function getHistory($cycle)
    {
        $data = $this->getBy('history');
        if ($cycle > 1) {
            $data = array_where(
                $data,
                function ($key, $vlue) {
                    return ($key != 98);
                }
            );
        } else {
            $data = array_where(
                $data,
                function ($key, $vlue) {
                    return ($key == 98);
                }
            );
        }
//        var_dump($data);
//        exit;
        return $data;
    }

    public function getLookup()
    {
        $data = Lookup::orderBy('name')->lists('name', 'id');
        return $data;
    }

    public function getLocationAjax()
    {
        $data = $this->_getLocationSpace();

        return $data;
    }

    public function getLocation($level = 4, $whereLikeIn = array(), $lang = 'kh')
    {
        $data = DB::table('cp_location')
            ->orderBy('id', 'asc')
            ->get();

        $dataList = array();
        foreach ($data as $list) {
            $dataList[$list->id] = array(
                'kh_name' => $list->kh_name,
                'en_name' => $list->en_name,
            );
        }

        $locationWhere = array_where(
            $dataList,
            function ($key, $value) use ($level) {
                return (strlen($key) == (2 * $level));
            }
        );

        $locationListKh = array();
        $locationListEn = array();
        foreach ($locationWhere as $key => $val) {
            // Get parent info
            $parentKhName = $val['kh_name'];
            $parentEnName = $val['en_name'];
            $separator = ' | ';
            $parentId = substr($key, 0, -2);
            for ($i = 1; $i < $level; $i++) {
                $getKhName = array_get($dataList, $parentId . '.kh_name');
                $getEnName = array_get($dataList, $parentId . '.en_name');
                $parentKhName = $getKhName . $separator . $parentKhName;
                $parentEnName = $getEnName . $separator . $parentEnName;
                $parentId = substr($parentId, 0, -2);
            }

            $locationListKh[$key] = $key . ' | ' . $parentKhName;
            $locationListEn[$key] = $key . ' | ' . $parentEnName;

            array_set($locationWhere, $key . '.kh_name', $parentKhName);
            array_set($locationWhere, $key . '.en_name', $parentEnName);
        }

        // Check $where
        if (count($whereLikeIn) > 0) {
            $locationListKh = array_where(
                $locationListKh,
                function ($key, $value) use ($whereLikeIn) {
                    foreach ($whereLikeIn as $whereLikeKey => $whereLikeValue) {
                        if ($whereLikeKey == 0) {
                            $compare = (starts_with($key, $whereLikeValue));
                        } else {
                            $compare = ($compare or (starts_with($key, $whereLikeValue)));
                        }
                    }
                    return ($compare);
                }
            );
            $locationListEn = array_where(
                $locationListEn,
                function ($key, $value) use ($whereLikeIn) {
                    foreach ($whereLikeIn as $whereLikeKey => $whereLikeValue) {
                        if ($whereLikeKey == 0) {
                            $compare = (starts_with($key, $whereLikeValue));
                        } else {
                            $compare = ($compare or (starts_with($key, $whereLikeValue)));
                        }
                    }
                    return ($compare);
                }
            );
        }

        return (($lang == 'kh') ? $locationListKh : $locationListEn);
    }

    public function getLocationCategory()
    {
        $data = array(0 => 'All', 1 => 'Province', 2 => 'District', 3 => 'Commune', 4 => 'Village');
        return $data;
    }

    public function getRepFreq($more = '')
    {
        $arr = array();
        $data = DB::select('select * from ln_lookup_value where ln_lookup_id = 2 ' . $more);
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getCategory($more = '')
    {
        $arr = array();
        $data = DB::select('select * from ln_category where 1=1 ' . $more);
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getCurrency()
    {
        $arr = array();
        $data = Currency::all();
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getFee()
    {
        $arr = array();
        $data = Fee::all();
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getPenalty()
    {
        $arr = array();
        $data = Penalty::all();
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getPenaltyClosing()
    {
        $arr = array();
        $data = PenaltyClosing::all();
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getFund()
    {
        $arr = array();
        $data = Fund::all();
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getClientLoan()
    {
        $arr = array();
        $data = ClientLoan::where('cp_office_id', '=', \UserSession::read()->sub_branch)->get();
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->id . ' | ' . $row->kh_last_name . ' ' . $row->kh_first_name . ' | ' . $row->en_last_name . ' ' . $row->en_first_name;
            }
        }
        return $arr;
    }

    public function getStaff($more = '')
    {
        $arr = array();
        $data = DB::select(
            'select * from ln_staff  where 1=1 and cp_office_id like "' . \UserSession::read(
            )->sub_branch . '%" ' . $more
        );
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->en_last_name . ' ' . $row->en_first_name . ' | ' . $row->kh_last_name . ' ' . $row->kh_first_name;
            }
        }
        return $arr;
    }

    public function getCenter($more = '')
    {
        $arr = array();
        $data = DB::select(
            'select * from ln_center where cp_office_id like "' . \UserSession::read()->sub_branch . '" ' . $more
        );
        if ($data) {
            foreach ($data as $row) {
                $staff = Staff::find($row->ln_staff_id);
                $arr[$row->id] = $row->name . ' | ' . $staff->kh_last_name . ' ' . $staff->kh_first_name;
            }
        }
        return $arr;
    }

    public function getProduct($more = '')
    {
        $arr = array();
        $data = DB::select("select * from ln_product where 1=1 and end_date > CURRENT_DATE() " . $more);
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getProductStatus($more = '')
    {
        $arr = array();
        $data = DB::select("select * from ln_product_status where 1=1 " . $more);
        if ($data) {
            foreach ($data as $row) {
                $arr[$row->id] = $row->name;
            }
        }
        return $arr;
    }

    public function getExchange()
    {
        $data = Exchange::all();
        $arr = array();
        foreach ($data as $row) {
            $arr[$row->id] = $row->exchange_at . ' | ' . $row->khr_usd . ' KHR = ' . $row->usd . ' USD | ' . $row->khr_thb . ' KHR = ' . $row->thb . ' THB';
        }
        return $arr;
    }

    public function jsonData($arr)
    {
        $data = DB::select(
            "select * from ln_lookup
                        inner join ln_lookup_value on ln_lookup.id = ln_lookup_value.ln_lookup_id
                        where ln_lookup_value.id IN('" . implode("','", $arr) . "')"
        );
        foreach ($data as $row) {
            $tmp[$row->id] = $row->name;
        }
        return $tmp;
    }

    public function getKhmerDay($date)
    {
        $tmp = '';
        if (explode('-', date('D-d-M-Y', strtotime($date)))) {
            list($khDay) = explode('-', date('D-d-M-Y', strtotime($date)));
        } else {
            $khDay = $date;
        }
        switch ($khDay) {
            case 'Mon':
                $tmp = 'ចន្ទ';
                break;
            case 'Tue':
                $tmp = 'អង្គារ';
                break;
            case 'Wed':
                $tmp = 'ពុធ';
                break;
            case 'Thu':
                $tmp = 'ព្រហស្បតិ៍';
                break;
            case 'Fri':
                $tmp = 'សុក្រ';
                break;
            case 'Sat':
                $tmp = 'សៅរ៍';
                break;
            case 'Sun':
                $tmp = 'អាទិត្រ';
                break;
            default:
                $tmp = 'None';
                break;
        }
        return $tmp;
    }

    public function getRepayStatus()
    {
        return array(
            'normal' => 'Normal',
            'closing' => 'Closing',
            'penalty' => 'Penalty',
            'fee' => 'Fee',
            /*'surplus'=>'Surplus',*/
        );
    }

    public function getOperator(){
        return array(
            "=="=>"Equal",
            "!="=>"Not Equal",
            "<"=>"Less Than",
            "<="=>"Less Than or Equal",
            ">"=>"Bigger Than",
            ">="=>"Bigger Than or Equal",
            "between"=>"between"
        );
    }

    public function getLoanAccount()
    {
            $data = \DB::select("select dc.id as id,CONCAT(c.kh_last_name,' ',c.kh_first_name) as client_kh_name,d.disburse_date as disburse_date
from ln_disburse_client dc
LEFT JOIN ln_disburse d
on dc.ln_disburse_id = d.id
LEFT JOIN ln_client c
on c.id = dc.ln_client_id
WHERE SUBSTR(dc.id,1,4) = '".\UserSession::read()->sub_branch."'
and dc.id not in (select p.ln_disburse_client_id from ln_perform p where p.repayment_type = 'closing'
and (p.balance_principal <=0 or p.balance_interest <=0))
GROUP BY dc.id
ORDER BY d.disburse_date desc");

        $arr = array();
        $status = '';
        $arr[''] = '--Select One--';
        foreach ($data as $row) {
            $arr[$row->id] = $row->id . ' | ' . $row->client_kh_name . ' | ' . date('d-m-Y', strtotime($row->disburse_date));
        }

        return $arr;
    }

    public function getLoanAccountAll()
    {
        $data = \DB::select("select dc.id as id,CONCAT(c.kh_last_name,' ',c.kh_first_name) as client_kh_name,d.disburse_date as disburse_date
from ln_disburse_client dc
LEFT JOIN ln_disburse d
on dc.ln_disburse_id = d.id
LEFT JOIN ln_client c
on c.id = dc.ln_client_id
WHERE SUBSTR(dc.id,1,4) = '".\UserSession::read()->sub_branch."'
GROUP BY dc.id
ORDER BY d.disburse_date desc");

        $arr = array();
        $status = '';
        foreach ($data as $row) {
            $arr[$row->id] = $row->id . ' | ' . $row->client_kh_name . ' | ' . date('d-m-Y', strtotime($row->disburse_date));
        }

        return $arr;
    }

}