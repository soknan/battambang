<?php

namespace Battambang\Loan;

use Eloquent;
use Carbon;
class Perform extends Eloquent {

	protected $table = 'ln_perform';
    protected $primaryKey='id';
	public $timestamps = true;
	protected $softDelete = false;

    /*public function disburseClient() {
        return $this->belongsTo('DisburseClient','ln_disburse_client_id');
    }*/

    /*public function setActivatedAtAttribute($value)
    {
        $this->attributes['activated_at'] = Carbon::createFromFormat('d-m-Y', $value)
            ->toDateString();
    }

    public function getActivatedAtAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d', $value)
            ->format('d-m-Y');
    }*/

}