@extends('layouts.app')

@section('heading', 'Setelan')
@section('subtitle', 'Atur notifikasi pasang surut otomatis, pilih lokasi pesisir favorit untuk dipantau, dan kelola privasi akunmu dengan mudah.')

@section('content')
    <section class="glass-card p-6">
        <h2 class="font-serif text-xl font-bold">Akun saya</h2>
        @auth
            @php $me = auth()->user(); @endphp
            <div class="mt-3 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[#1a0b8f] text-xl font-extrabold text-white" aria-hidden="true">{{ mb_substr(\App\Support\Format::initials($me->name), 0, 1) }}</span>
                    <div>
                        <p class="font-serif text-xl font-bold">{{ $me->name }}</p>
                        <p class="text-sm text-grey-700">No WhatsApp : {{ $me->whatsapp_number }}</p>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-navy-900 px-3 py-0.5 text-xs font-bold text-white">{{ $me->role->label() }}</span>
                            @if ($currentLocation)
                                <span class="rounded-full bg-blue-100 px-3 py-0.5 text-xs font-bold text-blue-600">📍 {{ $currentLocation->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="button" data-modal-open="edit-modal" class="rounded-full bg-blue-100 px-5 py-2 text-sm font-extrabold text-blue-600">Edit</button>
                    <button type="button" data-modal-open="password-modal" class="rounded-full bg-sky-50 px-5 py-2 text-sm font-extrabold text-navy-900">Kata sandi</button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-full bg-red-100 px-5 py-2 text-sm font-extrabold text-danger">LogOut</button>
                    </form>
                </div>
            </div>
        @else
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-md bg-sky-50 p-4">
                <p class="text-sm text-grey-700">Kamu sedang menjelajah sebagai tamu. Masuk untuk menyimpan lokasi, mencatat tangkapan, membuat laporan, dan ikut komunitas.</p>
                <a href="{{ route('auth') }}" class="btn-primary text-sm">Masuk / Daftar</a>
            </div>
        @endauth
    </section>

    <div class="mt-5 grid gap-5 md:grid-cols-2">
        <section id="lokasi" class="glass-card p-6">
            <h2 class="font-serif text-xl font-bold">Pilih Lokasi Pesisir</h2>
            <p class="mb-3 mt-1 text-sm text-grey-600">Cari atau saring berdasarkan wilayah untuk menemukan lokasimu</p>

            <form method="GET" action="{{ route('pengaturan.index') }}#lokasi" class="space-y-3">
                <div class="flex gap-2">
                    <label for="loc-q" class="sr-only">Cari lokasi</label>
                    <input id="loc-q" type="search" name="q" value="{{ $q }}" placeholder="Cari lokasi…" class="field-input">
                    <button type="submit" class="btn-ghost flex-none px-5">Cari</button>
                </div>
                <div class="flex flex-wrap gap-2" role="group" aria-label="Saring wilayah">
                    <a href="{{ route('pengaturan.index', ['q' => $q]) }}#lokasi" class="rounded-full border px-3 py-1.5 text-xs font-extrabold {{ $region === null ? 'border-navy-900 bg-navy-900 text-white' : 'border-grey-100 bg-white text-grey-400' }}">Semua</a>
                    @foreach ($regions as $r)
                        <a href="{{ route('pengaturan.index', ['q' => $q, 'wilayah' => $r->value]) }}#lokasi" class="rounded-full border px-3 py-1.5 text-xs font-extrabold {{ $region === $r->value ? 'border-navy-900 bg-navy-900 text-white' : 'border-grey-100 bg-white text-grey-400' }}">{{ $r->short() }}</a>
                    @endforeach
                </div>
            </form>

            @if ($locations->isEmpty())
                <p class="mt-4 rounded-md bg-sky-50 p-4 text-center text-sm text-grey-600">Lokasi tidak ditemukan.</p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($locations as $loc)
                        @php $isCurrent = $currentLocation && $currentLocation->id === $loc->id; @endphp
                        <li>
                            <form method="POST" action="{{ route('pengaturan.lokasi') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="location_id" value="{{ $loc->id }}">
                                <button type="submit" @if ($isCurrent) aria-current="true" @endif
                                        class="flex w-full items-center justify-between gap-3 rounded-md border-2 px-4 py-3 text-left {{ $isCurrent ? 'border-blue-600 bg-blue-100' : 'border-grey-100 bg-white hover:border-blue-500' }}">
                                    <span>
                                        <span class="block text-sm font-extrabold">{{ $loc->name }}</span>
                                        <span class="block text-xs text-grey-600">{{ $loc->region->label() }}</span>
                                    </span>
                                    @if ($isCurrent)
                                        <span class="text-xs font-extrabold text-blue-600">Aktif ✓</span>
                                    @endif
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="space-y-5">
            <section class="glass-card p-6">
                <h2 class="font-serif text-xl font-bold">Notifikasi dan Mode Offline</h2>
                <p class="mt-1 text-sm text-grey-600">Atur bagaimana MitraPesisir memberi tahu dan menyimpan data untukmu.</p>

                @php
                    $toggles = [
                        ['notif_kondisi', 'Notifikasi Kondisi', 'Dapat pemberitahuan saat status berubah ke waspada atau tidak disarankan melaut.', auth()->user()?->notif_kondisi ?? true],
                        ['whatsapp_opt_in', 'Info melalui WhatsApp', 'Izinkan MitraPesisir mengirim info pesisir melalui WhatsApp. Anda dapat mengubah persetujuan ini kapan saja.', auth()->user()?->whatsapp_opt_in ?? false],
                        ['mode_offline', 'Mode Offline', 'Bekukan tampilan Beranda pada data yang terakhir dimuat (tanpa penyegaran otomatis).', auth()->user()?->mode_offline ?? false],
                    ];
                @endphp
                <div class="mt-4 space-y-3">
                    @foreach ($toggles as [$field, $label, $hint, $on])
                        <div class="flex items-center justify-between gap-4 rounded-md border border-blue-100 bg-blue-100/60 p-4">
                            <div>
                                <p class="text-sm font-extrabold">{{ $label }}</p>
                                <p class="text-xs text-grey-700">{{ $hint }}</p>
                            </div>
                            @auth
                                <form method="POST" action="{{ route('pengaturan.preferensi') }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="{{ $field }}" value="{{ $on ? 0 : 1 }}">
                                    <button type="submit" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}" aria-label="{{ $label }}: {{ $on ? 'aktif, klik untuk mematikan' : 'mati, klik untuk menyalakan' }}"
                                            class="relative h-8 w-14 flex-none rounded-full transition-colors {{ $on ? 'bg-blue-500' : 'bg-grey-400/60' }}">
                                        <span class="absolute top-1 h-6 w-6 rounded-full shadow transition-all {{ $on ? 'left-7 bg-navy-900' : 'left-1 bg-white' }}"></span>
                                    </button>
                                </form>
                            @else
                                <span role="switch" aria-checked="{{ $on ? 'true' : 'false' }}" aria-disabled="true" class="relative h-8 w-14 flex-none rounded-full opacity-60 {{ $on ? 'bg-blue-500' : 'bg-grey-400/60' }}">
                                    <span class="absolute top-1 h-6 w-6 rounded-full shadow {{ $on ? 'left-7 bg-navy-900' : 'left-1 bg-white' }}"></span>
                                </span>
                            @endauth
                        </div>
                    @endforeach
                    @guest
                        <p class="text-xs text-grey-600">Masuk untuk menyimpan pengaturan ini di akunmu.</p>
                    @endguest
                </div>
            </section>

            <section class="rounded-lg bg-blue-100/80 p-5">
                <h2 class="font-serif text-lg font-bold">Tentang data ini</h2>
                <p class="mt-1 text-sm text-grey-700">Semua angka (status pasang surut, cuaca, grafik, riwayat) dihasilkan dari rumus simulasi matematis, bukan dari pengukuran laut yang sesungguhnya.</p>
            </section>
        </div>
    </div>

    @auth
        <dialog id="edit-modal" class="modal" @if ($errors->hasAny(['name', 'whatsapp_number'])) data-open-on-load @endif>
            <h2 class="font-serif text-xl font-bold">Edit akun</h2>
            <form method="POST" action="{{ route('pengaturan.profil') }}" novalidate class="mt-4">
                @csrf
                @method('PATCH')
                <label for="e-name" class="block text-sm font-semibold">Nama lengkap</label>
                <input id="e-name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="field-input mt-1.5 @error('name') has-error @enderror">
                @include('partials.field-error', ['name' => 'name'])

                <label for="e-wa" class="mt-4 block text-sm font-semibold">Nomor WhatsApp</label>
                <input id="e-wa" type="tel" inputmode="numeric" name="whatsapp_number" value="{{ old('whatsapp_number', auth()->user()->whatsapp_number) }}" class="field-input mt-1.5 @error('whatsapp_number') has-error @enderror">
                @include('partials.field-error', ['name' => 'whatsapp_number'])

                <div class="mt-6 flex gap-3">
                    <button type="button" data-modal-close class="btn-ghost flex-1">Batal</button>
                    <button type="submit" class="btn-primary flex-1">Simpan</button>
                </div>
            </form>
        </dialog>

        <dialog id="password-modal" class="modal" @if ($errors->hasAny(['current_password', 'password'])) data-open-on-load @endif>
            <h2 class="font-serif text-xl font-bold">Ganti kata sandi</h2>
            <form method="POST" action="{{ route('pengaturan.password') }}" novalidate class="mt-4">
                @csrf
                @method('PATCH')
                <label for="p-cur" class="block text-sm font-semibold">Kata sandi saat ini</label>
                <input id="p-cur" type="password" autocomplete="current-password" name="current_password" class="field-input mt-1.5 @error('current_password') has-error @enderror">
                @include('partials.field-error', ['name' => 'current_password'])

                <label for="p-new" class="mt-4 block text-sm font-semibold">Kata sandi baru</label>
                <input id="p-new" type="password" autocomplete="new-password" name="password" placeholder="Minimal 6 karakter" class="field-input mt-1.5 @error('password') has-error @enderror">
                @include('partials.field-error', ['name' => 'password'])

                <label for="p-new2" class="mt-4 block text-sm font-semibold">Ulangi kata sandi baru</label>
                <input id="p-new2" type="password" autocomplete="new-password" name="password_confirmation" class="field-input mt-1.5">

                <div class="mt-6 flex gap-3">
                    <button type="button" data-modal-close class="btn-ghost flex-1">Batal</button>
                    <button type="submit" class="btn-primary flex-1">Simpan</button>
                </div>
            </form>
        </dialog>
    @endauth
@endsection
