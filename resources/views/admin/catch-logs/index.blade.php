@extends('layouts.app')

@section('heading', 'Kelola tangkapan')
@section('subtitle', 'Tinjau dan edit semua catatan tangkapan yang dikirim oleh pengguna.')

@section('content')
    @include('admin.subnav')

    <section class="glass-card p-6">
        <form method="GET" action="{{ route('admin.catch-logs.index') }}" class="grid gap-3 sm:grid-cols-[1fr_12rem_auto]">
            <div>
                <label for="catch-q" class="sr-only">Cari catatan tangkapan</label>
                <input id="catch-q" type="search" name="q" value="{{ $q }}" placeholder="Cari ikan, catatan, atau pengguna…" class="field-input">
            </div>
            <div>
                <label for="catch-region" class="sr-only">Wilayah</label>
                <select id="catch-region" name="wilayah" class="field-input">
                    <option value="">Semua wilayah</option>
                    @foreach ($regions as $r)
                        <option value="{{ $r->value }}" @selected($region === $r->value)>{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary px-8 text-sm">Terapkan</button>
        </form>

        @if ($logs->isEmpty())
            <p class="mt-6 rounded-md bg-sky-50 p-6 text-center text-sm text-grey-600">Belum ada catatan tangkapan yang cocok.</p>
        @else
            <div class="mt-5 space-y-3">
                @foreach ($logs as $log)
                    <article class="rounded-md border border-grey-100 bg-white p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="font-extrabold">{{ $log->fish_name }} <span class="font-semibold text-grey-600">· {{ $log->quantity }}</span></h2>
                                <p class="mt-1 text-xs text-grey-600">
                                    Pengirim: {{ $log->user?->name ?? 'Pengguna dihapus' }}
                                    @if ($log->user?->whatsapp_number)
                                        · {{ $log->user->whatsapp_number }}
                                    @endif
                                    · {{ $log->region ? $log->region->label() : 'Wilayah tidak tersedia' }}
                                    · {{ $log->created_at->format('d M Y, H:i') }}
                                </p>
                                @if ($log->note)
                                    <p class="mt-2 whitespace-pre-line text-sm text-grey-700">{{ $log->note }}</p>
                                @endif
                                @if ($log->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($log->image_path) }}" alt="Foto tangkapan {{ $log->fish_name }}" class="mt-3 max-h-48 rounded-md object-cover">
                                @endif
                            </div>
                            <a href="{{ route('admin.catch-logs.edit', $log) }}" class="rounded-full bg-blue-100 px-4 py-2 text-xs font-extrabold text-blue-600">Edit catatan</a>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-4">{{ $logs->links() }}</div>
        @endif
    </section>
@endsection
