<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:300,400,700|Roboto+Condensed:400,700" rel="stylesheet">

</head>
<body class="@yield('bodyclass')">

    <div class="site-wrap">
            @yield('content')
            @include('includes.footer')
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="{{ asset('/js/imagesloaded.pkgd.min.js') }}"></script>
	<script src="{{ asset('/js/masonry.pkgd.min.js') }}"></script>
	<script src="{{ asset('/js/app.js') }}"></script>
    @yield ('scripts')

</body>
</html>