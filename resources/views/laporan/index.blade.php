@extends('layouts.app')

@section('heading', 'Laporan pesisir')
@section('subtitle', 'Tinjau laporan kondisi pesisir yang dikirim pengguna — cari dan saring berdasarkan kategori, tingkat, status, dan lokasi.')

@section('content')
    <section class="glass-card p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-serif text-2xl font-bold">Daftar laporan</h2>
                <p class="mt-1 text-sm text-grey-600">{{ $reports->total() }} laporan ditemukan.</p>
            </div>
            @auth
                <a href="{{ route('laporan.create') }}" class="btn-primary text-sm">+ Buat laporan</a>
            @else
                <a href="{{ route('auth') }}" class="btn-ghost text-sm">Masuk untuk membuat laporan</a>
            @endauth
        </div>

        <form method="GET" action="{{ route('laporan.index') }}" class="mt-5 grid gap-3 md:grid-cols-6">
            <div class="md:col-span-2">
                <label for="f-q" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Cari</label>
                <input id="f-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Judul atau isi laporan…" class="field-input mt-1.5">
            </div>
            <div>
                <label for="f-kategori" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Kategori</label>
                <select id="f-kategori" name="kategori" class="field-input mt-1.5">
                    <option value="">Semua</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->value }}" @selected($filters['category'] === $c->value)>{{ $c->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-tingkat" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Tingkat</label>
                <select id="f-tingkat" name="tingkat" class="field-input mt-1.5">
                    <option value="">Semua</option>
                    @foreach ($severities as $s)
                        <option value="{{ $s->value }}" @selected($filters['severity'] === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-status" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Status</label>
                <select id="f-status" name="status" class="field-input mt-1.5">
                    <option value="">Semua</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-lokasi" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Lokasi</label>
                <select id="f-lokasi" name="lokasi" class="field-input mt-1.5">
                    <option value="">Semua</option>
                    @foreach ($locations as $l)
                        <option value="{{ $l->id }}" @selected($filters['location_id'] === $l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 md:col-span-6">
                <button type="submit" class="btn-primary px-8 text-sm">Terapkan</button>
                @if ($hasFilter)
                    <a href="{{ route('laporan.index') }}" class="btn-ghost text-sm">Reset</a>
                @endif
            </div>
        </form>

        @if ($reports->isEmpty())
            <p class="mt-6 rounded-md bg-sky-50 p-6 text-center text-sm text-grey-600">
                {{ $hasFilter ? 'Tidak ada laporan yang cocok dengan filter.' : 'Belum ada laporan.' }}
            </p>
        @else
            <ul class="mt-6 space-y-3">
                @foreach ($reports as $r)
                    <li>
                        <a href="{{ route('laporan.show', $r) }}" class="block rounded-md border border-grey-100 bg-white p-4 hover:border-blue-500">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="text-base font-extrabold">{{ $r->title }}</p>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="rounded-full px-3 py-0.5 text-xs font-extrabold {{ $r->severity->badgeClass() }}">{{ $r->severity->label() }}</span>
                                    <span class="rounded-full px-3 py-0.5 text-xs font-extrabold {{ $r->status->badgeClass() }}">{{ $r->status->label() }}</span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-grey-600">{{ \Illuminate\Support\Str::limit($r->description, 140) }}</p>
                            <p class="mt-2 text-xs text-grey-400">
                                {{ $r->category->label() }} · {{ $r->location->name }} · oleh {{ $r->user?->name ?? 'pengguna terhapus' }} · {{ $r->created_at->diffForHumans() }}
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>
            {{ $reports->links() }}
        @endif
    </section>
@endsection
