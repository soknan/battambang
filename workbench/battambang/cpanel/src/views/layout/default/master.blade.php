<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="_token" content="{{ csrf_token() }}"/>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="<?php echo url::to('icon.ico')?>">

    <title><?php echo isset($title) ? $title : ''; ?></title>
    <!-- jscal -->
    <!--{{ HTML::style('packages/battambang/cpanel/jscal/css/jscal2.css') }}
    {{ HTML::style('packages/battambang/cpanel/jscal/css/border-radius.css') }}
    {{ HTML::style('packages/battambang/cpanel/jscal/css/win2k/win2k.css') }}
    {{ HTML::script('packages/battambang/cpanel/jscal/js/jscal2.js')}}
    {{ HTML::script('packages/battambang/cpanel/jscal/js/lang/en.js')}}-->
    <!-- end jscal -->

    <!-- Bootstrap core CSS -->
    <?php echo HTML::style('packages/battambang/cpanel/bootstrap3/css/bootstrap.min.css'); ?>
    <?php echo HTML::style('packages/battambang/cpanel/font-awesome/css/font-awesome.min.css'); ?>
    <!-- Custom styles for this template -->
    <?php echo HTML::style('packages/battambang/cpanel/theme/sticky-footer-navbar.css'); ?>
    <?php echo HTML::style('packages/battambang/cpanel/theme/theme.css'); ?>

    <?php echo HTML::style('packages/battambang/cpanel/select2-3.3.1/select2.css'); ?>
    <?php /*echo HTML::style('packages/battambang/cpanel/jcombo/jcombo.css'); */?>
    <?php echo HTML::style('packages/battambang/cpanel/datepicker/css/datepicker.css'); ?>
    <?php echo HTML::style('packages/battambang/cpanel/datatable/css/jquery.dataTables.css') ?>
    <?php echo HTML::style('packages/battambang/cpanel/jquery-ui-1.11.4/jquery-ui.min.css'); ?>
    @yield('css')

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <?php echo HTML::script('packages/battambang/cpanel/jquery/jquery-2.0.2.min.js'); ?>



</head>

<body>

<!-- Wrap all page content here -->
<div id="wrap">

    <!-- Fixed navbar -->
    @include('battambang/cpanel::layout.default.navbar')
    <!-- /Fixed navbar -->

    <!-- Breadcrumb -->
    @include('battambang/cpanel::layout.default.breadcrumb')
    <!-- /Breadcrumb -->

    <!-- Begin page content -->
    <div id="content">
        <div class="container">
            <!--<p>{{Route::currentRouteName()}}</p>-->
            <!-- Page Header -->
            @include('battambang/cpanel::layout.default.page_header')
            <!-- /Page Header -->

            <!-- Message -->
            @include('battambang/cpanel::layout.default.msg')
            <!-- /Message -->

            <!-- Action Add, Edit... -->
            @include('battambang/cpanel::layout.default.action_btn')
            <!-- /Action Add, Edit... -->

            @yield('content')
        </div>
    </div>
    <!-- /Begin page content -->
</div>
<!-- /Wrap all page content here -->

<!-- Footer -->
@include('battambang/cpanel::layout.default.footer')
<!-- /Footer -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<?php echo HTML::script('packages/battambang/cpanel/bootstrap3/js/bootstrap.min.js'); ?>
<?php echo HTML::script('packages/battambang/cpanel/bootbox/bootbox.min.js'); ?>
<?php echo HTML::script('packages/battambang/cpanel/bootbox/admin.js'); ?>
<?php echo HTML::script('packages/battambang/cpanel/select2-3.3.1/select2.min.js'); ?>
<?php /*echo HTML::script('packages/battambang/cpanel/jcombo/jcombo.js'); */?>
<?php echo HTML::script('packages/battambang/cpanel/datepicker/js/bootstrap-datepicker.js'); ?>
<?php echo HTML::script('packages/battambang/cpanel/datatable/js/jquery.dataTables.min.js'); ?>
<?php echo HTML::script('packages/battambang/cpanel/jquery-ui-1.11.4/jquery-ui.min.js'); ?>
@yield('js')
<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
    });
</script>

</body>
</html>
