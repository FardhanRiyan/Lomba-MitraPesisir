@extends('layouts.app')

@section('heading', 'Komunitas')
@section('subtitle', 'Ruang diskusi bersama warga pesisir untuk bertukar kabar situasi perairan terkini, peringatan bahaya lokal, dan saran jalur pelayaran.')

@section('content')
    <div class="grid gap-5 md:grid-cols-[1fr_1.3fr]">
        <section id="tulis" class="glass-card h-fit p-6">
            <h2 class="font-serif text-xl font-bold">Tulis info untuk komunitas</h2>
            <p class="mt-1 text-sm text-grey-600">Bagikan kondisi terkini di lokasimu agar pengguna lain mendapat informasi.</p>

            <form method="POST" action="{{ route('komunitas.store') }}" novalidate class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="c-name" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Nama</label>
                    <input id="c-name" type="text" name="name" value="{{ old('name', $defaultName) }}" placeholder="Nama kamu"
                           class="field-input mt-1.5 @error('name') has-error @enderror">
                    @include('partials.field-error', ['name' => 'name'])
                </div>
                <div>
                    <label for="c-area" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Daerah</label>
                    <input id="c-area" type="text" name="area" value="{{ old('area', $defaultArea) }}" placeholder="Daerah kamu"
                           class="field-input mt-1.5 @error('area') has-error @enderror">
                    @include('partials.field-error', ['name' => 'area'])
                </div>
                <div>
                    <label for="c-message" class="block text-xs font-extrabold uppercase tracking-wide text-grey-700">Pesan</label>
                    <textarea id="c-message" name="message" rows="4" placeholder="Contoh: Air surut cepat sore ini, harap hati-hati"
                              class="field-input mt-1.5 @error('message') has-error @enderror">{{ old('message', $prefillMessage) }}</textarea>
                    @include('partials.field-error', ['name' => 'message'])
                </div>
                @auth
                    <button type="submit" class="btn-primary w-full">Kirim ke komunitas</button>
                @else
                    <a href="{{ route('auth') }}" class="btn-primary w-full">Masuk untuk mengirim</a>
                @endauth
            </form>
        </section>

        <section class="glass-card p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-serif text-xl font-bold">Papan komunitas pesisir</h2>
                    <p class="mt-1 text-sm text-grey-600">Info dan kabar terbaru dari pengguna di berbagai lokasi.</p>
                </div>
                <a href="{{ route('komunitas.index') }}" class="btn-ghost flex-none px-4 py-2 text-xs">Muat ulang</a>
            </div>

            <form method="GET" action="{{ route('komunitas.index') }}" class="mt-4 flex gap-2">
                <label for="post-q" class="sr-only">Cari pesan</label>
                <input id="post-q" type="search" name="q" value="{{ $q }}" placeholder="Cari pesan, nama, atau daerah…" class="field-input">
                <button type="submit" class="btn-ghost flex-none px-5">Cari</button>
            </form>

            @if ($posts->isEmpty())
                <p class="mt-4 rounded-md bg-sky-50 p-6 text-center text-sm text-grey-600">
                    {{ $q ? 'Tidak ada pesan yang cocok dengan pencarian.' : 'Belum ada pesan. Jadilah yang pertama membagikan kabar.' }}
                </p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($posts as $post)
                        <li class="rounded-md border border-grey-100 bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-[#0b0b4a] text-xs font-extrabold text-white" aria-hidden="true">{{ \App\Support\Format::initials($post->name) }}</span>
                                    <span class="truncate text-sm font-extrabold">{{ $post->name }}</span>
                                </div>
                                <span class="flex-none font-mono text-xs text-grey-400">{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mt-1.5 text-xs font-bold text-[#4a3fd6]">{{ $post->area }}</p>
                            @if (mb_strlen($post->message) > 180)
                                <details class="community-message mt-1">
                                    <summary class="cursor-pointer list-none text-sm">
                                        <span class="community-message-excerpt whitespace-pre-line">{{ \Illuminate\Support\Str::limit($post->message, 180) }}</span>
                                        <span class="community-message-expand font-bold text-blue-600">Lihat lainnya</span>
                                        <span class="community-message-collapse font-bold text-blue-600">Tampilkan lebih sedikit</span>
                                    </summary>
                                    <p class="mt-1 whitespace-pre-line text-sm">{{ $post->message }}</p>
                                </details>
                            @else
                                <p class="mt-1 whitespace-pre-line text-sm">{{ $post->message }}</p>
                            @endif
                            @auth
                                <form method="POST" action="{{ route('komunitas.reply', $post) }}" class="mt-3 flex gap-2">
                                    @csrf
                                    <label for="reply-{{ $post->id }}" class="sr-only">Balas pesan dari {{ $post->name }}</label>
                                    <input id="reply-{{ $post->id }}" type="text" name="message" maxlength="1000" required placeholder="Tulis balasan…" class="field-input">
                                    <button type="submit" class="btn-ghost flex-none px-4 text-xs">Balas</button>
                                </form>
                            @endauth
                            @if ($post->replies->isNotEmpty())
                                <ul class="mt-3 space-y-2 border-l-2 border-blue-100 pl-3">
                                    @foreach ($post->replies as $reply)
                                        <li class="rounded-md bg-sky-50 p-3">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs font-extrabold">{{ $reply->name }} <span class="font-semibold text-grey-400">· {{ $reply->area }}</span></span>
                                                <span class="font-mono text-[10px] text-grey-400">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            @if (mb_strlen($reply->message) > 180)
                                                <details class="community-message mt-1">
                                                    <summary class="cursor-pointer list-none text-sm">
                                                        <span class="community-message-excerpt whitespace-pre-line">{{ \Illuminate\Support\Str::limit($reply->message, 180) }}</span>
                                                        <span class="community-message-expand font-bold text-blue-600">Lihat lainnya</span>
                                                        <span class="community-message-collapse font-bold text-blue-600">Tampilkan lebih sedikit</span>
                                                    </summary>
                                                    <p class="mt-1 whitespace-pre-line text-sm">{{ $reply->message }}</p>
                                                </details>
                                            @else
                                                <p class="mt-1 whitespace-pre-line text-sm">{{ $reply->message }}</p>
                                            @endif
                                            @auth
                                                <form method="POST" action="{{ route('komunitas.reply', $reply) }}" class="mt-2 flex gap-2">
                                                    @csrf
                                                    <label for="reply-{{ $reply->id }}" class="sr-only">Balas pesan dari {{ $reply->name }}</label>
                                                    <input id="reply-{{ $reply->id }}" type="text" name="message" maxlength="1000" required placeholder="Balas {{ $reply->name }}…" class="field-input">
                                                    <button type="submit" class="btn-ghost flex-none px-4 text-xs">Balas</button>
                                                </form>
                                            @endauth
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            @can('delete', $post)
                                <form method="POST" action="{{ route('komunitas.destroy', $post) }}" data-confirm="Hapus pesan ini?" class="mt-2 text-right">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            @endcan
                        </li>
                    @endforeach
                </ul>
                {{ $posts->links() }}
            @endif
        </section>
    </div>
@endsection
