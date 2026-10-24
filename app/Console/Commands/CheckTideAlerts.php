<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckTideAlerts extends Command
{
    protected $signature = 'pesisir:check-tide';

    protected $description = 'Periksa kondisi air laut tiap lokasi dan buat peringatan untuk pengguna terkait';

    public function handle(AlertService $alerts): int
    {
        $created = $alerts->syncAll();
        $this->info("Selesai. {$created} peringatan baru dibuat.");

        return self::SUCCESS;
    }
}
