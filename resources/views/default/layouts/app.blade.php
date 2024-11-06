<?php
	$themeClass = '';
	if (!empty($_COOKIE['theme'])) {
		if ($_COOKIE['theme'] == 'dark') {
			$themeClass = 'dark-theme';
		} else if ($_COOKIE['theme'] == 'light') {
			$themeClass = 'light-theme';
		}  
	} elseif (empty($_COOKIE['theme'])) {
		$themeClass = auth()->user()->theme;
		setcookie('theme', $themeClass);
	} else {
		$themeClass = config('settings.default_theme');
		setcookie('theme', $themeClass);
	}
?>
<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}"
dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}">
	<head>
		<!-- METADATA -->
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta content="" name="description">
		<meta content="" name="author">
		<meta name="keywords" content=""/>
		
        <!-- CSRF TOKEN -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- TITLE -->
        <title>{{ config('app.name', 'DaVinci AI') }}</title>
        
        @include('layouts.dashboard.header')

	</head>

	<body class="app sidebar-mini <?php echo $themeClass; ?> {{ LaravelLocalization::getCurrentLocaleDirection() }}">

		<div id="loader-line" class="hidden"></div>

		<!-- PAGE -->
		<div class="page">
			<div class="page-main" style="poisition: relative;">

				@include('layouts.dashboard.nav-aside')

				<!-- APP CONTENT -->			
				<div class="app-content main-content" style="padding-bottom: 4rem">
					<div class="side-app">

						@include('layouts.dashboard.nav-top')

						@yield('page-header')

						@yield('content')						

						
                    </div>   					
					
                </div>				
                <!-- END APP CONTENT -->
				
				@include('layouts.dashboard.footer')

            </div>		
        </div><!-- END PAGE -->
        
		        

	</body>
</html>


