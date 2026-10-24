<nav class="mb-5 flex flex-wrap gap-2" aria-label="Menu admin">
    @foreach ([['admin.dashboard', 'Ringkasan'], ['admin.users.index', 'Pengguna'], ['admin.catch-logs.index', 'Tangkapan'], ['admin.fish.index', 'Katalog ikan']] as [$route, $label])
        <a href="{{ route($route) }}" @if (request()->routeIs($route === 'admin.dashboard' ? $route : explode('.', $route)[0].'.'.explode('.', $route)[1].'.*')) aria-current="page" @endif
           class="rounded-full px-5 py-2 text-sm font-extrabold shadow-card {{ request()->routeIs($route === 'admin.dashboard' ? $route : explode('.', $route)[0].'.'.explode('.', $route)[1].'.*') ? 'bg-navy-900 text-white' : 'bg-white text-grey-600' }}">{{ $label }}</a>
    @endforeach
</nav>
