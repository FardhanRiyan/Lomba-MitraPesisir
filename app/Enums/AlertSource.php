<?php

namespace App\Enums;

enum AlertSource: String
{
    case KONDISI = 'KONDISI'; //dihasilkan dari status air laut
    case LAPORAN = 'LAPORAN'; //notifikasi laporan lama

    public function label() 
    {
        return match ($this){
            self::KONDISI => 'Kondisi air laut',
            self::LAPORAN => 'Laporan Pesisir',
        };
    }
}