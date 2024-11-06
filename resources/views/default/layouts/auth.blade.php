<?php
	$themeClass = '';
	if (!empty($_COOKIE['theme'])) {
		if ($_COOKIE['theme'] == 'dark') {
			$themeClass = 'dark-theme';
		} else if ($_COOKIE['theme'] == 'light') {
			$themeClass = 'light-theme';
		}  
	} elseif (empty($_COOKIE['theme'])) {
		$themeClass = config('settings.default_theme');
		setcookie('theme', $themeClass);
	} 
?>
<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}"
dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}">
	<head>
		<!-- Meta data -->
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  
        <meta name="robots" content="index, follow">	   
        <meta name="revisit-after" content="7 days">	   
        <meta name="distribution" content="web">	
		
		@yield('metadata')
		
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

		@include('layouts.frontend.header')

		@php
			$scss_path = 'resources/views/' . get_theme() . '/scss/frontend.scss';
		@endphp

		<!-- All Styles -->
		@vite($scss_path)

	</head>

	<body class="app sidebar-mini white-background <?php echo $themeClass; ?>">

		<div id="loader-line" class="hidden"></div>

		<!-- Page -->
		<div class="page">
			<div class="page-main">
				
				<!-- App-Content -->			
				<div class="main-content">
					<div class="side-app">

						@yield('content')

					</div>                   
				</div>
		
		</div><!-- End Page -->

		@include('layouts.frontend.footer')
        
	</body>
</html>


