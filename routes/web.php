<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FishController as AdminFishController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatchLogController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FishController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute web MitraPesisir
|--------------------------------------------------------------------------
| Publik (tamu boleh membaca) · Anggota (login) · User/Admin · Admin.
*/

// ---- Tamu: Masuk / Daftar -------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'show'])->name('auth');
    Route::get('/masuk', fn () => redirect()->route('auth', ['tab' => 'login']));
    Route::get('/daftar', fn () => redirect()->route('auth', ['tab' => 'register']));
    Route::post('/masuk', [AuthController::class, 'login'])->name('login');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register');
});

// ---- Publik (mode tamu = hanya baca) --------------------------------------
Route::get('/beranda', [DashboardController::class, 'index'])->name('beranda');
Route::get('/prediksi', [PredictionController::class, 'index'])->name('prediksi');
Route::get('/ikan', [FishController::class, 'index'])->name('ikan.index');
Route::get('/komunitas', [CommunityController::class, 'index'])->name('komunitas.index');
Route::get('/pengaturan', [SettingsController::class, 'index'])->name('pengaturan.index');
Route::put('/pengaturan/lokasi', [SettingsController::class, 'updateLocation'])->name('pengaturan.lokasi');

// ---- Anggota (wajib login) -------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

    // Pengaturan akun
    Route::patch('/pengaturan/profil', [SettingsController::class, 'updateProfile'])->name('pengaturan.profil');
    Route::patch('/pengaturan/kata-sandi', [SettingsController::class, 'updatePassword'])->name('pengaturan.password');
    Route::patch('/pengaturan/preferensi', [SettingsController::class, 'updatePreferences'])->name('pengaturan.preferensi');

    // Riwayat tangkapan
    Route::post('/ikan/tangkapan', [CatchLogController::class, 'store'])->name('tangkapan.store');
    Route::delete('/ikan/tangkapan/{catchLog}', [CatchLogController::class, 'destroy'])->whereNumber('catchLog')->name('tangkapan.destroy');

    // Komunitas
    Route::post('/komunitas', [CommunityController::class, 'store'])->name('komunitas.store');
    Route::post('/komunitas/{post}/balas', [CommunityController::class, 'reply'])->whereNumber('post')->name('komunitas.reply');
    Route::delete('/komunitas/{post}', [CommunityController::class, 'destroy'])->whereNumber('post')->name('komunitas.destroy');

    // Notifikasi
    Route::get('/notifikasi', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/notifikasi/baca-semua', [AlertController::class, 'readAll'])->name('alerts.readAll');
    Route::patch('/notifikasi/{alert}/baca', [AlertController::class, 'read'])->whereNumber('alert')->name('alerts.read');
    Route::delete('/notifikasi/{alert}', [AlertController::class, 'destroy'])->whereNumber('alert')->name('alerts.destroy');
});

// ---- Laporan: warga hanya mengirim; daftar dan detail hanya untuk admin. ---
Route::middleware(['auth', 'role:ADMIN,USER'])->group(function () {
    Route::get('/laporan/buat', [ReportController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [ReportController::class, 'store'])->name('laporan.store');
});
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/{report}/ubah', [ReportController::class, 'edit'])->whereNumber('report')->name('laporan.edit');
    Route::put('/laporan/{report}', [ReportController::class, 'update'])->whereNumber('report')->name('laporan.update');
    Route::delete('/laporan/{report}', [ReportController::class, 'destroy'])->whereNumber('report')->name('laporan.destroy');
    Route::get('/laporan/{report}', [ReportController::class, 'show'])->whereNumber('report')->name('laporan.show');
});

// ---- Admin -----------------------------------------------------------------
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/bagikan-info', [AdminDashboardController::class, 'shareInformation'])->name('admin.share-information');

    Route::patch('/laporan/{report}/status', [ReportController::class, 'updateStatus'])->whereNumber('report')->name('admin.laporan.status');

    Route::get('/pengguna', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('/pengguna/{user}', [AdminUserController::class, 'update'])->whereNumber('user')->name('admin.users.update');
    Route::delete('/pengguna/{user}', [AdminUserController::class, 'destroy'])->whereNumber('user')->name('admin.users.destroy');

    Route::get('/ikan', [AdminFishController::class, 'index'])->name('admin.fish.index');
    Route::get('/ikan/tambah', [AdminFishController::class, 'create'])->name('admin.fish.create');
    Route::post('/ikan', [AdminFishController::class, 'store'])->name('admin.fish.store');
    Route::get('/ikan/{fish}/ubah', [AdminFishController::class, 'edit'])->whereNumber('fish')->name('admin.fish.edit');
    Route::put('/ikan/{fish}', [AdminFishController::class, 'update'])->whereNumber('fish')->name('admin.fish.update');
    Route::delete('/ikan/{fish}', [AdminFishController::class, 'destroy'])->whereNumber('fish')->name('admin.fish.destroy');
});
