<?php

namespace App\Http\Controllers;

use App\Enums\Region;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Location;
use App\Support\CurrentLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Pengaturan: akun, lokasi favorit, notifikasi, mode offline, kata sandi. */
class SettingsController extends Controller
{
    public function __construct(private readonly CurrentLocation $current)
    {
    }

    public function index(Request $request): View
    {
        $q = $this->textFilter($request->query('q'));
        $region = $this->enumFilter($request->query('wilayah'), Region::class);

        return view('pengaturan.index', [
            'currentLocation' => $this->current->get(),
            'locations' => Location::query()->search($q, $region)->orderBy('id')->get(),
            'regions' => Region::cases(),
            'q' => $q,
            'region' => $region,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('pengaturan.index')->with('success', 'Data akun berhasil disimpan.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return redirect()->route('pengaturan.index')->with('success', 'Kata sandi berhasil diganti.');
    }

    /** Toggle Notifikasi Kondisi / Mode Offline (satu atau dua kolom boolean). */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notif_kondisi' => ['sometimes', 'boolean'],
            'whatsapp_opt_in' => ['sometimes', 'boolean'],
            'mode_offline' => ['sometimes', 'boolean'],
        ]);
        if ($data === []) {
            return redirect()->route('pengaturan.index')->with('warning', 'Tidak ada pengaturan yang diubah.');
        }
        $request->user()->update($data);

        return redirect()->route('pengaturan.index')->with('success', 'Pengaturan disimpan.');
    }

    /** Pilih lokasi aktif: tersimpan di akun (login) atau di session (tamu). */
    public function updateLocation(Request $request): RedirectResponse
    {
        $data = $request->validate(
            ['location_id' => ['required', 'integer', 'exists:locations,id']],
            ['location_id.required' => 'Pilih lokasi.', 'location_id.exists' => 'Lokasi tidak ditemukan.'],
        );

        if ($request->user()) {
            $request->user()->update(['location_id' => $data['location_id']]);
        } else {
            $request->session()->put('location_id', (int) $data['location_id']);
        }
        $this->current->forget();

        return redirect()->route('beranda')->with('success', 'Lokasi pesisir diganti ke '.$this->current->get()->name.'.');
    }
}
