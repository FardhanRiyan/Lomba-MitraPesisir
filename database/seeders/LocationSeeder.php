<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public const LOCATIONS = [
        ['name' => 'Kenjeran, Surabaya', 'region' => 'JAWA'],
        ['name' => 'Tanjung Perak, Surabaya', 'region' => 'JAWA'],
        ['name' => 'Muara Angke, Jakarta', 'region' => 'JAWA'],
        ['name' => 'Pelabuhan Ratu, Sukabumi', 'region' => 'JAWA'],
        ['name' => 'Padang, Sumatra Barat', 'region' => 'SUMATRA'],
        ['name' => 'Balikpapan, Kalimantan Timur', 'region' => 'KALIMANTAN'],
        ['name' => 'Bitung, Sulawesi Utara', 'region' => 'SULAWESI'],
        ['name' => 'Kupang, NTT', 'region' => 'NTT'],
    ];

    public function run(): void
    {
        foreach (self::LOCATIONS as $row) {
            Location::updateOrCreate(['name' => $row['name']], ['region' => $row['region']]);
        }
    }
}
