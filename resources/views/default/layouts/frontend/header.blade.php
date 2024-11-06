<!--Favicon -->
<link rel="icon" href="{{ URL::asset('uploads/logo/favicon.ico')}}" type="image/x-icon"/>

<!-- Animate -->
<link href="{{theme_url('css/animated.css')}}" rel="stylesheet" />

<!-- Bootstrap 5 -->
<link href="{{URL::asset('plugins/bootstrap-5.0.2/css/bootstrap.min.css')}}" rel="stylesheet">

<!-- Icons -->
<link href="{{ theme_url('css/icons.css')}}" rel="stylesheet" />

<!-- Toastr -->
<link href="{{URL::asset('plugins/toastr/toastr.min.css')}}" rel="stylesheet" />


@yield('css')

<!--Custom User CSS File -->
@if (isset($frontend_settings))
    @if (!is_null($frontend_settings->custom_css_url)) <link href="{{ $frontend_settings->custom_css_url }}" rel="stylesheet"> @endif
@endif




	