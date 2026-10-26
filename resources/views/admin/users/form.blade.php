@extends('layouts.app')

@section('heading', 'Edit data pengguna')
@section('subtitle', 'Perbarui profil, lokasi, peran, atau atur ulang kata sandi pengguna.')

@section('content')
    <section class="glass-card max-w-2xl p-6">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-bold text-blue-600">&larr; Kembali ke daftar pengguna</a>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-4 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Nama lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="field-input mt-1.5 @error('name') has-error @enderror">
                @include('partials.field-error', ['name' => 'name'])
            </div>

            <div>
                <label for="whatsapp_number" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Nomor WhatsApp</label>
                <input id="whatsapp_number" type="tel" name="whatsapp_number" value="{{ old('whatsapp_number', $user->whatsapp_number) }}" required class="field-input mt-1.5 @error('whatsapp_number') has-error @enderror">
                @include('partials.field-error', ['name' => 'whatsapp_number'])
            </div>

            <div>
                <label for="location_id" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Lokasi</label>
                <select id="location_id" name="location_id" class="field-input mt-1.5 @error('location_id') has-error @enderror">
                    <option value="">Tidak ada lokasi</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) old('location_id', $user->location_id) === (string) $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
                @include('partials.field-error', ['name' => 'location_id'])
            </div>

            <div>
                <label for="role" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Peran</label>
                @if ($user->is(auth()->user()))
                    <p class="mt-1.5 rounded-md bg-sky-50 px-4 py-3 text-sm font-bold text-grey-700">{{ $user->role->label() }} (peran akun sendiri tidak dapat diubah)</p>
                @else
                    <select id="role" name="role" required class="field-input mt-1.5 @error('role') has-error @enderror">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $user->role->value) === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'role'])
                @endif
            </div>

            <div class="border-t border-grey-100 pt-4">
                <h2 class="text-sm font-extrabold text-navy-900">Ganti kata sandi</h2>
                <p class="mt-1 text-xs text-grey-600">Kosongkan kedua kolom jika kata sandi tidak ingin diubah.</p>
            </div>

            <div>
                <label for="password" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Kata sandi baru</label>
                <input id="password" type="password" name="password" autocomplete="new-password" class="field-input mt-1.5 @error('password') has-error @enderror">
                @include('partials.field-error', ['name' => 'password'])
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Konfirmasi kata sandi baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="field-input mt-1.5">
            </div>

            <button type="submit" class="btn-primary w-full sm:w-auto sm:px-12">Simpan perubahan</button>
        </form>
    </section>
@endsection
