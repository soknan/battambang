@extends(Config::get('battambang/cpanel::views.layout'))

@section('content')
{{Former::open( route('loan.rpt_accru_int.report'))->method('POST')}}
<?php echo FormPanel2::make(
    'General',
    Former::select('cp_office_id[]','Branch Office')
        ->options(\GetLists::getSubBranchList(json_decode(\UserSession::read()->branch_arr, true)))
        ->multiple('multiple')
        ->required()
        .''
    .Former::select('ln_staff_id','Staff')
        ->options(array('all'=>'All'))
        ->options(LookupValueList::getStaff())
        ->class('select2')
        .''
    .Former::select('cp_currency_id','Currency')
        ->options(array('all'=>'All'))
        ->options(LookupValueList::getCurrency())
        ->class('select2')
        .''
    .Former::select('ln_fund_id','Fund')
        ->options(array('all'=>'All'))
        ->options(LookupValueList::getFund())
        ->class('select2')
        .''
    .Former::select('ln_product_id','Product')
        ->options(array('all'=>'All'))
        ->options(LookupValueList::getProduct())
        ->class('select2')
    .Former::select('ln_lv_repay_frequency','Repay.Fre')
        ->options(array('all'=>'All'))
        ->options(LookupValueList::getRepFreq())
        ->class('select2')
        .''
    ,
    Former::select('classify','Classify')
        ->options(array('all'=>'All'))
        ->options(LookupValueList::getProductStatus(' and code !="WOL" '))
        ->class('select2')
    .''
    .Former::select('location_cat','Location.Cat')
        ->options(LookupValueList::getLocationCategory())
        ->class('select2').''
    .Former::select('cp_location_id','Location')
        ->options(array('all'=>'All'))
        ->options(LookupValueList::getLocation())
        ->class('select2')
        .''
    .Former::select('exchange_rate','Ex.Rate')
        ->options(LookupValueList::getExchange())
        ->class('select2')
        ->required().''
    .Former::text('as_date','As.Date',date('d-m-Y'))->append('dd-mm-yyyy')->required().''

)
?>

<div class="text-center">
    {{ Former::lg_primary_submit('Submit').'&nbsp;' . Former::lg_inverse_reset('Reset') }}
</div>

<!--FormPanel2::make('
    <a href="#" class="btn btn-primary" rel="tooltip" data-placement="top"
       data-original-title="Refresh" data-toggle="modal"
       onclick="document.location.reload(true);">
        <i class="icon-repeat"></i>
    </a> Report History (Last 5 Views)',
    $reportHistory
)
Former::close()-->

@stop
@section('js')
<?php
echo DatePicker::make('as_date');
?>
@stop

