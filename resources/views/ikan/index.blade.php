@extends('layouts.app')

@section('heading', 'Informasi ketersediaan ikan')
@section('subtitle', 'Lihat seluruh data ikan di katalog atau saring berdasarkan wilayah, lalu catat hasil tangkapanmu.')

@section('content')
    <section class="glass-card p-6">
        <h2 class="font-serif text-2xl font-bold">Katalog ikan</h2>
        <p class="mt-1 text-sm text-grey-600">Data ikan dari katalog admin. Tanpa filter wilayah, seluruh data akan ditampilkan.</p>

        <form method="GET" action="{{ route('ikan.index') }}" class="mt-4 grid max-w-3xl gap-3 sm:grid-cols-[1fr_12rem_auto]">
            <div>
                <label for="fish-q" class="sr-only">Cari nama ikan</label>
                <input id="fish-q" type="search" name="q" value="{{ $q }}" placeholder="Cari nama ikan…" class="field-input">
            </div>
            <div>
                <label for="fish-region" class="sr-only">Filter wilayah</label>
                <select id="fish-region" name="wilayah" class="field-input">
                    <option value="">Semua wilayah</option>
                    @foreach ($regions as $r)
                        <option value="{{ $r->value }}" @selected($region === $r->value)>{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-ghost px-5">Terapkan</button>
        </form>

        @if ($fish->isEmpty())
            <p class="py-10 text-center text-sm text-grey-600">{{ $q || $region ? 'Tidak ada ikan yang cocok dengan filter.' : 'Belum ada data ikan di katalog.' }}</p>
        @else
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-grey-600">
                    @if ($showAllFish)
                        Menampilkan semua {{ $fishCount }} data ikan.
                    @elseif ($fishCount > 5)
                        Menampilkan 5 ikan paling umum dari {{ $fishCount }} data yang cocok.
                    @else
                        Menampilkan {{ $fishCount }} data ikan.
                    @endif
                </p>
                @if ($fishCount > 5)
                    @if ($showAllFish)
                        <a href="{{ route('ikan.index', request()->except('semua')) }}" class="text-sm font-extrabold text-blue-600">Tampilkan lebih sedikit</a>
                    @else
                        <a href="{{ route('ikan.index', array_merge(request()->query(), ['semua' => 1])) }}" class="text-sm font-extrabold text-blue-600">Lihat semua ikan ({{ $fishCount }})</a>
                    @endif
                @endif
            </div>
            <ul class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($fish as $f)
                    <li>
                        <article class="rounded-md border border-grey-100 bg-white p-4">
                            <a href="{{ route('ikan.index', ['wilayah' => $region, 'ikan' => $f->name]) }}#catat"
                               class="flex w-full items-start gap-4 text-left hover:text-blue-600"
                               aria-label="{{ $f->name }}, {{ $f->rarity->label() }}. Pilih untuk dicatat.">
                                <span class="flex h-20 w-20 flex-none items-center justify-center overflow-hidden rounded-md bg-gradient-to-br from-blue-100 to-teal-100 text-3xl" aria-hidden="true">
                                    @if ($f->photoUrl())
                                        <img src="{{ $f->photoUrl() }}" alt="Foto {{ $f->name }} di wilayah {{ $f->region->label() }}" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        🐟
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-start justify-between gap-2">
                                        <span class="text-base font-extrabold">{{ $f->name }}</span>
                                        <span class="flex-none rounded-full px-3 py-0.5 text-xs font-extrabold {{ $f->rarity->badgeClass() }}">{{ $f->rarity->label() }}</span>
                                    </span>
                                    <span class="mt-1 line-clamp-2 block text-sm text-grey-600">{{ $f->description }}</span>
                                    <span class="mt-2 block text-xs font-bold text-blue-600">Pilih untuk mencatat tangkapan</span>
                                </span>
                            </a>
                            <details class="mt-3 border-t border-grey-100 pt-3">
                                <summary class="cursor-pointer text-sm font-extrabold text-blue-600">Tampilkan selengkapnya</summary>
                                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <dt class="font-bold text-grey-700">Deskripsi</dt>
                                        <dd class="mt-1 whitespace-pre-line text-grey-600">{{ $f->description }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-bold text-grey-700">Wilayah</dt>
                                        <dd class="mt-1 text-grey-600">{{ $f->region->label() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-bold text-grey-700">Tingkat kelangkaan</dt>
                                        <dd class="mt-1 text-grey-600">{{ $f->rarity->label() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-bold text-grey-700">Ditambahkan</dt>
                                        <dd class="mt-1 text-grey-600">{{ $f->created_at->format('d M Y, H:i') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-bold text-grey-700">Terakhir diperbarui</dt>
                                        <dd class="mt-1 text-grey-600">{{ $f->updated_at->format('d M Y, H:i') }}</dd>
                                    </div>
                                </dl>
                                @if ($f->photoUrl())
                                    <img src="{{ $f->photoUrl() }}" alt="Foto {{ $f->name }}" class="mt-3 max-h-72 rounded-md object-cover" loading="lazy">
                                @else
                                    <p class="mt-3 text-sm text-grey-600">Belum ada foto untuk jenis ikan ini.</p>
                                @endif
                            </details>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section id="riwayat" class="glass-card mt-5 p-6">
        <h2 class="font-serif text-2xl font-bold">Riwayat penangkapan</h2>
        <p class="mt-1 text-sm text-grey-600">Catat ikan yang berhasil kamu tangkap — riwayat ini membantu melihat pola tangkapan dari waktu ke waktu.</p>

        <p class="mt-1 text-sm text-grey-600">
            @if ($region)
                Riwayat dan foto ditampilkan sesuai filter wilayah {{ \App\Enums\Region::from($region)->label() }}.
            @else
                Semua riwayat dan foto tangkapanmu ditampilkan.
            @endif
        </p>
        <form id="catat" method="POST" action="{{ route('tangkapan.store') }}" enctype="multipart/form-data" novalidate class="mt-4 max-w-xl">
            @csrf
            <div>
                <label for="fish_name" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Jenis ikan</label>
                <input id="fish_name" type="text" name="fish_name" value="{{ old('fish_name', $prefillFish) }}" placeholder="Contoh: Tenggiri"
                       class="field-input mt-1.5 @error('fish_name') has-error @enderror">
                @include('partials.field-error', ['name' => 'fish_name'])
            </div>
            <div class="mt-4">
                <label for="catch-region" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Wilayah tangkapan</label>
                <select id="catch-region" name="region" required class="field-input mt-1.5 @error('region') has-error @enderror">
                    @foreach ($regions as $r)
                        <option value="{{ $r->value }}" @selected(old('region', $catchRegion) === $r->value)>{{ $r->label() }}</option>
                    @endforeach
                </select>
                @include('partials.field-error', ['name' => 'region'])
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
                <label for="catch-image" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Foto ikan / tangkapan (opsional, maks. 5 MB)</label>
                <input id="catch-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       class="field-input mt-1.5 @error('image') has-error @enderror">
                <p class="mt-1 text-xs text-grey-600">Jika jenis ikan belum ada, foto akan membuat data katalog ikan untuk wilayah ini. Jika data sudah ada tanpa foto, foto ini akan ditambahkan. Foto katalog dapat ditinjau dan diubah oleh admin.</p>
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
