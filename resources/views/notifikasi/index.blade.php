@extends('layouts.app')

@section('heading', 'Notifikasi')
@section('subtitle', 'Informasi dari admin dan peringatan kondisi pesisir untuk akunmu.')

@section('content')
    <section class="glass-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex rounded-full bg-blue-100 p-1" role="group" aria-label="Saring notifikasi">
                <a href="{{ route('alerts.index') }}" class="rounded-full px-4 py-1.5 text-sm font-extrabold {{ $onlyUnread ? 'text-navy-900' : 'bg-navy-900 text-white' }}">Semua</a>
                <a href="{{ route('alerts.index', ['filter' => 'belum']) }}" class="rounded-full px-4 py-1.5 text-sm font-extrabold {{ $onlyUnread ? 'bg-navy-900 text-white' : 'text-navy-900' }}">Belum dibaca ({{ $unreadTotal }})</a>
            </div>
            @if ($unreadTotal > 0)
                <form method="POST" action="{{ route('alerts.readAll') }}">
                    @csrf
                    <button type="submit" class="btn-ghost px-5 py-2 text-sm">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        @if ($userNotifications->isNotEmpty())
            <section class="mt-6" aria-labelledby="admin-messages-heading">
                <h2 id="admin-messages-heading" class="font-serif text-lg font-bold">Informasi dari admin</h2>
                <ul class="mt-3 space-y-3">
                    @foreach ($userNotifications as $notification)
                        <li class="rounded-md border p-4 {{ $notification->isRead() ? 'border-grey-100 bg-white' : 'border-blue-500 bg-blue-100/50' }}">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="text-sm font-extrabold">{{ $notification->title }}</p>
                                @unless ($notification->isRead())
                                    <span class="rounded-full bg-blue-100 px-3 py-0.5 text-xs font-extrabold text-blue-600">Baru</span>
                                @endunless
                            </div>
                            <p class="mt-1 whitespace-pre-line text-sm text-grey-700">{{ $notification->message }}</p>
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs text-grey-400">{{ $notification->created_at->diffForHumans() }}</p>
                                <div class="flex gap-2">
                                    @unless ($notification->isRead())
                                        <form method="POST" action="{{ route('alerts.messages.read', $notification) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-full border border-blue-100 px-3 py-1 text-xs font-bold text-blue-600 hover:bg-blue-100">Tandai dibaca</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('alerts.messages.destroy', $notification) }}" data-confirm="Hapus informasi ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                {{ $userNotifications->links() }}
            </section>
        @endif

        @if ($alerts->isEmpty())
            <p class="mt-6 rounded-md bg-sky-50 p-6 text-center text-sm text-grey-600">
                {{ $onlyUnread ? 'Tidak ada peringatan kondisi yang belum dibaca.' : 'Belum ada peringatan kondisi. Peringatan akan muncul saat kondisi air laut Waspada atau Bahaya.' }}
            </p>
        @else
            <ul class="mt-5 space-y-3">
                @foreach ($alerts as $alert)
                    <li class="rounded-md border p-4 {{ $alert->isRead() ? 'border-grey-100 bg-white' : 'border-blue-500 bg-blue-100/50' }}">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <p class="text-sm font-extrabold">{{ $alert->title }}</p>
                            <div class="flex flex-wrap gap-1.5">
                                <span class="rounded-full px-3 py-0.5 text-xs font-extrabold {{ $alert->level->badgeClass() }}">{{ $alert->level->label() }}</span>
                                <span class="rounded-full bg-grey-100 px-3 py-0.5 text-xs font-bold text-grey-700">{{ $alert->source->label() }}</span>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-grey-700">{{ $alert->message }}</p>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs text-grey-400">{{ $alert->location->name }} · {{ $alert->created_at->diffForHumans() }}</p>
                            <div class="flex gap-2">
                                @unless ($alert->isRead())
                                    <form method="POST" action="{{ route('alerts.read', $alert) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-full border border-blue-100 px-3 py-1 text-xs font-bold text-blue-600 hover:bg-blue-100">Tandai dibaca</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('alerts.destroy', $alert) }}" data-confirm="Hapus notifikasi ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            {{ $alerts->links() }}
        @endif
    </section>
@endsection
