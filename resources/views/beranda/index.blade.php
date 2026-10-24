@extends('layouts.app')

@section('heading', 'MitraPesisir')
@section('subtitle', 'Pusat informasi lengkap untuk memantau kondisi air laut, perkiraan cuaca harian, dan kabar terbaru wilayah pesisir secara real-time langsung dari genggamanmu.')

@section('head')
    {{-- Penyegaran otomatis tiap 60 detik, kecuali Mode Offline aktif. --}}
    @unless ($offline)
        <meta http-equiv="refresh" content="60">
    @endunless
@endsection

@section('content')
    @if ($offline)
        <p role="status" class="mb-4 rounded-md bg-white/90 px-4 py-2 text-sm font-semibold text-navy-900">
            Mode offline aktif — halaman tidak disegarkan otomatis. Matikan di menu Setelan untuk pembaruan berkala.
        </p>
    @endif

    @if (! $status)
        <div class="glass-card p-6 text-center">
            <p class="text-sm font-semibold text-danger">Belum ada lokasi pesisir. Jalankan <code>php artisan db:seed</code> terlebih dahulu.</p>
        </div>
    @else
        @if ($showAlertBanner)
            <div role="alert" class="mb-5 rounded-md px-5 py-4 text-sm font-bold shadow-card {{ $status['level']->bannerClass() }}">
                {{ $status['level']->label() }}: air laut {{ \App\Support\Format::meters($status['waterLevel']) }} m di {{ $location->name }}.
                {{ $status['level']->message() }}
                @auth
                    <a href="{{ route('alerts.index') }}" class="ml-1 underline">Lihat notifikasi</a>
                @endauth
            </div>
        @endif

        <div class="grid gap-5 md:grid-cols-[1fr_1.2fr]">
            <div class="space-y-5">
                <section class="glass-card p-6" aria-live="polite">
                    <div class="flex items-start justify-between gap-3">
                        <svg viewBox="0 0 48 48" class="h-12 w-12 {{ $status['level']->ringClass() }}" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M8 20 16 10h12l6 8"/><path d="M4 28c4-4 8 4 12 0s8-4 12 0 8 4 12 0"/><path d="M4 36c4-4 8 4 12 0s8-4 12 0 8 4 12 0"/>
                        </svg>
                        <p class="text-sm font-semibold text-navy-900">{{ $location->name }}</p>
                    </div>
                    <div class="mt-2 flex items-center gap-3">
                        <h2 class="font-serif text-3xl font-extrabold">{{ $status['level']->label() }}</h2>
                        <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $status['level']->badgeClass() }}">{{ $status['level']->value }}</span>
                    </div>
                    <p class="mt-1 text-sm text-grey-700">{{ $status['level']->message() }}</p>

                    <p class="mt-5 font-mono text-5xl font-bold text-navy-900">{{ \App\Support\Format::meters($status['waterLevel']) }}<span class="ml-1 text-lg">m</span></p>
                    <p class="text-xs font-semibold uppercase tracking-widest text-grey-600">Tinggi air saat ini</p>

                    <p class="mt-5 text-sm font-extrabold text-navy-900">
                        <span aria-hidden="true">{{ ['NAIK' => '↑', 'TURUN' => '↓', 'STABIL' => '→'][$status['trend']] }}</span>
                        {{ ucfirst($trendText[$status['trend']]) }}
                    </p>
                    <p class="text-xs font-semibold uppercase tracking-widest text-grey-600">Tren 1 jam terakhir</p>

                    <hr class="my-4 border-grey-100">
                    <div class="flex flex-wrap justify-between gap-2 text-xs text-grey-700">
                        <span>Diperbarui {{ \App\Support\Format::clock($status['recordedAt']) }} WIB</span>
                        <span>
                            @if ($status['nextHighTideAt'])
                                Pasang tertinggi sekitar {{ \App\Support\Format::clock($status['nextHighTideAt']) }}
                            @else
                                Pasang tertinggi: -
                            @endif
                        </span>
                    </div>
                </section>

                <section class="glass-card p-4">
                    <a href="{{ route('pengaturan.index') }}#lokasi" class="block w-full rounded-full border-2 border-blue-500 py-3 text-center text-sm font-extrabold text-blue-600 hover:bg-blue-100">Ganti lokasi pesisir</a>
                </section>
            </div>

            <div class="space-y-5">
                <section class="glass-card p-6">
                    <h2 class="font-serif text-2xl font-bold">Cuaca &amp; Gelombang</h2>
                    <p class="mt-1 text-sm text-grey-600">Perkiraan kondisi permukaan laut yang dipilih</p>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="rounded-md border border-blue-100 bg-blue-100/60 p-4 text-center">
                            <p class="text-xs font-semibold text-grey-700">Kecepatan angin</p>
                            <p class="mt-2 font-mono text-2xl font-bold">{{ $status['windSpeedKmh'] }} km/j</p>
                        </div>
                        <div class="rounded-md border border-blue-100 bg-blue-100/60 p-4 text-center">
                            <p class="text-xs font-semibold text-grey-700">Ketinggian gelombang</p>
                            <p class="mt-2 font-mono text-2xl font-bold">{{ \App\Support\Format::meters($status['waveHeightM'], 1) }} m</p>
                        </div>
                    </div>
                </section>

                @if (auth()->user()?->isAdmin())
                    <section class="glass-card p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="font-serif text-2xl font-bold">Laporan di lokasi ini</h2>
                                <p class="mt-1 text-sm text-grey-600">Kabar terbaru dari warga di {{ $location->name }}.</p>
                            </div>
                            <a href="{{ route('laporan.index', ['lokasi' => $location->id]) }}" class="btn-ghost flex-none px-4 py-2 text-xs">Semua</a>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                            @foreach (\App\Enums\ReportStatus::cases() as $s)
                                <div class="rounded-md border border-blue-100 bg-blue-100/60 p-3">
                                    <p class="font-mono text-xl font-bold">{{ (int) ($reportCounts[$s->value] ?? 0) }}</p>
                                    <p class="mt-1 text-[11px] font-semibold text-grey-700">{{ $s->label() }}</p>
                                </div>
                            @endforeach
                        </div>
                        @forelse ($recentReports as $r)
                            <a href="{{ route('laporan.show', $r) }}" class="mt-3 block rounded-md border border-grey-100 bg-white p-3 hover:border-blue-500">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-extrabold">{{ $r->title }}</p>
                                    <span class="flex-none rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $r->severity->badgeClass() }}">{{ $r->severity->label() }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-grey-400">{{ $r->created_at->diffForHumans() }}</p>
                            </a>
                        @empty
                            <p class="mt-3 rounded-md bg-sky-50 p-4 text-sm text-grey-600">Belum ada laporan untuk lokasi ini.</p>
                        @endforelse
                    </section>
                @endif
            </div>
        </div>
    @endif
@endsection
