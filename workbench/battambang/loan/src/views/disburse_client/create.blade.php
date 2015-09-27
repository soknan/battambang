@extends(Config::get('battambang/cpanel::views.layout'))

@section('content')
<div class="alert alert-info">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <dl class="dl-horizontal">
        <dt>Center Info :</dt>
        <dd>{{ $dis->center_name.' ,'.$dis->staff_en_name.' , '.$dis->geography_name }}</dd>
        <dt>Product Info :</dt>
        <dd>{{ $dis->product_name }}</dd>

    </dl>
</div>

{{Former::horizontal_open(route('loan.disburse_client.store'))->method('POST')}}

<?php
if(Session::has('errors')){
    $ln_lv_education ='';
    $ln_lv_id_type = '';
    $ln_lv_marital_status ='';
   $ln_lv_business ='';
    $ln_lv_poverty_status='';
    $ln_lv_handicap='';
    $ln_lv_contact_type ='';
}
if($pro->interest_type_id==129){
echo FormPanel2::make(
        'Additional Info',
        Former::number('pre_amount', 'Pre_Amount',$pro->default_amount)->required()->min(0)
                . ''.
        Former::number('discount', 'Discount',0)->required()->min(0)
                ->append('%')    . ''
        ,
Former::number('pay_down', 'PayDown',0)->required()->min(0)
        . ''
);
        }

echo FormPanel2::make(
    'General',
    Former::text('ln_disburse_id', 'Disburse ID')
        ->required()
        ->readonly() . ''
    . Former::number('amount', 'Amount',$pro->default_amount)
        ->min($pro->min_amount)
        ->max($pro->max_amount)
        ->step('0.01')
        ->required()
        ->append($pro->append_amount)
//        ->append(number_format($pro->min_amount,'2','.',',') . ' - ' . number_format($pro->max_amount,2,'.',',') . ' ' . $dis->currency_code)
    .Former::text_hidden('currency_id',$dis->currency_id)
    ,
    Former::text('voucher_id', 'Voucher ID')
        ->maxlength(6)
        ->required() . ''
);

echo FormPanel2::make(
    'Loan Additional',
    Former::text('cycle', 'Cycle',$cycle)
        ->readonly('readonly')
        ->required() . ''
    . Former::select('ln_lv_history', 'History', LookupValueList::getHistory($cycle))
//    . Former::select('ln_lv_history', 'History', LookupValueList::getBy('history'))
        ->required()
    . Former::select('ln_lv_purpose', 'Purpose',LookupValueList::getBy('purpose'))
        ->required()

    . Former::textarea('purpose_des', 'Purpose Des')
        ->required()
    . Former::select('ln_lv_activity', 'Activity',LookupValueList::getBy('activity'))
        ->required()

    ,
    Former::select('ln_lv_collateral_type', 'Collateral Type',LookupValueList::getBy('collateral type'))
        ->required()
         . ''
    . Former::textarea('collateral_des', 'Des')
        ->required()
    . Former::select('ln_lv_security', 'Security',LookupValueList::getBy('security'))
        ->required()

);

echo FormPanel2::make(
    'Client Additional',
    Former::select('ln_client_id', 'Client ID')
        ->options($client)
        ->required()
        ->readonly()
    . Former::select('ln_lv_id_type', 'ID Type',LookupValueList::getBy('id type'),$ln_lv_id_type)
        ->required()

    . Former::text('id_num', 'ID Number',$id_num)
    . Former::text('expire_date', 'Expire Day',$expire_date)
        ->append('dd/mm/yyyy')
    . Former::select('ln_lv_marital_status', 'Marital Status',LookupValueList::getBy('marital status'),$ln_lv_marital_status)

        ->required()
    . Former::number('family_member', 'Family Member',$family_member)
        ->required()
    . Former::number('num_dependent', 'Number Dependent',$num_dependent)
        ->required()
    . Former::select('ln_lv_education', 'Education',LookupValueList::getBy('education'),$ln_lv_education)

        ->required()
    . Former::select('ln_lv_business', 'Business',LookupValueList::getBy('business'),$ln_lv_business)

        ->required()
    ,
    Former::select('ln_lv_poverty_status', 'Poverty Status',LookupValueList::getBy('poverty status'),$ln_lv_poverty_status)
        ->required()
         . ''
    . Former::number('income_amount', 'Income Amount',$income_amount)
        ->step('0.01')
        ->required()
        ->append('.00 USD')
    . Former::select('ln_lv_handicap', 'Handicap',LookupValueList::getBy('handicap'),$ln_lv_handicap)

        ->required()
    . Former::textarea('address', 'Address',$address)
        ->required()
    . Former::select('ln_lv_contact_type', 'Contact Type',LookupValueList::getBy('contact type'),$ln_lv_contact_type)

        ->required()
    . Former::text('contact_num', 'Contact Number',$contact_num)
        ->required()
    . Former::textarea('email', 'Email',$email)
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
            $('[name="pre_amount"]').change(function() {changeAmount()});
            $('[name="discount"]').change(function() {changeAmount()});
            $('[name="pay_down"]').change(function() {changeAmount()});
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
