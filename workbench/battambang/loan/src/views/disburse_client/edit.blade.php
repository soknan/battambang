@extends(Config::get('battambang/cpanel::views.layout'))

@section('content')

<div class="alert alert-info">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <dl class="dl-horizontal">
        <dt>Center Info :</dt>
        <dd>{{ $dis->center_name }}</dd>
        <dt>Product Info :</dt>
        <dd>{{ $dis->product_name }}</dd>
    </dl>
</div>

{{Former::open( route('loan.disburse_client.update',$row->id))->method('PUT')}}

<?php
if($pro->interest_type_id==129){
echo FormPanel2::make(
        'Additional Info',
        Former::number('pre_amount', 'Pre_Amount',$row->pre_amount)->required()
        . ''.
        Former::number('discount', 'Discount',$row->discount)->required()
                ->append('%')    . ''
        ,
        Former::number('pay_down', 'PayDown',$row->pay_down)->required()
        . ''
);
      }
echo FormPanel2::make(
    'General',
    Former::text('ln_disburse_id', 'Disburse ID', $row->ln_disburse_id)
        ->required()
        ->readonly() . ''
    .Former::number('amount', 'Amount', $row->amount)
        ->min($pro->min_amount)
        ->max($pro->max_amount)
        ->step('0.01')
        ->required()
        ->append($pro->append_amount).''
    .Former::text_hidden('currency_id',$dis->currency_id)
    ,
    Former::text('voucher_id', 'Voucher ID', $row->voucher_id)
        ->maxlength(6)
        ->required() . ''
    .Former::text_hidden('hidden_voucher_id',$row->voucher_id)
);
//->options(LookupValueList::getBy('history'),$row->ln_lv_history)
echo FormPanel2::make(
    'Loan Additional',
    Former::text('cycle', 'Cycle', $row->cycle)
        ->readonly('readonly')
        ->required() . ''
    . Former::select('ln_lv_history', 'History',LookupValueList::getHistory($row->cycle),array($row->ln_lv_history))
        ->required()
    . Former::select('ln_lv_purpose', 'Purpose',LookupValueList::getBy('purpose'),$row->ln_lv_purpose)
        ->required()

    . Former::textarea('purpose_des', 'Purpose Des', $row->purpose_des)
        ->required()
    . Former::select('ln_lv_activity', 'Activity',LookupValueList::getBy('activity'),$row->ln_lv_activity)
        ->required()

    ,
    Former::select('ln_lv_collateral_type', 'Collateral Type',LookupValueList::getBy('collateral type'),$row->ln_lv_collateral_type)
        ->required()
         . ''
    . Former::textarea('collateral_des', 'Des', $row->collateral_des)
        ->required()
    . Former::select('ln_lv_security', 'Security',LookupValueList::getBy('security'),$row->ln_lv_security)
        ->required()

);

echo FormPanel2::make(
    'Client Additional',
    Former::select('ln_client_id', 'Client ID')
        ->options($client, $row->ln_client_id)
        ->readonly()
        ->required()
        . ''
    . Former::select('ln_lv_id_type', 'ID Type',LookupValueList::getBy('id type'), $row->ln_lv_id_type)
        ->required()

    . Former::text('id_num', 'ID Number', $row->id_num)
    . Former::text('expire_date', 'Expire Day',$row->expire_date=='0000-00-00'?$row->expire_date='':date('d-m-Y',strtotime($row->expire_date)) )
        ->append('dd/mm/yyyy')

    . Former::select('ln_lv_marital_status', 'Marital Status',LookupValueList::getBy('marital status'),$row->ln_lv_marital_status)

        ->required()
    . Former::number('family_member', 'Family Member', $row->family_member)
        ->required()
    . Former::number('num_dependent', 'Number Dependent', $row->num_dependent)
        ->required()
    . Former::select('ln_lv_education', 'Education',LookupValueList::getBy('education'),$row->ln_lv_education)

        ->required()
    .Former::select('ln_lv_business', 'Business',LookupValueList::getBy('business'),$row->ln_lv_business)

        ->required()
    ,
     Former::select('ln_lv_poverty_status', 'Poverty Status',LookupValueList::getBy('poverty status'),$row->ln_lv_poverty_status)
         ->required()
        . ''
    . Former::number('income_amount', 'Income Amount', $row->income_amount)
        ->step('0.01')
        ->append('.00 USD')
         ->required()
    . Former::select('ln_lv_handicap', 'Handicap',LookupValueList::getBy('handicap'),$row->ln_lv_handicap)

         ->required()
    . Former::textarea('address', 'Address', $row->address)
         ->required()
    . Former::select('ln_lv_contact_type', 'Contact Type',LookupValueList::getBy('contact type'),$row->ln_lv_contact_type)

         ->required()
    . Former::text('contact_num', 'Contact Number', $row->contact_num)
         ->required()
    . Former::textarea('email', 'Email', $row->email)
);

?>

<div class="text-center">
    {{ Former::lg_primary_submit('Submit') . '&nbsp;' . Former::lg_inverse_reset('Reset') }}
</div>
{{Former::close()}}

@stop

@section('js')
 <?php  if($pro->interest_type_id==129){ ?>
    <script>
        $(document).ready(function () {
            $('[name="pre_amount"]').ready(function() {changeAmount()}).change(function() {changeAmount()});
            $('[name="discount"]').ready(function() {changeAmount()}).change(function() {changeAmount()});
            $('[name="pay_down"]').ready(function() {changeAmount()}).change(function() {changeAmount()});
            function changeAmount() {
                //alert('aa');
                var pre_amount = $('[name="pre_amount"]');
                var amount = $('[name="amount"]');
                var discount = $('[name="discount"]');
                var paydown = $('[name="pay_down"]');
                var tmpDis;
                tmpDis = discount.val()/100;
                //if(tmpDis<=0){tmpDis.val(1);}

                amount.val(pre_amount.val()-(pre_amount.val()*tmpDis)-paydown.val());
            }
        });
    </script>
 <?php }?>
<?php echo DatePicker::make('expire_date'); ?>
@stop

