@extends(Config::get('battambang/cpanel::views.layout'))

@section('content')
{{Former::open( route('loan.center.store'))->method('POST')->enctype('multipart/form-data')}}

<?php
echo FormPanel2::make(
    'General',
    Former::text('name', 'name')->required() . ''
    . Former::textarea('des', 'Description')
    . Former::select('meeting_weekly', 'Meeting Weekly')
        ->options($work_week)
        ->required()
        ///->placeholder('--Select One--')
    ,
    Former::select('meeting_monthly', 'Meeting Monthly')
        ->options($work_month)
        ->required()
        //->placeholder('--Select One--') . ''
    . Former::text('joining_date', 'Joining Date')
        ->required()
        ->append('dd-mm-yyyy')
//    . Former::text('ln_staff_id', 'Staff')
    . Former::select('ln_staff_id', 'Staff')
        ->options(\LookupValueList::getStaff())
        ->required()
        ->placeholder('--Select One--')
        ->class('select2')
);

echo FormPanel2::make(
    'Contact',
    Former::select('cp_location_id', 'Location')
        ->options(LookupValueList::getLocation())
        ->required()
        ->class('select2')
        //->placeholder('--Select One--')
    ,
    Former::select('ln_lv_geography', 'Geography')
                ->options(LookupValueList::getBy('geography'))
                ->required()
                 . ''
    .Former::hidden('cp_office_id', UserSession::read()->sub_branch)
//   Former::select('cp_office_id', 'Branch Office')
//        ->options(\GetLists::getSubBranchList())
//        ->required()
//        ->placeholder('--Select One--') . ''
    /*. Former::textarea('address', 'Address')
        ->required()*/ . ''
);
?>

<div class="text-center">
    {{ Former::lg_primary_submit('Submit') . '&nbsp;' . Former::lg_inverse_reset('Reset') }}
</div>
{{Former::close()}}

@stop

@section('js')
<?php
//echo Select2::make('ln_staff_id', URL::route('loan.staff.ajax'));
echo DatePicker::make('joining_date');
?>
@stop


