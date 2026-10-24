<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Fungsi format tampilan murni (tanpa efek samping), dipakai oleh View. */
final class Format
{
    /** "13.45" — jam.menit gaya Indonesia. */
    public static function clock(?CarbonInterface $at): string
    {
        return $at ? $at->format('H.i') : '-';
    }

    /** "Kam 22.09" — hari singkat + jam. */
    public static function dayClock(?CarbonInterface $at): string
    {
        return $at ? $at->translatedFormat('D').' '.self::clock($at) : '-';
    }

    /** "1,25" — angka gaya Indonesia. */
    public static function meters(?float $value, int $digits = 2): string
    {
        return $value === null ? '-' : number_format($value, $digits, ',', '.');
    }

    public static function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($parts === []) {
            return '?';
        }
        $letters = count($parts) === 1
            ? mb_substr($parts[0], 0, 2)
            : mb_substr($parts[0], 0, 1).mb_substr($parts[count($parts) - 1], 0, 1);

        return mb_strtoupper($letters);
    }

    /** Nomor lokal 08xx -> format internasional 62xx untuk tautan wa.me. */
    public static function waNumber(?string $local): string
    {
        $digits = preg_replace('/\D+/', '', (string) $local) ?? '';

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }

    /** Normalisasi input nomor WhatsApp ke bentuk 08xxxxxxxxxx. */
    public static function normalizeWhatsapp(?string $raw): string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw) ?? '';
        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }
        if (str_starts_with($digits, '8')) {
            return '0'.$digits;
        }

        return $digits;
    }
}
