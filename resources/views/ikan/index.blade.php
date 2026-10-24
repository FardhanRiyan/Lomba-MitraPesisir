@extends('layouts.app')

@section('heading', 'Informasi ketersediaan ikan')
@section('subtitle', 'Lihat ikan yang sering dijumpai di wilayah pilihanmu, lengkap dengan tingkat kelangkaannya, dan catat hasil tangkapanmu di sini.')

@section('content')
    <section class="glass-card p-6">
        <h2 class="font-serif text-2xl font-bold">Ketersediaan ikan per wilayah</h2>
        <p class="mt-1 text-sm text-grey-600">Pilih wilayah penangkapan untuk melihat ikan yang sering dijumpai beserta tingkat kelangkaannya.</p>

        <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="Pilih wilayah">
            @foreach ($regions as $r)
                <a href="{{ route('ikan.index', ['wilayah' => $r->value]) }}" @if ($region === $r->value) aria-current="true" @endif
                   class="rounded-full border px-4 py-2 text-sm font-extrabold {{ $region === $r->value ? 'border-navy-900 bg-navy-900 text-white' : 'border-grey-100 bg-white text-grey-400' }}">{{ $r->label() }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('ikan.index') }}" class="mt-4 flex max-w-md gap-2">
            <input type="hidden" name="wilayah" value="{{ $region }}">
            <label for="fish-q" class="sr-only">Cari nama ikan</label>
            <input id="fish-q" type="search" name="q" value="{{ $q }}" placeholder="Cari nama ikan…" class="field-input">
            <button type="submit" class="btn-ghost flex-none px-5">Cari</button>
        </form>

        @if ($fish->isEmpty())
            <p class="py-10 text-center text-sm text-grey-600">{{ $q ? 'Tidak ada ikan yang cocok dengan pencarian.' : 'Belum ada data ikan untuk wilayah ini.' }}</p>
        @else
            <ul class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($fish as $f)
                    @php
                        $fishImage = $f->image_path
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($f->image_path)
                            : ($f->image_url ?: ($catchImages[mb_strtolower(trim($f->name))] ?? null));
                    @endphp
                    <li>
                        <a href="{{ route('ikan.index', ['wilayah' => $region, 'ikan' => $f->name]) }}#catat"
                           class="flex w-full items-start gap-4 rounded-md border border-grey-100 bg-white p-4 text-left hover:border-blue-500"
                           aria-label="{{ $f->name }}, {{ $f->rarity->label() }}. Pilih untuk dicatat.">
                            <span class="flex h-20 w-20 flex-none items-center justify-center overflow-hidden rounded-md bg-gradient-to-br from-blue-100 to-teal-100 text-3xl" aria-hidden="true">
                                @if ($fishImage)
                                    <img src="{{ $fishImage }}" alt="Foto {{ $f->name }} di wilayah {{ $region }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    🐟
                                @endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-start justify-between gap-2">
                                    <span class="text-base font-extrabold">{{ $f->name }}</span>
                                    <span class="flex-none rounded-full px-3 py-0.5 text-xs font-extrabold {{ $f->rarity->badgeClass() }}">{{ $f->rarity->label() }}</span>
                                </span>
                                <span class="mt-1 block text-sm text-grey-600">{{ $f->description }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            @if ($fish->hasPages())
                <nav class="mt-6 flex flex-wrap items-center justify-between gap-3" aria-label="Halaman katalog ikan">
                    <p class="text-sm text-grey-600">Menampilkan {{ $fish->firstItem() }}–{{ $fish->lastItem() }} dari {{ $fish->total() }} jenis ikan.</p>
                    @if ($fish->hasMorePages())
                        <a href="{{ $fish->nextPageUrl() }}" rel="next" class="btn-ghost px-5">
                            Lihat selengkapnya
                        </a>
                    @endif
                </nav>
                <div class="mt-3">{{ $fish->links() }}</div>
            @endif
        @endif
    </section>

    <section id="riwayat" class="glass-card mt-5 p-6">
        <h2 class="font-serif text-2xl font-bold">Riwayat penangkapan</h2>
        <p class="mt-1 text-sm text-grey-600">Catat ikan yang berhasil kamu tangkap — riwayat ini membantu melihat pola tangkapan dari waktu ke waktu.</p>

        <p class="mt-1 text-sm text-grey-600">Riwayat dan foto ditampilkan sesuai wilayah yang dipilih: {{ \App\Enums\Region::from($region)->label() }}.</p>
        <form id="catat" method="POST" action="{{ route('tangkapan.store') }}" enctype="multipart/form-data" novalidate class="mt-4 max-w-xl">
            @csrf
            <input type="hidden" name="region" value="{{ $region }}">
            <div>
                <label for="fish_name" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Jenis ikan</label>
                <input id="fish_name" type="text" name="fish_name" value="{{ old('fish_name', $prefillFish) }}" placeholder="Contoh: Tenggiri"
                       class="field-input mt-1.5 @error('fish_name') has-error @enderror">
                @include('partials.field-error', ['name' => 'fish_name'])
            </div>
            <div class="mt-4">
                <label for="quantity" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Jumlah / berat</label>
                <input id="quantity" type="text" name="quantity" value="{{ old('quantity') }}" placeholder="Contoh: 5 kg atau 12 ekor"
                       class="field-input mt-1.5 @error('quantity') has-error @enderror">
                @include('partials.field-error', ['name' => 'quantity'])
            </div>
            <div class="mt-4">
                <label for="note" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Catatan (opsional)</label>
                <textarea id="note" name="note" rows="3" placeholder="Contoh: Didapat dari tengah laut, air sedang tinggi dan ombak besar"
                          class="field-input mt-1.5 @error('note') has-error @enderror">{{ old('note') }}</textarea>
                @include('partials.field-error', ['name' => 'note'])
            </div>
            <div class="mt-4">
                <label for="catch-image" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Foto tangkapan (opsional, maks. 5 MB)</label>
                <input id="catch-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       class="field-input mt-1.5 @error('image') has-error @enderror">
                @include('partials.field-error', ['name' => 'image'])
            </div>

            @auth
                <button type="submit" class="btn-primary mt-5 w-full sm:w-auto sm:px-12">Catat tangkapan</button>
            @else
                <a href="{{ route('auth') }}" class="btn-primary mt-5 w-full sm:w-auto sm:px-12">Masuk untuk mencatat</a>
            @endauth
        </form>

        @auth
            <div class="mt-6">
                <h3 class="text-sm font-extrabold">Catatan saya</h3>
                @if ($logs->isEmpty())
                    <p class="mt-2 rounded-md bg-sky-50 p-4 text-sm text-grey-600">Belum ada catatan. Catat tangkapan pertamamu di atas.</p>
                @else
                    <ul class="mt-2 space-y-2">
                        @foreach ($logs as $log)
                            <li class="flex items-start justify-between gap-3 rounded-md border border-grey-100 bg-white p-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-extrabold">{{ $log->fish_name }} <span class="font-semibold text-grey-600">· {{ $log->quantity }}</span></p>
                                    @if ($log->note)
                                        <p class="mt-0.5 text-sm text-grey-600">{{ $log->note }}</p>
                                    @endif
                                    @if ($log->image_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($log->image_path) }}" alt="Foto tangkapan {{ $log->fish_name }}" class="mt-2 max-h-48 rounded-md object-cover">
                                    @endif
                                    <p class="mt-0.5 text-xs text-grey-400">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                                <form method="POST" action="{{ route('tangkapan.destroy', $log) }}" data-confirm="Hapus catatan {{ $log->fish_name }} ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    {{ $logs->fragment('riwayat')->links() }}
                @endif
            </div>
        @endauth
    </section>
@endsection
