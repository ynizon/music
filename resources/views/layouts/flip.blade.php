<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
	<script src="{{ asset('js/modernizr.custom.70748.js') }}"></script>
	<script src="{{ asset('js/jquery.transitions.js') }}"></script>

	<link href="/css/flip.css" rel="stylesheet" type="text/css">	
</head>
<body>
    @yield('content')    
</body>
</html>
