<?php namespace Battambang\Loan\Services\Validators\Center;

use Battambang\Cpanel\Services\Validators\ValidatorService;

class Validator extends ValidatorService
{
    public function __construct(){
        parent::__construct($data = null);

        static::$rules = array(
            //'name'=>'required|unique:ln_center,name,"'.\Request::segment(4).'"',
            'name'=>'required',
            'meeting_weekly'=>'required',
            'meeting_monthly'=>'required',
            'cp_location_id'=>'chk_location',
        );

        static::$messages =  array(
            'chk_location'=>'Please Select Village.!'
        );
    }


}

\Validator::extend('chk_location', function ($attribute, $value, $parameters) {
    $location = \Input::get('cp_location_id');

    if (strlen($location)!=8) {
        return false;
    }
    return true;
});
