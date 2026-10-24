<?php

use Illuminate\Support\Facades\Schedule;

// Periksa kondisi air laut tiap 10 menit dan buat peringatan bila Waspada/Bahaya.
// Aktifkan scheduler: * * * * * php /path/ke/proyek/artisan schedule:run >> /dev/null 2>&1
Schedule::command('pesisir:check-tide')->everyTenMinutes()->withoutOverlapping();
Schedule::command('pesisir:check-extreme-weather')->everyTenMinutes()->withoutOverlapping();
