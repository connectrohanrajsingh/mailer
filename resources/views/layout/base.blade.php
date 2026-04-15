<!DOCTYPE html>
<html lang="en">

	<head>
		<title>@yield('title')</title>

		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<meta name="description" content="Portal - Bootstrap 5 Admin Dashboard Template For Developers">
		<meta name="author" content="Xiaoying Riley at 3rd Wave Media">
		<link rel="shortcut icon" href="favicon.ico">

		@stack('before-styles')
		<!-- FontAwesome JS-->
		<script defer src="{{ asset('assets/plugins/fontawesome/js/all.min.js')}}"></script>

		<!-- App CSS -->
		<link id="theme-style" rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">

		@stack('after-styles')
	</head>


	<body class="app">
		<header class="app-header fixed-top">
			@include('layout.navbar')
			@include('layout.sidebar')
		</header>

		<div class="app-wrapper">
			@yield('content')
			@include('layout.footer')
		</div>

		@stack('before-scripts')
		<!-- Javascript -->
		<script src="{{ asset('assets/plugins/popper.min.js')}}"></script>
		<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>

		<!-- Page Specific JS -->
		<script src="{{ asset('assets/js/app.js')}}"></script>
		@stack('after-scripts')
	</body>

</html>