@extends('layouts.app')

@section('heading', 'Panel admin')
@section('subtitle', 'Ringkasan statistik aplikasi, sebaran laporan, dan akses cepat ke pengelolaan pengguna serta katalog ikan.')

@section('content')
    <section class="glass-card mb-5 p-6">
        <h2 class="font-serif text-xl font-bold">Bagikan info pasang surut</h2>
        <p class="mt-1 text-sm text-grey-600">
            Tulis informasi, pilih akun penerima, lalu kirim sebagai notifikasi di aplikasi. Pesan tidak dikirim melalui WhatsApp.
        </p>
        <button type="button" data-modal-open="admin-share-modal" @disabled($shareUsers->isEmpty())
                class="btn-primary mt-4 disabled:cursor-not-allowed disabled:opacity-50">
            Bagikan ke user
        </button>
        @if ($shareUsers->isEmpty())
            <p class="mt-2 text-sm text-grey-600">Belum ada akun user yang dapat menerima informasi.</p>
        @endif
    </section>

    <dialog id="admin-share-modal" class="modal admin-share-modal" aria-labelledby="admin-share-heading"
            @if ($errors->hasAny(['title', 'message', 'user_ids', 'user_ids.*', 'recipient_mode'])) data-open-on-load @endif>
        <div class="flex h-full flex-col">
            <header class="flex items-start justify-between gap-4 border-b border-grey-100 px-6 py-5">
                <div>
                    <h2 id="admin-share-heading" class="font-serif text-xl font-bold">Bagikan informasi ke user</h2>
                    <p class="mt-1 text-sm text-grey-600">Pesan akan masuk ke notifikasi aplikasi penerima.</p>
                </div>
                <button type="button" data-modal-close aria-label="Tutup" class="rounded-full px-3 py-1 text-2xl leading-none text-grey-600 hover:bg-grey-100">&times;</button>
            </header>

            <form id="admin-share-form" method="POST" action="{{ route('admin.share-information') }}"
                  class="flex min-h-0 flex-1 flex-col"
                  data-confirm="Kirim informasi kepada penerima yang dipilih?">
                @csrf
                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                    <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)]">
                        <div>
                            <label for="share-title" class="block text-sm font-extrabold">Judul informasi</label>
                            <input id="share-title" name="title" value="{{ old('title', $shareTitle) }}" maxlength="150" required
                                   class="field-input mt-1.5 @error('title') has-error @enderror">
                            @error('title') <p class="mt-1 text-xs font-bold text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="share-message" class="block text-sm font-extrabold">Isi informasi</label>
                            <textarea id="share-message" name="message" rows="3" maxlength="2000" required
                                      class="field-input mt-1.5 @error('message') has-error @enderror">{{ old('message', $shareMessage) }}</textarea>
                            @error('message') <p class="mt-1 text-xs font-bold text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-extrabold">Pilih penerima ({{ $shareUsers->count() }} user)</h3>
                                <p class="text-xs text-grey-600">Penerima dapat membaca dan menghapus informasi dari menu Notifikasi.</p>
                            </div>
                            <span class="text-xs font-bold text-blue-600"><span data-selected-count>0</span> dipilih</span>
                        </div>

                        @error('user_ids')
                            <p class="mt-2 text-sm font-bold text-danger">{{ $message }}</p>
                        @enderror
                        @error('user_ids.*')
                            <p class="mt-2 text-sm font-bold text-danger">{{ $message }}</p>
                        @enderror

                        @if ($shareUsers->isNotEmpty())
                            <div class="mt-3">
                                <label for="share-user-search" class="sr-only">Cari user</label>
                                <input id="share-user-search" type="search" data-recipient-search placeholder="Cari nama atau nomor WhatsApp..."
                                       class="field-input">
                                <label class="mt-3 inline-flex cursor-pointer items-center gap-2 text-sm font-bold">
                                    <input type="checkbox" data-select-all-checkbox class="h-4 w-4 accent-blue-600">
                                    Pilih semua user
                                </label>
                            </div>
                            <div class="mt-3 max-h-[34vh] overflow-y-auto rounded-xl border border-grey-100" data-recipient-list>
                                @foreach ($shareUsers as $shareUser)
                                    <label class="flex cursor-pointer items-center gap-3 border-b border-grey-100 px-4 py-3 last:border-b-0 hover:bg-blue-100/40"
                                           data-recipient-option data-search-text="{{ mb_strtolower($shareUser->name.' '.$shareUser->whatsapp_number) }}">
                                        <input type="checkbox" name="user_ids[]" value="{{ $shareUser->id }}"
                                               @checked(in_array($shareUser->id, old('user_ids', [])))
                                               class="h-4 w-4 accent-blue-600" data-recipient-checkbox>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-bold">{{ $shareUser->name }}</span>
                                            <span class="block text-xs text-grey-600">{{ $shareUser->whatsapp_number }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-3 rounded-md bg-sky-50 p-4 text-sm text-grey-600">Belum ada akun user untuk dipilih.</p>
                        @endif
                    </div>
                </div>

                <footer class="flex flex-wrap justify-end gap-2 border-t border-grey-100 px-6 py-4">
                    <button type="button" data-modal-close class="btn-ghost">Batal</button>
                    <button type="submit" name="recipient_mode" value="selected" @disabled($shareUsers->isEmpty())
                            data-confirm="Kirim informasi ke user yang dipilih?"
                            class="btn-primary disabled:cursor-not-allowed disabled:opacity-50">
                        Kirim ke user terpilih
                    </button>
                    <button type="submit" name="recipient_mode" value="all" data-send-all @disabled($shareUsers->isEmpty())
                            data-confirm="Kirim informasi ini ke semua user ({{ $shareUsers->count() }})?"
                            class="btn-primary bg-teal-600 hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Kirim ke semua user ({{ $shareUsers->count() }})
                    </button>
                </footer>
            </form>
        </div>
    </dialog>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-6">
        @foreach ($totals as $label => $value)
            <div class="glass-card p-4 text-center">
                <p class="font-mono text-3xl font-bold">{{ number_format($value, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs font-bold text-grey-600">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        @foreach ([['Laporan per status', $statusRows], ['Laporan per kategori', $categoryRows], ['Pengguna per peran', $roleRows]] as [$title, $rows])
            @php $max = max(1, $rows->max('total')); @endphp
            <section class="glass-card p-6">
                <h2 class="font-serif text-lg font-bold">{{ $title }}</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($rows as $row)
                        <li>
                            <div class="flex justify-between text-xs font-bold"><span>{{ $row['label'] }}</span><span class="font-mono">{{ $row['total'] }}</span></div>
                            <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-grey-100">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ round($row['total'] / $max * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        <section class="glass-card p-6">
            <h2 class="font-serif text-lg font-bold">Lokasi dengan laporan terbanyak</h2>
            @if ($topLocations->isEmpty())
                <p class="mt-3 rounded-md bg-sky-50 p-4 text-sm text-grey-600">Belum ada laporan.</p>
            @else
                <ol class="mt-3 space-y-2">
                    @foreach ($topLocations as $row)
                        <li class="flex items-center justify-between rounded-md border border-grey-100 bg-white px-4 py-2.5 text-sm">
                            <span class="font-bold">{{ $row->location?->name ?? '-' }}</span>
                            <span class="font-mono text-xs text-grey-600">{{ $row->total }} laporan</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <section class="glass-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-lg font-bold">Laporan terbaru</h2>
                <a href="{{ route('laporan.index') }}" class="text-xs font-bold text-blue-600">Lihat semua</a>
            </div>
            @if ($latestReports->isEmpty())
                <p class="mt-3 rounded-md bg-sky-50 p-4 text-sm text-grey-600">Belum ada laporan.</p>
            @else
                <ul class="mt-3 space-y-2">
                    @foreach ($latestReports as $r)
                        <li>
                            <a href="{{ route('laporan.show', $r) }}" class="block rounded-md border border-grey-100 bg-white px-4 py-2.5 hover:border-blue-500">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-sm font-bold">{{ $r->title }}</span>
                                    <span class="flex-none rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $r->status->badgeClass() }}">{{ $r->status->label() }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-grey-400">{{ $r->location->name }} · {{ $r->created_at->diffForHumans() }}</p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
