<?php

namespace Database\Seeders;

use App\Models\CatchLog;
use App\Models\CommunityPost;
use App\Models\Location;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Contoh riwayat tangkapan, papan komunitas, dan laporan agar tampilan tidak kosong. */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $nelayan = User::where('whatsapp_number', '082336478234')->first();
        if ($nelayan === null) {
            return;
        }
        $nelayan->loadMissing('location');

        CatchLog::firstOrCreate(
            ['user_id' => $nelayan->id, 'fish_name' => 'Tenggiri', 'quantity' => '5 kg'],
            [
                'location_id' => $nelayan->location_id,
                'region' => $nelayan->location?->region->value,
                'note' => 'Didapat dari tengah laut, air sedang tinggi dan ombak besar',
            ],
        );
        CatchLog::firstOrCreate(
            ['user_id' => $nelayan->id, 'fish_name' => 'Selar', 'quantity' => '12 ekor'],
            ['location_id' => $nelayan->location_id, 'region' => $nelayan->location?->region->value],
        );

        $posts = [
            ['Pak Yudistira', 'Kenjeran, Surabaya', 'Pagi ini air surut lebih cepat dari biasanya, kapal kecil sebaiknya berangkat sebelum jam 6.'],
            ['Pak Jeki', 'Pelabuhan Ratu, Sukabumi', 'Alhamdulillah tangkapan lumayan pas air mulai pasang tadi malam.'],
            ['Bu Yuli', 'Muara Angke, Jakarta', 'Ombak cukup tinggi di sekitar tanjung, nelayan perahu kecil harap hati-hati.'],
            ['Kelompok Nelayan Kuta', 'Kuta, Bali', 'Jadwal melaut bersama besok pagi disesuaikan dengan surut terendah pukul 05.30.'],
        ];
        foreach ($posts as [$name, $area, $message]) {
            CommunityPost::firstOrCreate(['name' => $name, 'area' => $area, 'message' => $message]);
        }

        $reports = [
            ['Kenjeran, Surabaya', 'Air pasang melewati dermaga kecil', 'KONDISI_AIR', 'SEDANG', 'DIPROSES',
                'Sejak pagi air pasang naik hingga menutup dermaga kayu di sisi timur. Perahu kecil kesulitan sandar dan tambatan perlu diperkuat.'],
            ['Muara Angke, Jakarta', 'Sampah plastik menumpuk di muara', 'PENCEMARAN', 'TINGGI', 'BARU',
                'Tumpukan sampah plastik terbawa arus dan menutup alur keluar masuk perahu. Mohon bantuan pembersihan bersama.'],
            ['Pelabuhan Ratu, Sukabumi', 'Abrasi mengikis tepi pantai warga', 'ABRASI', 'TINGGI', 'DIPROSES',
                'Garis pantai mundur sekitar dua meter sejak bulan lalu, beberapa warung di tepi pantai terancam roboh.'],
            ['Padang, Sumatra Barat', 'Angin kencang sore hari', 'CUACA_EKSTREM', 'RENDAH', 'SELESAI',
                'Angin kencang berlangsung dua jam di sore hari dan sudah mereda. Tidak ada kerusakan perahu yang dilaporkan.'],
        ];
        foreach ($reports as [$locationName, $title, $category, $severity, $status, $description]) {
            $locationId = Location::where('name', $locationName)->value('id');
            if ($locationId === null) {
                continue;
            }
            Report::firstOrCreate(
                ['location_id' => $locationId, 'title' => $title],
                ['user_id' => $nelayan->id, 'category' => $category, 'severity' => $severity, 'status' => $status, 'description' => $description],
            );
        }
    }
}
