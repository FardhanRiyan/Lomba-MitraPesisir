<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Akun demo. GANTI kata sandi (atau hapus akun) sebelum dipakai di produksi. */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $kenjeran = Location::where('name', 'Kenjeran, Surabaya')->value('id');
        $muara = Location::where('name', 'Muara Angke, Jakarta')->value('id');

        // firstOrCreate: tidak menimpa akun yang sudah ada / sudah diubah.
        User::firstOrCreate(['whatsapp_number' => '081200000001'], [
            'name' => 'Admin MitraPesisir',
            'password' => 'admin12345',
            'role' => 'ADMIN',
            'location_id' => $kenjeran,
        ]);
        User::firstOrCreate(['whatsapp_number' => '082336478234'], [
            'name' => 'Yudistira',
            'password' => 'pesisir123',
            'role' => 'USER',
            'location_id' => $kenjeran,
        ]);
        User::firstOrCreate(['whatsapp_number' => '081234567890'], [
            'name' => 'Siti User',
            'password' => 'pesisir123',
            'role' => 'USER',
            'location_id' => $muara,
        ]);
    }
}
