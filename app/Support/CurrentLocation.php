<?php

namespace App\Support;

use App\Models\Location;
use Illuminate\Support\Facades\Auth;

/**
 * Lokasi pesisir aktif untuk request ini.
 * Pengguna login -> lokasi di akunnya; tamu -> lokasi yang disimpan di session.
 * Bila belum ada pilihan, dipakai lokasi pertama (id terkecil).
 */
final class CurrentLocation
{
    private ?Location $resolved = null;

    public function get(): ?Location
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $id = Auth::user()?->location_id ?? session('location_id');
        $location = $id ? Location::find($id) : null;

        return $this->resolved = $location ?? Location::query()->orderBy('id')->first();
    }

    public function forget(): void
    {
        $this->resolved = null;
    }
}
