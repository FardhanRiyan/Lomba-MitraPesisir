@extends('layouts.app')

@section('heading', 'Prediksi air laut')
@section('subtitle', 'Dapatkan analisis akurat perkiraan tinggi gelombang, grafik pasang surut, hingga estimasi waktu terbaik untuk beraktivitas dan melaut dengan aman.')

@section('content')
    <p class="mb-3 text-sm font-semibold text-navy-900">
        Lokasi: <span class="rounded-full bg-white/80 px-3 py-1">{{ $location?->name ?? '-' }}</span>
        <a href="{{ route('pengaturan.index') }}#lokasi" class="ml-2 font-bold text-blue-600 underline">ganti</a>
    </p>

    @if (! $location)
        <div class="glass-card p-6 text-center text-sm font-semibold text-danger">Belum ada lokasi pesisir. Jalankan <code>php artisan db:seed</code> terlebih dahulu.</div>
    @else
        <div class="grid gap-5 lg:grid-cols-2">
            <section class="glass-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-serif text-2xl font-bold">Prediksi pasang surut</h2>
                    <div class="flex rounded-full bg-blue-100 p-1" role="group" aria-label="Rentang prediksi">
                        @foreach ([24, 48] as $h)
                            <a href="{{ route('prediksi', ['jam' => $h]) }}" @if ($hours === $h) aria-current="true" @endif
                               class="rounded-full px-4 py-1.5 text-sm font-extrabold {{ $hours === $h ? 'bg-navy-900 text-white' : 'text-navy-900' }}">{{ $h }} Jam</a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    @include('prediksi.chart', ['chart' => $predictionChart])
                </div>

                <ul class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs font-semibold text-navy-800">
                    <li class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-blue-600"></span>Ketinggian air laut</li>
                    <li class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-danger"></span>Waktu saat ini</li>
                    <li class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-navy-900"></span>Puncak pasang / surut</li>
                </ul>
            </section>

            <section class="glass-card p-6">
                <h2 class="font-serif text-2xl font-bold">Riwayat Pasang Surut</h2>
                <p class="mt-1 text-sm text-grey-600">Pola 7 hari terakhir — membantu membaca kebiasaan air laut untuk merencanakan waktu melaut berikutnya.</p>

                <div class="mt-4">
                    @include('prediksi.chart', ['chart' => $historyChart])
                </div>

                @if ($history)
                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-md border border-blue-100 bg-blue-100/60 p-3">
                            <p class="font-mono text-lg font-bold">{{ \App\Support\Format::meters($history['average']) }}m</p>
                            <p class="mt-1 text-[11px] font-semibold leading-tight text-grey-700">Rata-rata tinggi air</p>
                        </div>
                        <div class="rounded-md border border-blue-100 bg-blue-100/60 p-3">
                            <p class="font-mono text-lg font-bold">{{ \App\Support\Format::meters($history['highest']['value']) }}m</p>
                            <p class="mt-1 text-[11px] font-semibold leading-tight text-grey-700">Pasang tertinggi {{ \App\Support\Format::dayClock($history['highest']['at']) }}</p>
                        </div>
                        <div class="rounded-md border border-blue-100 bg-blue-100/60 p-3">
                            <p class="font-mono text-lg font-bold">{{ \App\Support\Format::meters($history['lowest']['value']) }}m</p>
                            <p class="mt-1 text-[11px] font-semibold leading-tight text-grey-700">Surut terendah {{ \App\Support\Format::dayClock($history['lowest']['at']) }}</p>
                        </div>
                    </div>
                @endif

                <p class="mt-4 text-center text-sm text-grey-700">Bandingkan pola ini dengan hasil tangkapan pekan lalu untuk melihat kaitan antara waktu melaut dan kondisi air laut.</p>
            </section>
        </div>
    @endif
@endsection
