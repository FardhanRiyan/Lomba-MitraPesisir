<?php

namespace App\Enums;

/** Wilayah / pulau besar — mengelompokkan lokasi pesisir dan data ketersediaan ikan. */
enum Region: string
{
    case JAWA = 'JAWA';
    case SUMATRA = 'SUMATRA';
    case KALIMANTAN = 'KALIMANTAN';
    case SULAWESI = 'SULAWESI';
    case NTT = 'NTT';

    public function label(): string
    {
        return match($this){
            self::JAWA => 'Jawa',
            self::SUMATRA => 'Sumatra',
            self::KALIMANTAN => 'Kalimantan',
            self::SULAWESI => 'Sulawesi',
            self::NTT => 'Nusa Tanggara Timur',
        };
    }

    public function short(): string
    {
        return $this === self::NTT ? 'NTT' : $this->label();
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}