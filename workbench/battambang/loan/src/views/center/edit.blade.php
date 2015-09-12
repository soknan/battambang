@extends(Config::get('battambang/cpanel::views.layout'))

@section('content')

{{Former::open( route('loan.center.update',$row->id))->method('PUT')->enctype('multipart/form-data')}}

<?php
echo FormPanel2::make(
    'General',
    Former::text('name', 'name', $row->name)->required() . ''
    . Former::textarea('des', 'Description', $row->des)
    . Former::select('meeting_weekly', 'Meeting Weekly')
        ->options($work_week, $row->meeting_weekly)
            //->placeholder('--Select One--')
    ,
    Former::select('meeting_monthly', 'Meeting Monthly')
            ->options($work_month, $row->meeting_monthly)
            //->placeholder('--Select One--')
    . ''
    . Former::text('joining_date', 'Joining Date', \Carbon::createFromFormat('Y-m-d',$row->joining_date)->format('d-m-Y'))->required()->append('mm/dd/yyyy')
//    . Former::text('ln_staff_id', 'Staff')
//        ->value($row->ln_staff_id)
//        ->required()
    . Former::select('ln_staff_id', 'Staff')
        ->options(\LookupValueList::getStaff(), $row->ln_staff_id)
        ->required()
        //->placeholder('--Select One--')
        ->class('select2')
);

echo FormPanel2::make(
    'Contact',
    Former::select('cp_location_id', 'Location')->options(LookupValueList::getLocation(), $row->cp_location_id)->required() ->class('select2')->placeholder('--Select One--')
    ,
    Former::select('ln_lv_geography', 'Geography')->options(LookupValueList::getBy('geography'), $row->ln_lv_geography)->required() . ''
    .Former::hidden('cp_office_id', UserSession::read()->sub_branch)
//    Former::select('cp_office_id', 'Branch Office')
//        ->options(\GetLists::getSubBranchList())
//        ->required()
//        ->placeholder('--Select One--') . ''
    /*. Former::textarea('address', 'Address', $row->address)->required()*/ . ''
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

