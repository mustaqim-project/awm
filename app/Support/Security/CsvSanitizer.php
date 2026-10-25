<?php

declare(strict_types=1);

namespace App\Support\Security;

/**
 * Netralkan potensi Formula / CSV Injection (CWE-1236).
 * Mencegah eksekusi formula dinamis (DDE, macro, hyperlink) saat berkas
 * CSV dibuka pada Microsoft Excel, LibreOffice Calc, atau Apple Numbers.
 */
final class CsvSanitizer
{
    /**
     * Karakter pemicu formula spreadsheet yang berpotensi berbahaya.
     */
    private const DANGEROUS_TRIGGERS = ['=', '+', '-', '@', '|', "\t", "\r", "\n", '%'];

    /**
     * Sanitasi nilai sel teks sebelum dicetak ke dalam file CSV.
     * Jika karakter pertama adalah trigger formula, tambahkan awalan petik tunggal (').
     */
    public static function sanitize(mixed $value): string
    {
        $str = (string) ($value ?? '');
        if ($str === '') {
            return '';
        }

        $firstChar = mb_substr($str, 0, 1);

        if (in_array($firstChar, self::DANGEROUS_TRIGGERS, true)) {
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Sanitasi seluruh baris array CSV.
     *
     * @param array<string|int, mixed> $row
     * @return array<string|int, string>
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map(static fn(mixed $val) => self::sanitize($val), $row);
    }
}
