@extends('layouts.app')

@section('heading', 'Panel admin')
@section('subtitle', 'Ringkasan statistik aplikasi, sebaran laporan, dan akses cepat ke pengelolaan pengguna serta katalog ikan.')

@section('content')
    @include('admin.subnav')

    <section class="glass-card mb-5 p-6">
        <h2 class="font-serif text-xl font-bold">Bagikan info pasang surut</h2>
        <p class="mt-1 text-sm text-grey-600">
            Informasi lokasi aktif akan dikirim otomatis melalui WhatsApp kepada user yang memberi persetujuan.
            @unless ($whatsappConfigured)
                Lengkapi konfigurasi Meta WhatsApp Cloud API di environment server sebelum mengirim.
            @endunless
        </p>
        <form method="POST" action="{{ route('admin.share-information') }}" class="mt-4">
            @csrf
            <button type="submit" @disabled(! $whatsappConfigured)
                    class="btn-primary disabled:cursor-not-allowed disabled:opacity-50">
                Bagikan ke WhatsApp user
            </button>
        </form>
    </section>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-6">
        @foreach ($totals as $label => $value)
            <div class="glass-card p-4 text-center">
                <p class="font-mono text-3xl font-bold">{{ number_format($value, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs font-bold text-grey-600">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        @foreach ([['Laporan per status', $statusRows], ['Laporan per kategori', $categoryRows], ['Pengguna per peran', $roleRows]] as [$title, $rows])
            @php $max = max(1, $rows->max('total')); @endphp
            <section class="glass-card p-6">
                <h2 class="font-serif text-lg font-bold">{{ $title }}</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($rows as $row)
                        <li>
                            <div class="flex justify-between text-xs font-bold"><span>{{ $row['label'] }}</span><span class="font-mono">{{ $row['total'] }}</span></div>
                            <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-grey-100">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ round($row['total'] / $max * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        <section class="glass-card p-6">
            <h2 class="font-serif text-lg font-bold">Lokasi dengan laporan terbanyak</h2>
            @if ($topLocations->isEmpty())
                <p class="mt-3 rounded-md bg-sky-50 p-4 text-sm text-grey-600">Belum ada laporan.</p>
            @else
                <ol class="mt-3 space-y-2">
                    @foreach ($topLocations as $row)
                        <li class="flex items-center justify-between rounded-md border border-grey-100 bg-white px-4 py-2.5 text-sm">
                            <span class="font-bold">{{ $row->location?->name ?? '-' }}</span>
                            <span class="font-mono text-xs text-grey-600">{{ $row->total }} laporan</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <section class="glass-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-lg font-bold">Laporan terbaru</h2>
                <a href="{{ route('laporan.index') }}" class="text-xs font-bold text-blue-600">Lihat semua</a>
            </div>
            @if ($latestReports->isEmpty())
                <p class="mt-3 rounded-md bg-sky-50 p-4 text-sm text-grey-600">Belum ada laporan.</p>
            @else
                <ul class="mt-3 space-y-2">
                    @foreach ($latestReports as $r)
                        <li>
                            <a href="{{ route('laporan.show', $r) }}" class="block rounded-md border border-grey-100 bg-white px-4 py-2.5 hover:border-blue-500">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-sm font-bold">{{ $r->title }}</span>
                                    <span class="flex-none rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $r->status->badgeClass() }}">{{ $r->status->label() }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-grey-400">{{ $r->location->name }} · {{ $r->created_at->diffForHumans() }}</p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
