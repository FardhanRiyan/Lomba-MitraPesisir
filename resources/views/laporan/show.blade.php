@extends('layouts.app')

@section('heading', 'Detail laporan')
@section('subtitle', 'Rincian laporan kondisi pesisir beserta status tindak lanjutnya.')

@section('content')
    <section class="glass-card p-6">
        <a href="{{ route('laporan.index') }}" class="text-sm font-bold text-blue-600">&larr; Kembali ke daftar</a>

        <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
            <h2 class="font-serif text-2xl font-bold">{{ $report->title }}</h2>
            <div class="flex flex-wrap gap-1.5">
                <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $report->severity->badgeClass() }}">Tingkat {{ $report->severity->label() }}</span>
                <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $report->status->badgeClass() }}">{{ $report->status->label() }}</span>
            </div>
        </div>

        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-md bg-sky-50 p-3"><dt class="text-xs font-bold text-grey-600">Kategori</dt><dd class="mt-0.5 font-bold">{{ $report->category->label() }}</dd></div>
            <div class="rounded-md bg-sky-50 p-3"><dt class="text-xs font-bold text-grey-600">Lokasi</dt><dd class="mt-0.5 font-bold">{{ $report->location->name }}</dd></div>
            <div class="rounded-md bg-sky-50 p-3"><dt class="text-xs font-bold text-grey-600">Pelapor</dt><dd class="mt-0.5 font-bold">{{ $report->user?->name ?? 'Pengguna terhapus' }}</dd></div>
            <div class="rounded-md bg-sky-50 p-3"><dt class="text-xs font-bold text-grey-600">Dilaporkan</dt><dd class="mt-0.5 font-bold">{{ $report->created_at->translatedFormat('d F Y, H.i') }} WIB</dd></div>
        </dl>

        <p class="mt-5 whitespace-pre-line text-sm leading-relaxed">{{ $report->description }}</p>

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-grey-100 pt-5">
            @can('update', $report)
                <a href="{{ route('laporan.edit', $report) }}" class="btn-ghost text-sm">Ubah laporan</a>
            @endcan
            @can('delete', $report)
                <form method="POST" action="{{ route('laporan.destroy', $report) }}" data-confirm="Hapus laporan ini? Tindakan tidak bisa dibatalkan.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger px-5 py-2.5 text-sm">Hapus</button>
                </form>
            @endcan

            @can('updateStatus', $report)
                <form method="POST" action="{{ route('admin.laporan.status', $report) }}" class="ml-auto flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <label for="status" class="text-xs font-extrabold uppercase tracking-wide text-grey-700">Status</label>
                    <select id="status" name="status" class="field-input w-auto">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" @selected($report->status === $s)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary px-5 py-2.5 text-sm">Simpan</button>
                </form>
            @endcan
        </div>
    </section>
@endsection
