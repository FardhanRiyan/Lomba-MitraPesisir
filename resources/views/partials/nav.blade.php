@php
    $adminArea = request()->routeIs(
        'admin.*',
        'laporan.index',
        'laporan.show',
        'laporan.edit',
        'laporan.update',
        'laporan.destroy',
    );
    $navItems = $adminArea
        ? [
            ['admin.dashboard', 'Ringkasan', 'home', 'admin.dashboard'],
            ['admin.users.index', 'Pengguna', 'people', 'admin.users.*'],
            ['admin.catch-logs.index', 'Tangkapan', 'fish', 'admin.catch-logs.*'],
            ['admin.fish.index', 'Katalog ikan', 'fish', 'admin.fish.*'],
            ['beranda', 'Dashboard pengguna', 'home', 'beranda'],
        ]
        : [
            ['beranda', 'Beranda', 'home', 'beranda'],
            ['prediksi', 'Prediksi', 'wave', 'prediksi'],
            ['ikan.index', 'Ikan', 'fish', 'ikan.*'],
            ['laporan.create', 'Laporan', 'report', 'laporan.*'],
            ['komunitas.index', 'Komunitas', 'people', 'komunitas.*'],
            ['pengaturan.index', 'Setelan', 'gear', 'pengaturan.*'],
        ];
    if (! $adminArea && auth()->user()?->isAdmin()) {
        $navItems[] = ['admin.dashboard', 'Admin', 'shield', 'admin.*'];
    }
@endphp
<nav aria-label="{{ $adminArea ? 'Navigasi admin' : 'Navigasi utama' }}" class="fixed inset-x-0 bottom-0 z-40 border-t border-grey-100 bg-white md:static md:z-auto md:mx-auto md:mt-4 md:max-w-6xl md:border-0 md:bg-transparent md:px-8">
    <ul class="mx-auto flex max-w-6xl justify-around md:justify-between md:gap-1 md:rounded-lg md:bg-white/90 md:p-2 md:shadow-card">
        @foreach ($navItems as [$route, $label, $icon, $pattern])
            @php $active = request()->routeIs($pattern); @endphp
            <li class="flex-1">
                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                   class="flex flex-col items-center gap-1 px-1 py-2.5 text-[10px] font-bold md:flex-row md:justify-center md:gap-2 md:rounded-md md:text-sm {{ $active ? 'text-blue-600 md:bg-white md:shadow-card' : 'text-navy-800/70 hover:text-blue-600' }}">
                    @include('partials.icon', ['name' => $icon, 'class' => 'h-5 w-5'])
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
