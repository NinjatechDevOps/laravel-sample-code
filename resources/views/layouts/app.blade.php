<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="robots" content="noindex, nofollow"  />
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Favicon icon -->
        <!-- <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/admin/images/favicon.png') }}"> -->
        <link rel="icon" type="image/x-icon" sizes="16x16" href="{{getSetting('site_favicon')}}">
        <title>{{ config('app.name', siteName()) }}</title>
        <!-- page css -->
        <link href="{{ asset('assets/admin/dist/css/pages/login-register-lock.css') }}" rel="stylesheet">
        <!-- Custom CSS -->
        <link href="{{ asset('assets/admin/dist/css/style.min.css') }}" rel="stylesheet">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="{{ asset('assets/admin/dist/js/html5shiv.js') }}"></script>
        <script src="{{ asset('assets/admin/dist/js/respond.min.js') }}"></script>
        <![endif]-->
    </head>
    <body>
    <body class="skin-default card-no-border">
        <div class="preloader">
            <div class="loader">
                <div class="loader__figure"></div>
                <p class="loader__label"></p>
            </div>
        </div>
        @yield('content')
        <script src="{{ asset('assets/admin/node_modules/jquery/dist/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/admin/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
        <script type="text/javascript">
$(function () {
    $(".preloader").fadeOut();
});
        </script>
    </body>
</html>
