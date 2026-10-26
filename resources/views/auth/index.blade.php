@extends('layouts.auth')

@section('heading', $tab === 'register' ? 'Daftar' : 'Masuk')

@section('content')
    <header class="text-center md:text-left">
        <p class="hero-text flex items-center justify-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-100 md:justify-start">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-white ring-2 ring-blue-500" aria-hidden="true"></span>
            Pantau pasang surut
        </p>
        <h1 class="hero-text mt-2 font-serif text-4xl font-extrabold md:text-5xl">MitraPesisir</h1>
        <p class="hero-text mt-3 text-sm leading-relaxed md:text-base">
            Pusat informasi lengkap untuk memantau kondisi air laut, perkiraan cuaca harian, dan kabar terbaru wilayah
            pesisir secara real-time langsung dari genggamanmu.
        </p>
    </header>

    <nav class="mt-8 grid grid-cols-2 gap-3" aria-label="Masuk atau daftar">
        <a href="{{ route('auth', ['tab' => 'login']) }}" @if ($tab === 'login') aria-current="page" @endif
           class="rounded-full py-3 text-center text-base font-extrabold shadow-card {{ $tab === 'login' ? 'bg-navy-900 text-white' : 'bg-white text-grey-400' }}">Masuk</a>
        <a href="{{ route('auth', ['tab' => 'register']) }}" @if ($tab === 'register') aria-current="page" @endif
           class="rounded-full py-3 text-center text-base font-extrabold shadow-card {{ $tab === 'register' ? 'bg-navy-900 text-white' : 'bg-white text-grey-400' }}">Daftar</a>
    </nav>

    <section class="glass-card mt-5 p-6">
        @if ($tab === 'login')
            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <input type="hidden" name="_tab" value="login">

                <div class="mt-1">
                    <label for="login-wa" class="block text-sm font-semibold text-navy-900">Nomor WhatsApp</label>
                    <input id="login-wa" type="tel" inputmode="numeric" autocomplete="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="0867-6767-6767"
                           class="field-input mt-1.5 @error('whatsapp_number') has-error @enderror">
                    @include('partials.field-error', ['name' => 'whatsapp_number'])
                </div>

                <div class="mt-4">
                    <label for="login-pw" class="block text-sm font-semibold text-navy-900">Kata sandi</label>
                    <div class="relative mt-1.5">
                        <input id="login-pw" type="password" autocomplete="current-password" name="password" placeholder="Masukkan kata sandi"
                               class="field-input pr-16 @error('password') has-error @enderror">
                        <button type="button" data-toggle-password="login-pw" aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-blue-600">Lihat</button>
                    </div>
                    @include('partials.field-error', ['name' => 'password'])
                </div>

                <div class="mt-4 flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 font-semibold text-navy-900">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 accent-blue-600">
                        Ingat saya
                    </label>
                    <button type="button" data-modal-open="forgot-modal" class="font-bold text-blue-600">Lupa kata sandi?</button>
                </div>

                <button type="submit" class="btn-primary mt-6 w-full">Masuk →</button>
            </form>

            <div class="my-5 text-center text-xs font-bold text-grey-400">ATAU</div>
            <a href="{{ route('beranda') }}" class="btn-ghost w-full">Jelajahi sebagai tamu</a>
            <p class="mt-5 text-center text-sm text-grey-600">
                Belum punya akun? <a href="{{ route('auth', ['tab' => 'register']) }}" class="font-bold text-blue-600">Daftar di sini</a>
            </p>
        @else
            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf
                <input type="hidden" name="_tab" value="register">

                <div class="mt-1">
                    <label for="reg-name" class="block text-sm font-semibold text-navy-900">Nama lengkap</label>
                    <input id="reg-name" type="text" autocomplete="name" name="name" value="{{ old('name') }}" placeholder="cth. Ujang"
                           class="field-input mt-1.5 @error('name') has-error @enderror">
                    @include('partials.field-error', ['name' => 'name'])
                </div>

                <div class="mt-4">
                    <label for="reg-wa" class="block text-sm font-semibold text-navy-900">Nomor WhatsApp</label>
                    <input id="reg-wa" type="tel" inputmode="numeric" autocomplete="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="0867 6767 6767"
                           class="field-input mt-1.5 @error('whatsapp_number') has-error @enderror">
                    @include('partials.field-error', ['name' => 'whatsapp_number'])
                </div>

                <div class="mt-4">
                    <label for="reg-loc" class="block text-sm font-semibold text-navy-900">Lokasi desa pesisir</label>
                    <select id="reg-loc" name="location_id" class="field-input mt-1.5 @error('location_id') has-error @enderror">
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" @selected((int) old('location_id', $locations->first()?->id) === $loc->id)>{{ $loc->name }} — {{ $loc->region->label() }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'location_id'])
                </div>

                <label class="mt-4 flex items-start gap-2 text-sm text-grey-700">
                    <input type="checkbox" name="whatsapp_opt_in" value="1" @checked(old('whatsapp_opt_in')) class="mt-1 h-4 w-4 accent-blue-600">
                    <span>Saya menyimpan persetujuan WhatsApp sebagai preferensi akun. Informasi yang dibagikan admin saat ini dikirim melalui notifikasi aplikasi.</span>
                </label>

                <div class="mt-4">
                    <label for="reg-pw" class="block text-sm font-semibold text-navy-900">Kata sandi</label>
                    <div class="relative mt-1.5">
                        <input id="reg-pw" type="password" autocomplete="new-password" name="password" placeholder="Minimal 6 karakter"
                               class="field-input pr-16 @error('password') has-error @enderror">
                        <button type="button" data-toggle-password="reg-pw" aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-blue-600">Lihat</button>
                    </div>
                    @include('partials.field-error', ['name' => 'password'])
                </div>

                <div class="mt-4">
                    <label for="reg-pw2" class="block text-sm font-semibold text-navy-900">Konfirmasi kata sandi</label>
                    <div class="relative mt-1.5">
                        <input id="reg-pw2" type="password" autocomplete="new-password" name="password_confirmation" placeholder="Ulangi kata sandi" class="field-input pr-16">
                        <button type="button" data-toggle-password="reg-pw2" aria-label="Tampilkan konfirmasi kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-blue-600">Lihat</button>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex items-start gap-2 text-xs font-semibold text-navy-900">
                        <input type="checkbox" name="agree" value="1" @checked(old('agree')) class="mt-0.5 h-4 w-4 accent-blue-600">
                        <span>
                            Saya menyetujui
                            <button type="button" data-modal-open="terms-modal" class="font-bold text-blue-600 underline">Syarat &amp; Ketentuan</button>
                            dan
                            <button type="button" data-modal-open="privacy-modal" class="font-bold text-blue-600 underline">Kebijakan Privasi</button>
                            MitraPesisir.
                        </span>
                    </label>
                    @include('partials.field-error', ['name' => 'agree'])
                </div>

                <button type="submit" class="btn-primary mt-6 w-full">Buat akun</button>
            </form>

            <p class="mt-5 text-center text-sm text-grey-600">
                Sudah punya akun? <a href="{{ route('auth', ['tab' => 'login']) }}" class="font-bold text-blue-600">Masuk di sini</a>
            </p>
        @endif
    </section>

    {{-- Modal informasi (elemen <dialog>, dibuka oleh public/js/app.js) --}}
    <dialog id="forgot-modal" class="modal">
        <h2 class="font-serif text-xl font-bold">Lupa kata sandi?</h2>
        <p class="mt-3 text-sm leading-relaxed text-navy-800">
            Pemulihan kata sandi mandiri belum tersedia. Hubungi admin MitraPesisir melalui WhatsApp agar kata sandi Anda dapat diatur ulang.
            Setelah masuk, Anda bisa menggantinya di menu Setelan.
        </p>
        <button type="button" data-modal-close class="btn-primary mt-5 w-full">Mengerti</button>
    </dialog>
    <dialog id="terms-modal" class="modal">
        <h2 class="font-serif text-xl font-bold">Syarat &amp; Ketentuan</h2>
        <div class="mt-3 space-y-3 text-sm leading-relaxed text-navy-800">
            <p>MitraPesisir adalah aplikasi informasi komunitas pesisir untuk kompetisi FITCOM 4.0.</p>
            <p>Angka pasang surut, cuaca, dan gelombang dihasilkan dari rumus simulasi matematis, bukan pengukuran laut yang sesungguhnya. Jangan dijadikan satu-satunya dasar keputusan melaut.</p>
            <p>Konten yang Anda kirim (laporan dan papan komunitas) harus sopan, benar, dan tidak menyesatkan.</p>
        </div>
        <button type="button" data-modal-close class="btn-primary mt-5 w-full">Mengerti</button>
    </dialog>
    <dialog id="privacy-modal" class="modal">
        <h2 class="font-serif text-xl font-bold">Kebijakan Privasi</h2>
        <div class="mt-3 space-y-3 text-sm leading-relaxed text-navy-800">
            <p>Kami menyimpan nama, nomor WhatsApp, lokasi pilihan, dan preferensi Anda untuk menjalankan fitur aplikasi.</p>
            <p>Kata sandi disimpan dalam bentuk hash satu arah, tidak pernah dalam bentuk asli.</p>
            <p>Nama dan daerah pada pesan komunitas tampil publik, sedangkan laporan pesisir hanya dapat dilihat admin. Nomor WhatsApp Anda tidak ditampilkan ke pengguna lain. Informasi yang dibagikan admin dikirim melalui notifikasi aplikasi.</p>
        </div>
        <button type="button" data-modal-close class="btn-primary mt-5 w-full">Mengerti</button>
    </dialog>
@endsection
