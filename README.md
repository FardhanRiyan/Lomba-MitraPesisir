# MitraPesisir — Pusat Data Komunitas & Kondisi Laut (Laravel MVC)

Aplikasi web **FITCOM 4.0 (Intelligent Water & Marine)** berbasis **Laravel 12 (PHP 8.2+) · Blade · Tailwind CSS · MySQL 8**, disusun dengan pola **Model–View–Controller** dan mengikuti desain Figma MitraPesisir.

## Fitur

| Modul | Isi | Controller |
|---|---|---|
| Autentikasi | Masuk, Daftar (akun User otomatis), "Ingat saya", batas 5 percobaan gagal, Keluar, mode tamu (hanya baca) | `AuthController` |
| Dashboard & monitoring | Status Aman/Waspada/Bahaya, tinggi air, tren, pasang tertinggi, angin & gelombang, penyegaran otomatis 60 dtk (mati bila Mode Offline) | `DashboardController` |
| Prediksi | Grafik pasang surut 24/48 jam + riwayat 7 hari (SVG dirender server) | `PredictionController` |
| Laporan pesisir | User dapat mengirim laporan; hanya admin dapat melihat, mencari, mengubah status, dan mengelola laporan | `ReportController` |
| Notifikasi / anomali | Peringatan otomatis saat air Waspada/Bahaya; tandai dibaca, hapus | `AlertController`, `AlertService`, `pesisir:check-tide` |
| Ikan | Katalog per wilayah dengan detail lengkap, unggah foto tangkapan yang otomatis mengisi foto katalog, dan filter riwayat berdasarkan wilayah | `FishController`, `CatchLogController` |
| Komunitas | Papan pesan (cari, pagination), kirim pesan dan balasan antar pengguna | `CommunityController` |
| Setelan | Edit akun, ganti kata sandi, pilih lokasi (cari + filter wilayah), toggle Notifikasi Kondisi & Mode Offline | `SettingsController` |
| Admin | Ringkasan statistik, kelola pengguna, edit semua catatan tangkapan termasuk foto, CRUD katalog ikan termasuk moderasi foto unggahan | `Admin\*` |

### RBAC

| Aksi | Tamu | User | Admin |
|---|:-:|:-:|:-:|
| Baca Beranda, Prediksi, Ikan, Komunitas | ✓ | ✓ | ✓ |
| Ganti lokasi (tamu: disimpan di session) | ✓ | ✓ | ✓ |
| Catat tangkapan, kirim/balas pesan, notifikasi, setelan akun | – | ✓ | ✓ |
| Buat laporan (tanpa melihat laporan) | – | ✓ | ✓ |
| Lihat dan kelola semua laporan, ubah status, kelola pengguna & katalog ikan | – | – | ✓ |

Penegakan: middleware `role:` (`app/Http/Middleware/EnsureRole.php`) + Policy (`app/Policies`).

## Struktur proyek (MVC)

```
app/
├── Models/            Location, User, Fish, CatchLog, CommunityPost, Report, Alert   (M)
├── Enums/             Region, UserRole, FishRarity, ReportCategory/Severity/Status, TideLevel, AlertSource
├── Http/
│   ├── Controllers/   Auth, Dashboard, Prediction, Fish, CatchLog, Community, Report, Alert, Settings, Admin/*   (C)
│   ├── Requests/      Validasi server (Login, Register, Report, CatchLog, CommunityPost, Fish, UpdateProfile/Password)
│   └── Middleware/    EnsureRole (RBAC)
├── Policies/          ReportPolicy, CatchLogPolicy, CommunityPostPolicy
├── Services/          TideSimulator (simulasi pasang surut), AlertService (logika peringatan)
├── Support/           ChartBuilder, Format, CurrentLocation
└── Console/Commands/  CheckTideAlerts (pesisir:check-tide)
resources/views/       layouts, partials, auth, beranda, prediksi, ikan, laporan, komunitas, pengaturan, notifikasi, admin, errors   (V)
routes/                web.php, console.php (scheduler)
database/              migrations/ · seeders/ · sql/pesisir_mysql.sql
public/                js/app.js · css/app.css (hasil build Tailwind)
lang/id/               pesan validasi & auth berbahasa Indonesia
tests/Unit/            TideSimulatorTest, FormatTest
```

## Skema database (3NF)

- `locations` (id, name **unik**, region ENUM) 
- `users` (id, name, whatsapp_number **unik**, password, role ENUM[ADMIN,USER], location_id → locations *SET NULL*, notif_kondisi, whatsapp_opt_in, mode_offline)
- `fish` (id, name, description, rarity ENUM, region ENUM, image_url; **unik (name, region)**)
- `catch_logs` (id, user_id → users *CASCADE*, location_id → locations *SET NULL*, region, fish_name, quantity, note, image_path)
- `community_posts` (id, user_id → users *SET NULL*, parent_id → community_posts *CASCADE*, name, area, message)
- `reports` (id, user_id → users *SET NULL*, location_id → locations *RESTRICT*, title, category/severity/status ENUM, description)
- `alerts` (id, user_id → users *CASCADE*, location_id → locations *CASCADE*, source/level ENUM, water_level, title, message, read_at)
- `user_notifications` (id, user_id → users *CASCADE*, title, message, read_at) — informasi yang dibagikan admin kepada user melalui notifikasi aplikasi
- Index: `locations(region)`, `users(role)`, `fish(region)`, `catch_logs(user_id, created_at)`, `catch_logs(region, fish_name)`, `community_posts(parent_id, created_at)`, `reports(status|category|severity|created_at)`, `alerts(user_id, read_at)`, `alerts_dedupe_idx`, `user_notifications(user_id, read_at)`.

## REST API v1

API JSON tersedia dalam namespace `/api/v1` dan mengikuti pola laravel default untuk aplikasi sesion-based (gunakan cookie/session seperti web app). Endpoint utama yang sudah tersedia:

- Auth & profil: `POST /api/v1/login`, `POST /api/v1/register`, `POST /api/v1/logout`, `GET /api/v1/me`, `PATCH /api/v1/profile`, `PATCH /api/v1/password`, `PATCH /api/v1/preferences`
- Komunitas: `GET /api/v1/community/posts`, `POST /api/v1/community/posts`, `POST /api/v1/community/posts/{post}/replies`, `DELETE /api/v1/community/posts/{post}`
- Dashboard & prediksi: `GET /api/v1/dashboard`, `GET /api/v1/predictions?hours=24|48`

Semua response API memakai JSON, dan endpoint yang membutuhkan login akan mengembalikan `401` bila sesi tidak valid.

## Menjalankan secara lokal

**Prasyarat:** PHP ≥ 8.2 (ekstensi `pdo_mysql`, `mbstring`, `intl`, `xml`, `ctype`, `openssl`), Composer 2, MySQL 8 / MariaDB 10.6+, Node.js ≥ 18 (hanya untuk membangun CSS).

```bash
# 1. Pasang dependensi
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
#    lalu edit .env -> DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Buat database kosong (contoh)
mysql -u root -p -e "CREATE DATABASE pesisir_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Migrasi + data awal
php artisan migrate --seed
php artisan storage:link

# 5. Bangun CSS Tailwind  (WAJIB — tanpa ini halaman tampil tanpa gaya)
npm run build              # atau `npm run dev` untuk mode watch

# 6. Jalankan
php artisan serve          # http://localhost:8000
```

Foto ikan yang diunggah disimpan pada disk publik Laravel, sementara path fotonya disimpan di basis data. Pastikan `php artisan storage:link` sudah dijalankan agar foto dapat diakses melalui web. Unggahan anggota melengkapi foto katalog yang belum ada; admin dapat meninjau, mengganti, atau menghapus foto dari menu pengelolaan ikan.

**Peringatan otomatis berkala (opsional):** tambahkan cron `* * * * * php /path/proyek/artisan schedule:run >> /dev/null 2>&1` (menjalankan `pesisir:check-tide` tiap 10 menit), atau jalankan manual `php artisan pesisir:check-tide`. Tanpa cron, peringatan tetap dibuat saat pengguna membuka Beranda.

**Alternatif tanpa artisan migrate:** `mysql -u USER -p pesisir_db < database/sql/pesisir_mysql.sql` (DDL + data awal; jangan dicampur dengan `migrate`).

**Tes unit:** `composer test`

### Akun demo (GANTI/hapus sebelum produksi)

| Peran | WhatsApp | Kata sandi |
|---|---|---|
| Admin | `081200000001` | `admin12345` |
| User | `082336478234` | `pesisir123` |
| User | `081234567890` | `pesisir123` |

Seeder idempoten (`php artisan db:seed` aman diulang; akun yang sudah ada tidak ditimpa).

## Catatan teknis

- **Data laut adalah simulasi** (`app/Services/TideSimulator.php`): dua gelombang sinus (periode ±12,42 jam & ±24,84 jam) dengan fase per lokasi; tidak disimpan di DB dan bukan pengukuran nyata — sesuai teks pada desain Figma. Ambang: `< 1,5 m` Aman · `1,5–1,99 m` Waspada · `≥ 2,0 m` Bahaya.
- **Keamanan:** CSRF di semua form, kata sandi bcrypt, session `httpOnly`+`SameSite=Lax`, validasi server via FormRequest, otorisasi via Policy, batas percobaan login, query terparameter (Eloquent), output Blade ter-escape.
- **Zona waktu:** aplikasi & sesi MySQL memakai WIB (`Asia/Jakarta`, `DB_TIMEZONE=+07:00`).
- **Bagikan informasi admin:** tombol di panel admin membuka pemilih penerima dan mengirim informasi ke notifikasi aplikasi untuk user terpilih atau seluruh user. Fitur ini tidak memerlukan WhatsApp Cloud API.
- **Font** dimuat dari Google Fonts (perlu internet di browser).
- **Produksi:** `APP_ENV=production`, `APP_DEBUG=false`, `php artisan config:cache route:cache view:cache`, arahkan web server ke folder `public/`.

## Belum tersedia
Reset kata sandi mandiri ("Lupa kata sandi?" menampilkan petunjuk menghubungi admin), unggah foto laporan, modul marketplace.
