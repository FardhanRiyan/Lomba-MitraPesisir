@extends('layouts.app')

@php $editing = $fish->exists; @endphp

@section('heading', $editing ? 'Ubah data ikan' : 'Tambah ikan')
@section('subtitle', 'Data ini tampil di halaman Ikan sesuai wilayah dan tingkat kelangkaannya.')

@section('content')
    <section class="glass-card max-w-2xl p-6">
        <a href="{{ route('admin.fish.index') }}" class="text-sm font-bold text-blue-600">&larr; Batal</a>

        <form method="POST" action="{{ $editing ? route('admin.fish.update', $fish) : route('admin.fish.store') }}" enctype="multipart/form-data" novalidate class="mt-4 space-y-4">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Nama ikan</label>
                <input id="name" type="text" name="name" value="{{ old('name', $fish->name) }}" class="field-input mt-1.5 @error('name') has-error @enderror">
                @include('partials.field-error', ['name' => 'name'])
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="region" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Wilayah</label>
                    <select id="region" name="region" class="field-input mt-1.5 @error('region') has-error @enderror">
                        @foreach ($regions as $r)
                            <option value="{{ $r->value }}" @selected(old('region', $fish->region?->value) === $r->value)>{{ $r->label() }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'region'])
                </div>
                <div>
                    <label for="rarity" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Kelangkaan</label>
                    <select id="rarity" name="rarity" class="field-input mt-1.5 @error('rarity') has-error @enderror">
                        @foreach ($rarities as $r)
                            <option value="{{ $r->value }}" @selected(old('rarity', $fish->rarity?->value) === $r->value)>{{ $r->label() }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'rarity'])
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Deskripsi</label>
                <textarea id="description" name="description" rows="4" class="field-input mt-1.5 @error('description') has-error @enderror">{{ old('description', $fish->description) }}</textarea>
                @include('partials.field-error', ['name' => 'description'])
            </div>

            @if ($fish->photoUrl())
                <div>
                    <p class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Foto saat ini</p>
                    <img src="{{ $fish->photoUrl() }}" alt="Foto {{ $fish->name }}" class="mt-2 max-h-64 rounded-md object-cover">
                </div>
            @endif

            <div>
                <label for="image" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Unggah foto (JPG, PNG, atau WebP; maks. 5 MB)</label>
                <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="field-input mt-1.5 @error('image') has-error @enderror">
                @include('partials.field-error', ['name' => 'image'])
            </div>

            <div>
                <label for="image_url" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Atau tautan foto (opsional)</label>
                <input id="image_url" type="url" name="image_url" value="{{ old('image_url', filter_var($fish->image_url, FILTER_VALIDATE_URL) ? $fish->image_url : '') }}" placeholder="https://…" class="field-input mt-1.5 @error('image_url') has-error @enderror">
                @include('partials.field-error', ['name' => 'image_url'])
            </div>

            @if ($editing && $fish->image_url)
                <label class="flex items-center gap-2 text-sm text-grey-700">
                    <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))>
                    Hapus foto yang tersimpan saat ini
                </label>
            @endif

            <button type="submit" class="btn-primary w-full sm:w-auto sm:px-12">{{ $editing ? 'Simpan perubahan' : 'Tambah ikan' }}</button>
        </form>
    </section>
@endsection
