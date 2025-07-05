<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'POS') }}</title> 

    @include('layouts.partials.css')

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .eq-height-col {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .right-col {
            background-color: #ffffff;
            height: 100%;
            min-height: calc(100vh - 100px);
        }
        .login-header{
            text-align: center;
        }
        .login-header a{
            font-size: 60px;
            font-weight: 600 !important;
            text-shadow: 2px 2px #4aaee1;
            color: #fff;
        }
        .login-header small{
            color: #fff;
        }
        .right-col-content {
            padding: 10%;
            background: #fff;
            border-radius: 0 5px 5px 0;
        }
        .login-form-cointainer{
            width: 80%;
            box-shadow: 0 0 5px #8f8f8f;
            border-radius: 5px;
        }
        .login-form-cointainer .login-form-left{
            background-color: #7bd4ff;
            background-image: url("{{ asset('uploads/bg.png') }}");
            background-size: cover;
        }
        .login-form-cointainer .login-form a,
        .login-form-cointainer .login-form label{
            color: #000;
        }
        .login-btn{
            background: #7bd4ff;
            color: #fff;
        }
    </style>
</head>

<body>
    @inject('request', 'Illuminate\Http\Request')
    @if (session('status'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
    @endif
    <div class="container-fluid">
        <div class="row eq-height-row">
            <div class="col-md-12 col-sm-12 hidden-xs">
                <div class="row">
                    <div class="col-md-2 col-xs-3" style="text-align: left;">
                        <select class="form-control input-sm" id="change_lang" style="margin: 10px;">
                        @foreach(config('constants.langs') as $key => $val)
                            <option value="{{$key}}" 
                                @if( (empty(request()->lang) && config('app.locale') == $key) 
                                || request()->lang == $key) 
                                    selected 
                                @endif
                            >
                                {{$val['full_name']}}
                            </option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-10 col-xs-9" style="text-align: right;padding-top: 10px;">
                        @if(!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                            <!-- Register Url -->
                            @if(config('constants.allow_registration'))
                                <a href="{{ route('business.getRegister') }}@if(!empty(request()->lang)){{'?lang=' . request()->lang}} @endif" class="btn bg-maroon btn-flat" ><b>{{ __('business.not_yet_registered')}}</b> {{ __('business.register_now') }}</a>
                                <!-- pricing url -->
                                @if(Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing')
                                    &nbsp; <a href="{{ action('\Modules\Superadmin\Http\Controllers\PricingController@index') }}">@lang('superadmin::lang.pricing')</a>
                                @endif
                            @endif
                        @endif
                        @if($request->segment(1) != 'login')
                            &nbsp; &nbsp;<span class="text-white">{{ __('business.already_registered')}} </span><a class="btn btn-flat login-btn" href="{{ action('Auth\LoginController@login') }}@if(!empty(request()->lang)){{'?lang=' . request()->lang}} @endif">{{ __('business.sign_in') }}</a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-12 col-sm-12 col-xs-12 right-col eq-height-col">
                <div class="row eq-height-row @if($request->segment(1) == 'login') login-form-cointainer @endif">
                    @yield('content')
                </div>
            </div>
            
        </div>
    </div>

    
    @include('layouts.partials.javascripts')
    
    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>
    
    @yield('javascript')

    <script type="text/javascript">
        $(document).ready(function(){
            $('.select2_register').select2();

            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
</body>

</html>