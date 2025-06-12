<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title')</title>

        <!-- Fonts -->
        <!-- <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,600" rel="stylesheet" type="text/css"> -->
        
        <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">

        <!-- Styles -->
        <style>
            body {
                min-height: 100vh;
                background-color: #7bd4ff;
                color: #fff;
                background-image: url("{{ asset('uploads/bg.png') }}");
                background-size: cover;
            }
            .navbar-default {
                border: none;
                background: #4aaee1;
                box-shadow: 0 4px 6px -6px black;
            }
            .navbar-default .navbar-brand{
                color: #fff !important;
            }
            .navbar-default .navbar-toggle:hover,
            .navbar-default .navbar-toggle:focus{
                background-color: #4aaee1;
            }
            .navbar-default .navbar-toggle .icon-bar {
                background-color: #fff;
            }
            .home-brand{
                display: flex;
                height: calc(100vh - 100px);
                align-items: center;
            }
            .home-brand .title{
                text-shadow: 2px 2px #4aaee1;
            }
            .navbar-static-top {
                margin-bottom: 19px;
            }
            .navbar-default .navbar-nav>li>a {
                color: #fff;
                font-weight: 600;
                font-size: 15px
            }
            .navbar-default .navbar-nav>li>a:hover{
                color: #ccc;
            }
        </style>
    </head>

    <body>
        @if(session('warning'))
            <div class="alert alert-danger text-center">
                {!! session('warning') !!}
            </div>
        @endif
        @include('layouts.partials.home_header')
        <div class="container home-brand">
            <div class="content">
                @yield('content')
            </div>
        </div>
        @include('layouts.partials.javascripts')

    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>
    @yield('javascript')
    </body>
</html>