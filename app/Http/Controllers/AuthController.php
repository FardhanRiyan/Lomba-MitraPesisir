<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Autentikasi: halaman Masuk/Daftar, proses login, register, dan logout. */
class AuthController extends Controller
{
    public function show(Request $request): View
    {
        $tab = old('_tab', $request->query('tab'));

        return view('auth.index', [
            'tab' => $tab === 'register' ? 'register' : 'login',
            'locations' => Location::query()->orderBy('id')->get(),
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('beranda'))
            ->with('success', 'Selamat datang kembali, '.$request->user()->name.'.');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'whatsapp_number' => $data['whatsapp_number'],
            'password' => $data['password'],
            'role' => UserRole::USER->value,
            'location_id' => $data['location_id'],
            'whatsapp_opt_in' => $data['whatsapp_opt_in'] ?? false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('beranda')->with('success', 'Akun berhasil dibuat. Selamat datang di MitraPesisir!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth')->with('success', 'Anda sudah keluar.');
    }
}
