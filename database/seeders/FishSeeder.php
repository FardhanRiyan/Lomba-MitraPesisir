<?php

namespace Database\Seeders;

use App\Models\Fish;
use Illuminate\Database\Seeder;

class FishSeeder extends Seeder
{
    public const FISH = [
        // Jawa
        ['Tenggiri', 'Banyak ditangkap sepanjang tahun di perairan pantai utara dan selatan Jawa.', 'UMUM', 'JAWA'],
        ['Pelagis', 'Sering muncul di dekat rumpon, terutama saat air bergerak dari pasang menuju surut.', 'UMUM', 'JAWA'],
        ['Asin', 'Biasa dijumpai di perairan agak dalam dekat muara sungai.', 'CUKUP_UMUM', 'JAWA'],
        ['Layang', 'Lebih sering didapat dengan jaring dasar di perairan yang lebih dalam.', 'JARANG', 'JAWA'],
        ['Selar', 'Peka terhadap kebisingan mesin dan perubahan cahaya, sehingga mereka cepat berpencar dari pancing.', 'SANGAT_JARANG', 'JAWA'],
        // Sumatra
        ['Kembung', 'Ikan kembung banyak dijumpai di perairan pesisir barat Sumatra sepanjang musim.', 'UMUM', 'SUMATRA'],
        ['Bawal Hitam', 'Cukup sering ditemukan di perairan agak dalam dekat muara.', 'CUKUP_UMUM', 'SUMATRA'],
        ['Kerapu Karang', 'Jarang muncul, biasanya bersembunyi di celah terumbu karang.', 'JARANG', 'SUMATRA'],
        // Kalimantan
        ['Kakap Merah', 'Banyak ditangkap nelayan di perairan pesisir timur Kalimantan.', 'UMUM', 'KALIMANTAN'],
        ['Tenggiri Papan', 'Cukup umum dijumpai saat air mulai surut.', 'CUKUP_UMUM', 'KALIMANTAN'],
        ['Pari Kembang', 'Jarang terlihat, biasanya di dasar perairan berlumpur.', 'JARANG', 'KALIMANTAN'],
        // Sulawesi
        ['Cakalang', 'Salah satu hasil tangkapan utama nelayan Sulawesi Utara.', 'UMUM', 'SULAWESI'],
        ['Tuna Sirip Kuning', 'Cukup umum ditemukan di perairan agak jauh dari pantai.', 'CUKUP_UMUM', 'SULAWESI'],
        ['Napoleon', 'Sangat jarang, populasinya dilindungi dan dibatasi penangkapannya.', 'SANGAT_JARANG', 'SULAWESI'],
        // NTT
        ['Kerapu Karang', 'Umum dijumpai di sekitar terumbu karang perairan NTT.', 'UMUM', 'NTT'],
        ['Ikan Terbang', 'Cukup umum, sering terlihat melompat di permukaan air.', 'CUKUP_UMUM', 'NTT'],
        ['Tenggiri Papan', 'Jarang didapat, biasanya hanya saat musim tertentu.', 'JARANG', 'NTT'],
    ];

    public function run(): void
    {
        foreach (self::FISH as [$name, $description, $rarity, $region]) {
            Fish::updateOrCreate(
                ['name' => $name, 'region' => $region],
                ['description' => $description, 'rarity' => $rarity],
            );
        }
    }
}
