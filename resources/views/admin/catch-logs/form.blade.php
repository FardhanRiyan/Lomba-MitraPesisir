@extends('layouts.app')

@section('heading', 'Edit catatan tangkapan')
@section('subtitle', 'Perbarui isi dan foto catatan tangkapan pengguna.')

@section('content')
    @include('admin.subnav')

    <section class="glass-card max-w-2xl p-6">
        <a href="{{ route('admin.catch-logs.index') }}" class="text-sm font-bold text-blue-600">&larr; Kembali ke daftar tangkapan</a>
        <p class="mt-3 text-sm text-grey-600">Dikirim oleh <strong>{{ $log->user?->name ?? 'Pengguna dihapus' }}</strong>
            @if ($log->user?->whatsapp_number)
                · {{ $log->user->whatsapp_number }}
            @endif
            · {{ $log->created_at->format('d M Y, H:i') }}
        </p>

        <form method="POST" action="{{ route('admin.catch-logs.update', $log) }}" enctype="multipart/form-data" novalidate class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="fish_name" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Jenis ikan</label>
                <input id="fish_name" type="text" name="fish_name" value="{{ old('fish_name', $log->fish_name) }}" class="field-input mt-1.5 @error('fish_name') has-error @enderror">
                @include('partials.field-error', ['name' => 'fish_name'])
            </div>

            <div>
                <label for="quantity" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Jumlah / berat</label>
                <input id="quantity" type="text" name="quantity" value="{{ old('quantity', $log->quantity) }}" class="field-input mt-1.5 @error('quantity') has-error @enderror">
                @include('partials.field-error', ['name' => 'quantity'])
            </div>

            <div>
                <label for="note" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Catatan (opsional)</label>
                <textarea id="note" name="note" rows="4" class="field-input mt-1.5 @error('note') has-error @enderror">{{ old('note', $log->note) }}</textarea>
                @include('partials.field-error', ['name' => 'note'])
            </div>

            <div>
                <label for="region" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Wilayah</label>
                <select id="region" name="region" class="field-input mt-1.5 @error('region') has-error @enderror">
                    @foreach ($regions as $r)
                        <option value="{{ $r->value }}" @selected(old('region', $log->region?->value) === $r->value)>{{ $r->label() }}</option>
                    @endforeach
                </select>
                @include('partials.field-error', ['name' => 'region'])
            </div>

            @if ($log->image_path)
                <div>
                    <p class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Foto saat ini</p>
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($log->image_path) }}" alt="Foto tangkapan {{ $log->fish_name }}" class="mt-2 max-h-64 rounded-md object-cover">
                </div>
            @endif

            <div>
                <label for="image" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Ganti foto (JPG, PNG, atau WebP; maks. 5 MB)</label>
                <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="field-input mt-1.5 @error('image') has-error @enderror">
                @include('partials.field-error', ['name' => 'image'])
            </div>

            @if ($log->image_path)
                <label class="flex items-center gap-2 text-sm text-grey-700">
                    <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))>
                    Hapus foto saat ini
                </label>
            @endif

            <button type="submit" class="btn-primary w-full sm:w-auto sm:px-12">Simpan perubahan</button>
        </form>
    </section>
@endsection
