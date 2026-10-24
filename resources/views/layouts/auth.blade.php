<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('heading', 'Masuk') — MitraPesisir</title>
    @include('partials.head')
</head>
<body>
<div class="min-h-screen px-5 pb-12 pt-10 md:px-8">
    <div class="mx-auto max-w-xl">
        @include('partials.flash')
        @yield('content')
    </div>
</div>
<script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
