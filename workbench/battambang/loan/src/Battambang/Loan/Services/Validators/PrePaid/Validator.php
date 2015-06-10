<?php namespace Battambang\Loan\Services\Validators\PrePaid;

use Battambang\Cpanel\Services\Validators\ValidatorService;
use Battambang\Loan\PrePaid;

class Validator extends ValidatorService
{
    public function __construct()
    {
        parent::__construct($data = null);

        static::$rules = array(
            'ln_disburse_client_id' => 'required' ,
            'date' => 'required|chk_date',

        );
        static::$messages = array(
            'chk_date'=>'Please do not choose perverse date!'
        );
    }
}

\Validator::extend('chk_date', function ($attribute, $value, $parameters){
    $data = PrePaid::where('ln_disburse_client_id','=',\Input::get('ln_disburse_client_id'))
        ->orderBy('id','desc')->limit(1)
        ->first();
    if(date('Y-m-d',strtotime($value)) < date('Y-m-d',strtotime($data->activated_at))){
        return false;
    }
    return true;
});
