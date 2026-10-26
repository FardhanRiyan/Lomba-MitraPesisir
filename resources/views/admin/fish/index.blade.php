@extends('layouts.app')

@section('heading', 'Katalog ikan')
@section('subtitle', 'Kelola data ketersediaan ikan yang tampil di halaman Ikan.')

@section('content')
    <section class="glass-card p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <form method="GET" action="{{ route('admin.fish.index') }}" class="grid flex-1 gap-3 sm:grid-cols-[1fr_12rem_auto]">
                <div>
                    <label for="a-q" class="sr-only">Cari ikan</label>
                    <input id="a-q" type="search" name="q" value="{{ $q }}" placeholder="Cari nama ikan…" class="field-input">
                </div>
                <div>
                    <label for="a-region" class="sr-only">Wilayah</label>
                    <select id="a-region" name="wilayah" class="field-input">
                        <option value="">Semua wilayah</option>
                        @foreach ($regions as $r)
                            <option value="{{ $r->value }}" @selected($region === $r->value)>{{ $r->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary px-8 text-sm">Terapkan</button>
            </form>
            <a href="{{ route('admin.fish.create') }}" class="btn-ghost text-sm">+ Tambah ikan</a>
        </div>

        @if ($fish->isEmpty())
            <p class="mt-6 rounded-md bg-sky-50 p-6 text-center text-sm text-grey-600">Tidak ada data ikan yang cocok.</p>
        @else
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-grey-100 text-xs font-extrabold uppercase tracking-wide text-grey-600">
                            <th class="py-2 pr-3">Foto</th>
                            <th class="py-2 pr-3">Nama</th>
                            <th class="py-2 pr-3">Wilayah</th>
                            <th class="py-2 pr-3">Kelangkaan</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fish as $f)
                            <tr class="border-b border-grey-100 align-middle">
                                <td class="py-3 pr-3">
                                    @if ($f->photoUrl())
                                        <img src="{{ $f->photoUrl() }}" alt="Foto {{ $f->name }}" class="h-12 w-12 rounded-md object-cover">
                                    @else
                                        <span class="text-xs text-grey-400">Belum ada</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-3 font-bold">{{ $f->name }}</td>
                                <td class="py-3 pr-3 text-xs text-grey-700">{{ $f->region->label() }}</td>
                                <td class="py-3 pr-3"><span class="rounded-full px-3 py-0.5 text-xs font-extrabold {{ $f->rarity->badgeClass() }}">{{ $f->rarity->label() }}</span></td>
                                <td class="py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.fish.edit', $f) }}" class="rounded-full bg-blue-100 px-3 py-1 text-xs font-extrabold text-blue-600">Ubah</a>
                                        <form method="POST" action="{{ route('admin.fish.destroy', $f) }}" data-confirm="Hapus {{ $f->name }} ({{ $f->region->label() }})?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $fish->links('pagination.admin-fish') }}
        @endif
    </section>
@endsection
