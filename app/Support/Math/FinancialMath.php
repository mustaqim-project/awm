<?php

declare(strict_types=1);

namespace App\Support\Math;

final class FinancialMath
{
    /**
     * Pembagian aman dengan perlindungan division by zero.
     */
    public static function safeDivide(float $numerator, float $denominator, float $fallback = 0.0): float
    {
        if (abs($denominator) < 0.0000001) {
            return $fallback;
        }

        return $numerator / $denominator;
    }

    /**
     * Hitung persentase margin keuntungan: (profit / revenue) * 100.
     * Jika revenue <= 0, return 0.0%.
     */
    public static function calculateMargin(float $profit, float $revenue): float
    {
        if ($revenue <= 0.0000001) {
            return 0.0;
        }

        return round(($profit / $revenue) * 100, 2);
    }

    /**
     * Hitung persentase pertumbuhan periode: ((current - previous) / previous) * 100.
     * Handle kondisi previous = 0.
     */
    public static function calculateGrowth(float $current, float $previous): float
    {
        if (abs($previous) < 0.0000001) {
            return $current > 0.0000001 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Pembulatan nominal finansial standar 2 desimal.
     */
    public static function roundFinancial(float $amount, int $precision = 2): float
    {
        return round($amount, $precision);
    }

    /**
     * Format quantity tanpa trailing zero berlebih (8.0000 => 8, 1.5000 => 1.5).
     */
    public static function formatQty(float $quantity): string
    {
        $formatted = rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    /**
     * Konversi angka nominal ke format kata-kata (terbilang) resmi Bahasa Indonesia.
     * Contoh: 1500000 => "Satu Juta Lima Ratus Ribu Rupiah"
     */
    public static function terbilang(int|float $nilai): string
    {
        $nilai = abs((float) $nilai);
        if ($nilai == 0) {
            return 'Nol Rupiah';
        }
        $hasil = trim(preg_replace('/\s+/', ' ', self::ejaAngka($nilai)));
        return $hasil !== '' ? $hasil . ' Rupiah' : 'Nol Rupiah';
    }

    private static function ejaAngka(float $nilai): string
    {
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($nilai < 12) {
            return ' ' . $huruf[(int) $nilai];
        }
        if ($nilai < 20) {
            return self::ejaAngka($nilai - 10) . ' Belas';
        }
        if ($nilai < 100) {
            return self::ejaAngka((int) ($nilai / 10)) . ' Puluh ' . self::ejaAngka(fmod($nilai, 10));
        }
        if ($nilai < 200) {
            return ' Seratus ' . self::ejaAngka($nilai - 100);
        }
        if ($nilai < 1000) {
            return self::ejaAngka((int) ($nilai / 100)) . ' Ratus ' . self::ejaAngka(fmod($nilai, 100));
        }
        if ($nilai < 2000) {
            return ' Seribu ' . self::ejaAngka($nilai - 1000);
        }
        if ($nilai < 1000000) {
            return self::ejaAngka((int) ($nilai / 1000)) . ' Ribu ' . self::ejaAngka(fmod($nilai, 1000));
        }
        if ($nilai < 1000000000) {
            return self::ejaAngka((int) ($nilai / 1000000)) . ' Juta ' . self::ejaAngka(fmod($nilai, 1000000));
        }
        if ($nilai < 1000000000000) {
            return self::ejaAngka((int) ($nilai / 1000000000)) . ' Miliar ' . self::ejaAngka(fmod($nilai, 1000000000));
        }

        return self::ejaAngka((int) ($nilai / 1000000000000)) . ' Triliun ' . self::ejaAngka(fmod($nilai, 1000000000000));
    }
}
