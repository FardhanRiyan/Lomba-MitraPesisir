<header class="mx-auto max-w-6xl px-5 pt-8 md:px-8 md:pt-12">
    <div class="flex items-start justify-between gap-3">
        <p class="hero-text flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-100">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-white ring-2 ring-blue-500" aria-hidden="true"></span>
            Pantau pasang surut
        </p>
        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('alerts.index') }}" class="relative flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-navy-900 shadow-card" aria-label="Notifikasi, {{ $unreadAlerts }} belum dibaca">
                    @include('partials.icon', ['name' => 'bell', 'class' => 'h-5 w-5'])
                    @if ($unreadAlerts > 0)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-danger px-1 text-[11px] font-extrabold text-white">{{ $unreadAlerts > 99 ? '99+' : $unreadAlerts }}</span>
                    @endif
                </a>
            @else
                <a href="{{ route('auth') }}" class="rounded-full bg-white/90 px-4 py-2 text-sm font-extrabold text-navy-900 shadow-card">Masuk / Daftar</a>
            @endauth
        </div>
    </div>
    <h1 class="hero-text mt-2 font-serif text-4xl font-extrabold md:text-5xl">@yield('heading', 'MitraPesisir')</h1>
    @hasSection('subtitle')
        <p class="hero-text mt-3 max-w-2xl text-sm leading-relaxed md:text-base">@yield('subtitle')</p>
    @endif
    <svg viewBox="0 0 800 24" class="mt-5 h-5 w-full" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 12 Q 50 0 100 12 T 200 12 T 300 12 T 400 12 T 500 12 T 600 12 T 700 12 T 800 12" fill="none" stroke="#0a5db8" stroke-width="2"/>
    </svg>
</header>
