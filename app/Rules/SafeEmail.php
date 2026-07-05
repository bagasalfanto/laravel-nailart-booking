<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Membatasi email hanya boleh berisi karakter aman:
 *   huruf (a-z, A-Z), angka (0-9), titik (.), strip (-), dan @.
 *
 * Menolak underscore, kutip, backtick, dan simbol lain
 * agar input email konsisten & terbatas.
 *
 * Catatan: Laravel sudah mencegah SQL injection melalui prepared statements.
 * Aturan ini hanya untuk pembatasan format input, bukan anti-injection.
 */
class SafeEmail implements ValidationRule
{
    public const PATTERN = '/^[A-Za-z0-9.\-@]+$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::PATTERN, $value)) {
            $fail('Email hanya boleh berisi huruf, angka, titik, dan strip (tanpa underscore atau simbol lain).');
        }
    }
}
