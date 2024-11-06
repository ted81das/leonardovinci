<!--Favicon -->
<link rel="icon" href="{{ URL::asset('uploads/logo/favicon.ico')}}" type="image/x-icon"/>

<!-- Bootstrap 5 -->
<link href="{{URL::asset('plugins/bootstrap-5.0.2/css/bootstrap.min.css')}}" rel="stylesheet">

<!-- Icons -->
<link href="{{ theme_url('css/icons.css')}}" rel="stylesheet" />

<!-- P-scrollbar -->
<link href="{{URL::asset('plugins/p-scrollbar/p-scrollbar.css')}}" rel="stylesheet" />

<!-- Simplebar -->
<link href="{{URL::asset('plugins/simplebar/css/simplebar.css')}}" rel="stylesheet">

<!-- Tippy -->
<link href="{{URL::asset('plugins/tippy/scale-extreme.css')}}" rel="stylesheet" />
<link href="{{URL::asset('plugins/tippy/material.css')}}" rel="stylesheet" />

<!-- Toastr -->
<link href="{{URL::asset('plugins/toastr/toastr.min.css')}}" rel="stylesheet" />

<link href="{{URL::asset('plugins/awselect/awselect.min.css')}}" rel="stylesheet" />

@yield('css')

@php
    $scss_path = 'resources/views/' . get_theme() . '/scss/dashboard.scss';
@endphp

<!-- All Styles -->
@vite($scss_path)



	