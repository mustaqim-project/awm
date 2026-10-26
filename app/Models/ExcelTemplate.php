<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class ExcelTemplate extends Model
{
    use HasFactory;

    protected $table = 'excel_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'format',
        'description',
        'highlights',
        'file_path',
        'file_name',
        'file_size',
        'downloads_count',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'highlights' => 'array',
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'downloads_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public static function availableCategories(): array
    {
        return [
            'Buku Kas',
            'Laporan Finansial',
            'Inventori',
            'Faktur & Kasir',
            'Operasional & SDM',
            'Pajak UMKM',
            'Lainnya',
        ];
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 1) . ' ' . ($units[$power] ?? 'B');
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('template.file', $this->slug);
    }

    /**
     * Populate initial starter templates from public/downloads if missing or table is empty.
     */
    public static function seedDefaultTemplatesIfEmpty(): void
    {
        $defaults = [
            [
                'slug' => 'suite-template-excel-super-lengkap-umkm',
                'name' => 'All-in-One Master Suite Template Excel UMKM (Super Lengkap + Chart)',
                'category' => 'Lainnya',
                'format' => 'XLSX',
                'description' => 'Operating system bisnis all-in-one dalam satu file Excel: Dashboard KPI dengan grafik visual, Penjualan, Stok Otomatis, Buku Kas, HPP & BEP, Payroll Karyawan, Marketplace & Pajak PPh 0.5%. Pengguna tinggal input data di kolom hijau!',
                'highlights' => [
                    '10 sheet terintegrasi lengkap dengan formula otomatis (SUMIFS, IF, VLOOKUP)',
                    'Executive Dashboard dengan grafik visual Bar Chart omzet & Donut Chart biaya',
                    'Sistem kartu stok otomatis dengan alert peringatan restock darurat',
                    'Kalkulator HPP, penentuan harga jual, markup/margin & analisis titik impas (BEP)',
                    'Rekapitulasi otomatis PPh Final UMKM 0.5% sesuai regulasi PP 55 Tahun 2022',
                ],
                'file_name' => 'Template_Super_Lengkap_UMKM_COOCA.xlsx',
                'sort_order' => 1,
            ],
            [
                'slug' => 'template-penjualan-dan-crm-umkm',
                'name' => 'Template Penjualan Harian, CRM & Komisi Sales (Excel)',
                'category' => 'Faktur & Kasir',
                'format' => 'XLSX',
                'description' => 'Format lengkap rekap penjualan harian dan bulanan multi-kanal (Offline, Shopee, Tokopedia, TikTok Shop, B2B), catatan status pembayaran tempo, database CRM pelanggan, dan kalkulator komisi tim sales.',
                'highlights' => [
                    'Catatan penjualan harian otomatis terhitung diskon dan total bersih',
                    'Monitoring status bayar (Lunas vs Tempo / Belum Lunas)',
                    'Database pelanggan CRM dengan tracking total order dan frekuensi belanja',
                    'Perhitungan otomatis komisi sales berdasarkan pencapaian target',
                ],
                'file_name' => 'Template_A_Penjualan_dan_Pelanggan_COOCA.xlsx',
                'sort_order' => 2,
            ],
            [
                'slug' => 'template-stok-persediaan-dan-opname',
                'name' => 'Template Kartu Stok, SKU & Stock Opname Otomatis',
                'category' => 'Inventori',
                'format' => 'XLSX',
                'description' => 'Kendalikan persediaan toko Anda secara presisi. Dilengkapi master kode SKU, kartu mutasi stok in/out otomatis, nilai aset persediaan berdasarkan HPP, dan deteksi dini stok menipis (restock alert).',
                'highlights' => [
                    'Kartu stok mutasi masuk dan keluar otomatis menghitung sisa stok',
                    'Kalkulasi nilai total aset persediaan gudang secara real-time',
                    'Indikator alert visual otomatis: "PERLU RESTOCK" vs "AMAN"',
                    'Format stock opname untuk mendeteksi selisih fisik vs catatan sistem',
                ],
                'file_name' => 'Template_B_Stok_dan_Persediaan_COOCA.xlsx',
                'sort_order' => 3,
            ],
            [
                'slug' => 'template-keuangan-buku-kas-dan-laba-rugi',
                'name' => 'Template Buku Kas Harian, Arus Kas & Laba Rugi Otomatis',
                'category' => 'Buku Kas',
                'format' => 'XLSX',
                'description' => 'Buku kas harian multi-akun (Kas Tunai Kasir & Rekening Bank) dengan perhitungan saldo berjalan otomatis, laporan arus kas (cash flow), dan ringkasan laba rugi operasional usaha.',
                'highlights' => [
                    'Buku kas masuk & keluar dengan saldo berjalan real-time tanpa rumus rumit',
                    'Pemisahan kantong kas tunai kasir vs rekening bank operasional',
                    'Laporan laba rugi bulanan terformat standar akuntansi sederhana',
                    'Dapat langsung diprint atau diekspor ke PDF untuk laporan investor/bank',
                ],
                'file_name' => 'Template_C_Keuangan_dan_Pembukuan_COOCA.xlsx',
                'sort_order' => 4,
            ],
            [
                'slug' => 'kalkulator-hpp-harga-jual-dan-bep',
                'name' => 'Kalkulator HPP Produk, Margin & Analisis Titik Impas (BEP)',
                'category' => 'Laporan Finansial',
                'format' => 'XLSX',
                'description' => 'Cegah salah pasang harga dan jual rugi! Hitung HPP akurat dari bahan baku, kemasan, tenaga kerja, dan overhead, serta simulasi target titik impas (Break-Even Point) unit dan rupiah.',
                'highlights' => [
                    'Kalkulator breakdown HPP per porsi/unit produk (F&B, retail, manufaktur)',
                    'Formula penentuan harga jual rekomendasi berdasarkan target margin laba',
                    'Perhitungan BEP otomatis: Berapa produk yang harus terjual agar balik modal',
                    'Simulasi batas maksimal diskon promo agar bisnis tetap profit',
                ],
                'file_name' => 'Template_D_Harga_HPP_dan_Keuntungan_COOCA.xlsx',
                'sort_order' => 5,
            ],
            [
                'slug' => 'template-sdm-absensi-dan-payroll-gaji',
                'name' => 'Template SDM, Rekap Absensi, Lembur & Penggajian (Payroll)',
                'category' => 'Operasional & SDM',
                'format' => 'XLSX',
                'description' => 'Kelola tim dan karyawan toko Anda secara tertata. Mulai dari database karyawan, rekapitulasi kehadiran dan jam lembur, perhitungan uang lembur, tunjangan, potongan kasbon, hingga gaji bersih (Take Home Pay).',
                'highlights' => [
                    'Master database karyawan lengkap dengan divisi, jabatan, dan status',
                    'Kalkulator uang lembur otomatis berdasarkan tarif per jam dan kehadiran',
                    'Daftar payroll penggajian bersih bulanan siap transfer',
                    'Format slip gaji sederhana siap kirim ke WhatsApp karyawan',
                ],
                'file_name' => 'Template_E_SDM_dan_Penggajian_COOCA.xlsx',
                'sort_order' => 6,
            ],
            [
                'slug' => 'template-marketing-dan-marketplace-roas',
                'name' => 'Template Marketing Medsos, ROAS Iklan & Penjualan Marketplace',
                'category' => 'Lainnya',
                'format' => 'XLSX',
                'description' => 'Maksimalkan omzet penjualan online Anda. Dilengkapi kalender konten media sosial (TikTok, Instagram), rekap pencairan dana marketplace (Shopee, Tokopedia, TikTok Shop), dan analisis ROAS biaya iklan berbayar.',
                'highlights' => [
                    'Analisis ROAS (Return on Ad Spend) untuk Meta Ads, TikTok Ads, dan Shopee Ads',
                    'Evaluasi profitabilitas iklan otomatis: "Sangat Profitable" vs "Evaluasi/Rugi"',
                    'Rekapitulasi omzet kotor vs potongan komisi platform marketplace',
                    'Kalender konten media sosial terstruktur dengan hook dan Call to Action',
                ],
                'file_name' => 'Template_F_Marketing_dan_Marketplace_COOCA.xlsx',
                'sort_order' => 7,
            ],
            [
                'slug' => 'template-operasional-dan-sop-toko',
                'name' => 'Template Checklist SOP Buka/Tutup Toko & Log Komplain',
                'category' => 'Operasional & SDM',
                'format' => 'XLSX',
                'description' => 'Standardisasi operasional outlet usaha Anda. Checklist harian pembukaan dan penutupan toko, jadwal servis perawatan mesin/aset, monitoring tugas tim, dan pencatatan komplain pelanggan.',
                'highlights' => [
                    'Checklist SOP harian shift pagi (opening) dan shift malam (closing)',
                    'Pencegahan selisih uang kas kasir melalui rekonsiliasi kas fisik saat tutup toko',
                    'Log monitoring komplain pelanggan beserta solusi dan status penyelesaian',
                    'Jadwal servis preventif mesin espresso, chiller, dan aset operasional toko',
                ],
                'file_name' => 'Template_G_Operasional_Usaha_COOCA.xlsx',
                'sort_order' => 8,
            ],
            [
                'slug' => 'template-akuntansi-dan-pajak-umkm',
                'name' => 'Template Jurnal Akuntansi, Neraca Saldo & PPh Final UMKM 0.5%',
                'category' => 'Pajak UMKM',
                'format' => 'XLSX',
                'description' => 'Pembukuan standar akuntansi double-entry (Debit & Kredit), pengecekan neraca saldo balance, penyusutan aset tetap, dan rekapitulasi perhitungan pajak PPh Final 0.5% sesuai ketentuan PP 55 Tahun 2022.',
                'highlights' => [
                    'Jurnal umum debit/kredit dengan validasi otomatis status keseimbangan (Balance)',
                    'Rekapitulasi omzet bruto bulanan dengan batas bebas pajak Rp 500 Juta bagi OP',
                    'Kalkulasi otomatis PPh Final 0.5% per bulan lengkap dengan status NTPN',
                    'Daftar aset tetap dan depresiasi garis lurus untuk pencatatan nilai buku',
                ],
                'file_name' => 'Template_H_Akuntansi_dan_Perpajakan_COOCA.xlsx',
                'sort_order' => 9,
            ],
            [
                'slug' => 'pembukuan-warung-excel',
                'name' => 'Template Pembukuan Warung & Toko Kelontong (Excel)',
                'category' => 'Buku Kas',
                'format' => 'XLSX',
                'description' => 'Format siap pakai untuk mencatat kas masuk harian, kas keluar, belanja kulakan warung, dan saldo kas otomatis.',
                'highlights' => [
                    'Buku kas masuk & keluar otomatis',
                    'Formula rekap saldo harian tanpa rumus rumit',
                    'Daftar hutang / bon pelanggan warung',
                    'Grafik ringkas pengeluaran bulanan',
                ],
                'file_name' => 'Template_Pembukuan_Warung_COOCA.xlsx',
                'sort_order' => 10,
            ],
            [
                'slug' => 'laporan-keuangan-sederhana',
                'name' => 'Template Laporan Keuangan Sederhana UMKM',
                'category' => 'Laporan Finansial',
                'format' => 'XLSX',
                'description' => 'Template laporan laba rugi bulanan dan neraca mini untuk mengajukan pinjaman bank (KUR) atau evaluasi investor.',
                'highlights' => [
                    'Format standar Laporan Laba Rugi (Income Statement)',
                    'Ringkasan Aset, Kas, dan Kewajiban usaha',
                    'Grafik margin kotor vs margin bersih',
                    'Dapat langsung dicetak ke PDF',
                ],
                'file_name' => 'Template_Laporan_Keuangan_UMKM_COOCA.xlsx',
                'sort_order' => 11,
            ],
            [
                'slug' => 'stok-opname-excel',
                'name' => 'Template Stok Opname & Kartu Persediaan Barang',
                'category' => 'Inventori',
                'format' => 'XLSX',
                'description' => 'Cegah kehilangan barang dan deteksi selisih antara stok di catatan dengan stok fisik di rak toko Anda.',
                'highlights' => [
                    'Pencatatan saldo awal, masuk, keluar, dan sisa stok',
                    'Kolom selisih fisik otomatis (anomali stok)',
                    'Perhitungan nilai aset persediaan berdasarkan harga beli',
                    'Highlight otomatis untuk stok yang menipis (Restock alert)',
                ],
                'file_name' => 'Template_Stok_Opname_COOCA.xlsx',
                'sort_order' => 12,
            ],
            [
                'slug' => 'invoice-sederhana',
                'name' => 'Template Invoice & Nota Pembayaran Sederhana',
                'category' => 'Faktur & Kasir',
                'format' => 'XLSX',
                'description' => 'Buat invoice profesional dan nota digital dalam hitungan detik. Dilengkapi nomor rekening, QRIS, dan rincian pajak.',
                'highlights' => [
                    'Format invoice profesional berlogo toko',
                    'Kalkulasi otomatis subtotal, diskon, dan PPN',
                    'Nomor invoice unik dan tanggal jatuh tempo',
                    'Bisa langsung dibagikan ke WhatsApp pelanggan',
                ],
                'file_name' => 'Template_Invoice_COOCA.xlsx',
                'sort_order' => 13,
            ],
        ];

        Storage::disk('public')->makeDirectory('templates');

        foreach ($defaults as $item) {
            $sourcePath = public_path('downloads/' . $item['file_name']);
            $targetRelative = 'templates/' . $item['file_name'];
            $fileSize = 0;

            if (File::exists($sourcePath)) {
                $fileSize = File::size($sourcePath);
                if (! Storage::disk('public')->exists($targetRelative)) {
                    Storage::disk('public')->put($targetRelative, File::get($sourcePath));
                }
            }

            self::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'format' => $item['format'],
                    'description' => $item['description'],
                    'highlights' => $item['highlights'],
                    'file_path' => $targetRelative,
                    'file_name' => $item['file_name'],
                    'file_size' => $fileSize,
                    'is_active' => true,
                    'sort_order' => $item['sort_order'],
                ]
            );
        }
    }
}
