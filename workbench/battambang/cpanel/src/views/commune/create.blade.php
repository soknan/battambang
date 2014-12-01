@extends(Config::get('battambang/cpanel::views.layout'))

@section('content')

{{Former::open( route('cpanel.commune.store'))->method('POST')->id('my_form')}}
<?php
echo FormPanel2::make(
    'General',
    Former::text('id', 'ID')->required() . ''
    .Former::text('kh_name', 'Kh Name')->required() . ''
    .Former::text('en_name', 'En Name')->required() . ''
,Former::select('pro', 'Province', \GetLists::getProvinceList())
        ->placeholder('- Select One -')

        ->required() . ''
.Former::select('cp_location_id', 'District')
        ->placeholder('- Select One -')

        ->required() . ''


);

?>

<div class="text-center">
    {{ Former::lg_primary_submit('Submit') . '&nbsp;' . Former::lg_inverse_reset('Reset') }}
</div>

{{Former::close()}}

@stop

@section('js')
<?php
echo FormerAjax::make('my_form', 'pro', 'change', URL::to('cpanel/pro_change'))
    ->getChange(array('cp_location_id' => 'html(data.cp_location_id)'));
?>
@stop