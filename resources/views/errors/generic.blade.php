<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — MitraPesisir</title>
    @include('partials.head')
</head>
<body>
<div class="flex min-h-screen items-center justify-center px-5">
    <div class="glass-card w-full max-w-md p-8 text-center">
        <p class="font-mono text-5xl font-bold text-blue-600">{{ $code }}</p>
        <h1 class="mt-3 font-serif text-2xl font-bold">{{ $title }}</h1>
        <p class="mt-2 text-sm text-grey-700">{{ $detail }}</p>
        <a href="{{ url('/beranda') }}" class="btn-primary mt-6">Kembali ke Beranda</a>
    </div>
</div>
</body>
</html>
