@extends('layouts.app')

@section('heading', 'Kelola pengguna')
@section('subtitle', 'Cari pengguna, saring berdasarkan peran, edit data akun, atau hapus akun.')

@section('content')
    <section class="glass-card p-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 sm:grid-cols-[1fr_12rem_auto]">
            <div>
                <label for="u-q" class="sr-only">Cari pengguna</label>
                <input id="u-q" type="search" name="q" value="{{ $q }}" placeholder="Cari nama atau nomor WhatsApp…" class="field-input">
            </div>
            <div>
                <label for="u-role" class="sr-only">Peran</label>
                <select id="u-role" name="peran" class="field-input">
                    <option value="">Semua peran</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->value }}" @selected($role === $r->value)>{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary px-8 text-sm">Terapkan</button>
        </form>

        @if ($users->isEmpty())
            <p class="mt-6 rounded-md bg-sky-50 p-6 text-center text-sm text-grey-600">Tidak ada pengguna yang cocok.</p>
        @else
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-grey-100 text-xs font-extrabold uppercase tracking-wide text-grey-600">
                            <th class="py-2 pr-3">Nama</th>
                            <th class="py-2 pr-3">WhatsApp</th>
                            <th class="py-2 pr-3">Lokasi</th>
                            <th class="py-2 pr-3">Peran</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr class="border-b border-grey-100 align-middle">
                                <td class="py-3 pr-3 font-bold">{{ $u->name }} @if ($u->is(auth()->user())) <span class="text-xs font-semibold text-grey-400">(Anda)</span> @endif</td>
                                <td class="py-3 pr-3 font-mono text-xs">{{ $u->whatsapp_number }}</td>
                                <td class="py-3 pr-3 text-xs text-grey-700">{{ $u->location?->name ?? '-' }}</td>
                                <td class="py-3 pr-3">
                                    <span class="rounded-full bg-navy-900 px-3 py-0.5 text-xs font-extrabold text-white">{{ $u->role->label() }}</span>
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $u) }}" class="rounded-full bg-blue-100 px-3 py-1 text-xs font-extrabold text-blue-600">Edit</a>
                                        @unless ($u->is(auth()->user()))
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" data-confirm="Hapus pengguna {{ $u->name }}? Seluruh catatan tangkapannya ikut terhapus.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-danger">Hapus</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        @endif
    </section>
@endsection
