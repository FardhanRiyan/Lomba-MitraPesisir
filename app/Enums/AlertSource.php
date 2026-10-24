<?php

namespace App\Enums;

enum AlertSource: string
{
    case KONDISI = 'KONDISI'; // dihasilkan dari status air laut
    case LAPORAN = 'LAPORAN'; // notifikasi laporan lama

    public function label(): string
    {
        return match ($this) {
            self::KONDISI => 'Kondisi air laut',
            self::LAPORAN => 'Laporan pesisir',
        };
    }
}
