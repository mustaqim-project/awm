<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Business Calculators Suite Translations (Indonesian)
    |--------------------------------------------------------------------------
    */

    // Common
    'calc_breadcrumb_home' => 'Beranda',
    'calc_breadcrumb_hub' => 'Alat Hitung Bisnis UMKM',
    'calc_currency_symbol' => 'Rp',
    'calc_action_calculate' => 'Hitung Sekarang',
    'calc_action_reset' => 'Reset Formulir',
    'calc_action_copy' => 'Salin Ringkasan',
    'calc_action_print' => 'Cetak / Unduh PDF',
    'calc_copied_success' => 'Ringkasan berhasil disalin ke clipboard!',
    'calc_disclaimer' => 'Kalkulator ini merupakan alat simulasi perkiraan. Nilai akhir aktual dapat bervariasi bergantung pada fluktuasi harga bahan dan beban operasional riil usaha Anda.',
    'calc_cta_erp_title' => 'Ingin Perhitungan Otomatis Terhubung Kasir & Stok?',
    'calc_cta_erp_desc' => 'Di COOCA, Anda tidak perlu menghitung manual lagi. Setiap penjualan kasir otomatis memotong stok bahan dan mencatat HPP serta laba kotor secara real-time.',
    'calc_cta_erp_button' => 'Coba COOCA Gratis',

    // Index / Hub
    'hub_kicker' => 'Alat Bantu Keputusan Finansial UMKM',
    'hub_title' => 'Kalkulator Finansial & HPP Presisi',
    'hub_subtitle' => 'Ambil keputusan harga jual dan kelola modal dengan pasti. Hitung biaya bahan baku per porsi, tentukan titik impas (BEP), dan simulasikan laba bersih riil secara instan tanpa perlu registrasi.',
    'hub_badge_free' => '100% Gratis Tanpa Daftar',
    'hub_badge_privacy' => 'Data Aman di Peramban Anda',
    'hub_badge_standard' => 'Standar SAK EMKM Indonesia',

    // 1. HPP
    'hpp_kicker' => 'Metode 3-Pilar Akuntansi Standar SAK EMKM',
    'hpp_title' => 'Kalkulator HPP & Biaya Produksi',
    'hpp_subtitle' => 'Ketahui modal pokok riil per porsi makanan, minuman, atau produk jualan Anda. Hindari jebakan harga jual murah yang ternyata membuat usaha tekor dan merugi tanpa disadari.',
    'hpp_label_raw_materials' => 'Biaya Bahan Baku Langsung',
    'hpp_label_labor' => 'Upah Tenaga Kerja Langsung',
    'hpp_label_overhead' => 'Biaya Overhead Operasional (Listrik, Gas, Kemasan)',
    'hpp_label_target_qty' => 'Jumlah Porsi / Unit yang Dihasilkan',
    'hpp_result_unit_cogs' => 'HPP Riil per Unit / Porsi',
    'hpp_result_total_batch' => 'Total Biaya Produksi per Batch',

    // 2. BEP (Titik Impas)
    'bep_kicker' => 'Analisis Titik Impas Operasional',
    'bep_title' => 'Kalkulator BEP (Break Even Point)',
    'bep_subtitle' => 'Hitung berapa banyak porsi atau barang yang wajib terjual dalam sebulan agar seluruh biaya sewa tempat, gaji karyawan, dan operasional tertutupi secara impas.',
    'bep_label_fixed_costs' => 'Total Biaya Tetap per Bulan (Sewa, Gaji Pokok, Wifi)',
    'bep_label_price_per_unit' => 'Harga Jual per Porsi / Unit',
    'bep_label_variable_cost_per_unit' => 'Biaya Variabel per Porsi (Bahan Baku, Kemasan)',
    'bep_result_units' => 'Target Penjualan Impas (Unit/Porsi)',
    'bep_result_revenue' => 'Target Omzet Impas (Rupiah)',

    // 3. Harga Jual & Margin
    'price_kicker' => 'Penetapan Harga Jual Menguntungkan',
    'price_title' => 'Kalkulator Harga Jual & Margin Laba',
    'price_subtitle' => 'Tentukan harga jual yang ideal berdasarkan target margin keuntungan atau markup modal. Lindungi margin usaha dari potongan komisi aplikasi online.',
    'price_label_cost' => 'Modal Pokok (HPP) per Unit',
    'price_label_method' => 'Metode Penentuan Harga',
    'price_method_margin' => 'Berdasarkan Target Margin (%)',
    'price_method_markup' => 'Berdasarkan Markup Modal (%)',
    'price_label_marketplace_fee' => 'Potongan Komisi Saluran / Aplikasi (%)',
    'price_result_selling_price' => 'Rekomendasi Harga Jual',
    'price_result_gross_profit' => 'Laba Kotor per Unit',

    // 4. Laba Bersih
    'profit_kicker' => 'Simulasi Laba Bersih Usaha',
    'profit_title' => 'Kalkulator Laba Bersih Riil UMKM',
    'profit_subtitle' => 'Pisahkan antara omzet kotor dan uang yang benar-benar menjadi keuntungan bersih bisnis Anda setelah dikurangi seluruh pengeluaran operasional.',
    'profit_label_revenue' => 'Total Pendapatan / Omzet Kotor Bulanan',
    'profit_label_cogs' => 'Total Beban Pokok Penjualan (HPP)',
    'profit_label_opex' => 'Beban Operasional & Administrasi Bulanan',
    'profit_label_tax' => 'Pajak & Beban Lainnya',
    'profit_result_net' => 'Estimasi Laba Bersih Bulanan',
    'profit_result_margin' => 'Net Profit Margin (%)',

    // 5. Gaji Karyawan & Payroll
    'payroll_kicker' => 'Perhitungan Upah & Tunjangan Karyawan',
    'payroll_title' => 'Kalkulator Gaji Karyawan & Beban BPJS',
    'payroll_subtitle' => 'Kalkulasikan estimasi take-home pay karyawan dan total biaya tenaga kerja yang wajib ditanggung pemilik bisnis secara transparan sesuai ketentuan ketenagakerjaan.',
    'payroll_label_basic' => 'Gaji Pokok Bulanan',
    'payroll_label_allowance' => 'Tunjangan Tetap (Makan, Transport)',
    'payroll_label_overtime' => 'Lembur & Insentif Prestasi',
    'payroll_label_bpjs_tk' => 'Iuran BPJS Ketenagakerjaan (JKK, JKM, JHT, JP)',
    'payroll_label_bpjs_kes' => 'Iuran BPJS Kesehatan (4% Perusahaan, 1% Karyawan)',
    'payroll_result_take_home' => 'Estimasi Take Home Pay Karyawan',
    'payroll_result_company_cost' => 'Total Beban Pengeluaran Usaha (Employer Cost)',

    // 6. PPh Final 0.5%
    'tax_kicker' => 'Perpajakan PP 55 / UU HPP untuk UMKM',
    'tax_title' => 'Kalkulator Pajak UMKM (PPh Final 0.5%)',
    'tax_subtitle' => 'Hitung kewajiban setor PPh Final 0.5% per bulan dengan memperhitungkan batasan omzet tidak kena pajak Rp500 juta per tahun bagi Wajib Pajak Orang Pribadi.',
    'tax_label_taxpayer_type' => 'Status Wajib Pajak',
    'tax_type_individual' => 'Orang Pribadi (Bebas Pajak s.d. Rp500 Juta Omzet Kumulatif)',
    'tax_type_corporate' => 'Badan Usaha / CV / PT (Langsung 0.5% dari Omzet)',
    'tax_label_monthly_turnover' => 'Peredaran Bruto / Omzet Bulan Berjalan',
    'tax_label_cumulative_turnover' => 'Akumulasi Omzet Tahun Berjalan Sebelumnya',
    'tax_result_tax_payable' => 'PPh Final Terutang Bulan Ini',

    // 7. Omzet Harian & Target Penjualan
    'rev_kicker' => 'Perencanaan Arus Kas Harian',
    'rev_title' => 'Kalkulator Target Omzet Harian & Mingguan',
    'rev_subtitle' => 'Bagi target laba dan biaya operasional bulanan Anda menjadi target omzet harian yang realistis untuk dicapai oleh kasir dan tim toko Anda.',
    'rev_label_target_monthly_net' => 'Target Laba Bersih yang Ingin Dicapai per Bulan',
    'rev_label_monthly_fixed_costs' => 'Estimasi Biaya Operasional Tetap Bulanan',
    'rev_label_gross_margin_pct' => 'Rata-rata Margin Laba Kotor Produk (%)',
    'rev_label_operating_days' => 'Jumlah Hari Buka Usaha dalam Sebulan',
    'rev_result_daily_target' => 'Target Omzet Penjualan per Hari',

    // 8. Simulasi Bisnis What-If
    'sim_kicker' => 'Eksplorasi Skenario Bisnis Lanjutan',
    'sim_title' => 'Kalkulator Simulasi Bisnis What-If',
    'sim_subtitle' => 'Uji dampak kenaikan harga bahan baku, diskon promo, atau pembukaan cabang baru terhadap arus kas dan profitabilitas sebelum Anda mengambil keputusan berisiko.',
    'sim_label_baseline_rev' => 'Omzet Saat Ini',
    'sim_label_price_change' => 'Rencana Perubahan Harga (%)',
    'sim_label_cost_change' => 'Estimasi Kenaikan Harga Bahan (%)',
    'sim_label_volume_change' => 'Estimasi Perubahan Jumlah Transaksi (%)',
    'sim_result_impact_profit' => 'Dampak Bersih terhadap Laba Usaha',
];

