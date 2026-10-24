<?php

namespace App\Http\Controllers;

use BackedEnum;

abstract class Controller
{
    /**
     * Ambil nilai filter dari query string hanya bila cocok dengan enum; selain itu null.
     *
     * @param  class-string<BackedEnum>  $enum
     */
    protected function enumFilter(mixed $value, string $enum): ?string
    {
        return is_string($value) && $enum::tryFrom($value) !== null ? $value : null;
    }

    /** Ambil integer positif dari query string, selain itu null. */
    protected function intFilter(mixed $value): ?int
    {
        return is_string($value) && ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }

    /** Teks pencarian dibersihkan & dibatasi panjangnya. */
    protected function textFilter(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : mb_substr($value, 0, 100);
    }
}
