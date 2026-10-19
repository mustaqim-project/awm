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
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($nilai < 12) {
            $hasil = ' ' . $huruf[(int) $nilai];
        } elseif ($nilai < 20) {
            $hasil = self::terbilang($nilai - 10) . ' Belas';
        } elseif ($nilai < 100) {
            $hasil = self::terbilang((int) ($nilai / 10)) . ' Puluh ' . self::terbilang(fmod($nilai, 10));
        } elseif ($nilai < 200) {
            $hasil = ' Seratus ' . self::terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            $hasil = self::terbilang((int) ($nilai / 100)) . ' Ratus ' . self::terbilang(fmod($nilai, 100));
        } elseif ($nilai < 2000) {
            $hasil = ' Seribu ' . self::terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $hasil = self::terbilang((int) ($nilai / 1000)) . ' Ribu ' . self::terbilang(fmod($nilai, 1000));
        } elseif ($nilai < 1000000000) {
            $hasil = self::terbilang((int) ($nilai / 1000000)) . ' Juta ' . self::terbilang(fmod($nilai, 1000000));
        } elseif ($nilai < 1000000000000) {
            $hasil = self::terbilang((int) ($nilai / 1000000000)) . ' Miliar ' . self::terbilang(fmod($nilai, 1000000000));
        } else {
            $hasil = self::terbilang((int) ($nilai / 1000000000000)) . ' Triliun ' . self::terbilang(fmod($nilai, 1000000000000));
        }

        $clean = trim(preg_replace('/\s+/', ' ', $hasil));
        return $clean !== '' ? $clean . ' Rupiah' : 'Nol Rupiah';
    }
}
