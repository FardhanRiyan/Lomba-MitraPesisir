<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('heading', 'MitraPesisir') — MitraPesisir</title>
    @include('partials.head')
    @yield('head')
</head>
<body>
<div class="min-h-screen pb-28 md:pb-10">
    @include('partials.header')
    @include('partials.nav')

    <main class="mx-auto mt-5 max-w-6xl px-5 md:px-8">
        @include('partials.flash')
        @yield('content')
        @include('partials.footer')
    </main>
</div>
<script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
