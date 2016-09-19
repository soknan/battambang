<?php namespace Battambang\Loan\Services\Validators\Repayment;

use Battambang\Cpanel\Libraries\Rule;
use Battambang\Cpanel\Services\Validators\ValidatorService;

class Validator extends ValidatorService
{
    public function __construct()
    {
        parent::__construct($data = null);

        static::$rules = array(
            'ln_disburse_client_id' => 'required|chk_acc' ,

        );
        static::$messages = array(
            'chk_repay_date'=>'Please do not choose future date!',
            'chk_acc'=>'Invalid Loan Account Number!'
        );
    }

}

\Validator::extend('chk_repay_date', function ($attribute, $value, $parameters){
    if(new \DateTime($value) > new \DateTime()){
        return false;
    }
    return true;
});

\Validator::extend('chk_acc', function ($attribute, $value, $parameters){
    $acc = \DB::table('ln_disburse_client')->where('id',$value)->count();
    if($acc <=0){
        return false;
    }
    return true;
});
