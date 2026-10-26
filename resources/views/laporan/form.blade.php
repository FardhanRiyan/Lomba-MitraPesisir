@extends('layouts.app')

@php $editing = $report->exists; @endphp

@section('heading', $editing ? 'Ubah laporan' : 'Buat laporan')
@section('subtitle', 'Laporkan kondisi pesisir agar admin dapat meninjau dan menindaklanjutinya.')

@section('content')
    <section class="glass-card mx-auto max-w-2xl p-6">
        <a href="{{ $editing ? route('laporan.show', $report) : (auth()->user()->isAdmin() ? route('laporan.index') : route('beranda')) }}" class="text-sm font-bold text-blue-600">&larr; Batal</a>

        <form method="POST" action="{{ $editing ? route('laporan.update', $report) : route('laporan.store') }}" novalidate class="mt-4 space-y-4">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div>
                <label for="title" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Judul</label>
                <input id="title" type="text" name="title" value="{{ old('title', $report->title) }}" placeholder="Contoh: Air pasang melewati dermaga"
                       class="field-input mt-1.5 @error('title') has-error @enderror">
                @include('partials.field-error', ['name' => 'title'])
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="category" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Kategori</label>
                    <select id="category" name="category" class="field-input mt-1.5 @error('category') has-error @enderror">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->value }}" @selected(old('category', $report->category?->value) === $c->value)>{{ $c->label() }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'category'])
                </div>
                <div>
                    <label for="severity" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Tingkat keparahan</label>
                    <select id="severity" name="severity" class="field-input mt-1.5 @error('severity') has-error @enderror">
                        @foreach ($severities as $s)
                            <option value="{{ $s->value }}" @selected(old('severity', $report->severity?->value) === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'severity'])
                </div>
            </div>

            <div>
                <label for="location_id" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Lokasi kejadian</label>
                <select id="location_id" name="location_id" class="field-input mt-1.5 @error('location_id') has-error @enderror">
                    @foreach ($locations as $l)
                        <option value="{{ $l->id }}" @selected((int) old('location_id', $report->location_id) === $l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
                @include('partials.field-error', ['name' => 'location_id'])
            </div>

            <div>
                <label for="description" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Deskripsi kejadian</label>
                <textarea id="description" name="description" rows="6" placeholder="Ceritakan apa yang terjadi, kapan, dan dampaknya."
                          class="field-input mt-1.5 @error('description') has-error @enderror">{{ old('description', $report->description) }}</textarea>
                @include('partials.field-error', ['name' => 'description'])
            </div>

            <button type="submit" class="btn-primary w-full sm:w-auto sm:px-12">{{ $editing ? 'Simpan perubahan' : 'Kirim laporan' }}</button>
        </form>
    </section>
@endsection
