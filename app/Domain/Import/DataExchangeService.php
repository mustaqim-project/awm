<?php

declare(strict_types=1);

namespace App\Domain\Import;

use App\Models\Attendance;
use App\Models\BusinessMembership;
use App\Models\CommerceOrder;
use App\Models\MarketplaceOrder;
use App\Models\Role;
use App\Models\WorkShift;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Unit;
use App\Models\User;
use App\Support\DocumentNumberGenerator;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Support\Security\CsvSanitizer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DataExchangeService
{
    // Apple HIG / Bento Semantic Palette
    public const COLOR_PRIMARY   = '0F766E'; // Teal 700 (Wajib)
    public const COLOR_LOOKUP    = '1D4ED8'; // Blue 700 (Data Terkait / Dropdown)
    public const COLOR_MUTED     = '475569'; // Slate 600 (Opsional)
    public const COLOR_WARNING   = 'B45309'; // Amber 700 (Kalkulasi / HPP)
    public const BORDER_COLOR    = 'CBD5E1'; // Slate 300
    public const ZEBRA_COLOR     = 'F8FAFC'; // Slate 50
    public const GUIDE_ROW_COLOR = 'F1F5F9'; // Slate 100

    // =========================================================================
    // 1. TEMPLATE GENERATORS
    // =========================================================================

    /**
     * Generate Smart Product Template with BOM Selection Dropdown & Master Data.
     */
    public function generateProductTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // SHEET 1: INPUT DATA
        // -------------------------------------------------------------
        $sheetInput = $spreadsheet->getActiveSheet();
        $sheetInput->setTitle('INPUT_PRODUK');

        $headers = [
            'A1' => ['text' => 'Nama Produk *', 'color' => self::COLOR_PRIMARY, 'width' => 30],
            'B1' => ['text' => 'Kode SKU *', 'color' => self::COLOR_PRIMARY, 'width' => 18],
            'C1' => ['text' => 'Barcode / EAN', 'color' => self::COLOR_MUTED, 'width' => 20],
            'D1' => ['text' => 'Kategori *', 'color' => self::COLOR_LOOKUP, 'width' => 22],
            'E1' => ['text' => 'Satuan Dasar *', 'color' => self::COLOR_LOOKUP, 'width' => 16],
            'F1' => ['text' => 'Harga Jual (Rp) *', 'color' => self::COLOR_PRIMARY, 'width' => 20],
            'G1' => ['text' => 'Harga Beli/HPP (Rp)', 'color' => self::COLOR_WARNING, 'width' => 20],
            'H1' => ['text' => 'Stok Minimum Alert', 'color' => self::COLOR_MUTED, 'width' => 18],
            'I1' => ['text' => 'Pilihan Resep BOM', 'color' => self::COLOR_LOOKUP, 'width' => 35],
            'J1' => ['text' => 'Gudang Utama *', 'color' => self::COLOR_LOOKUP, 'width' => 22],
            'K1' => ['text' => 'Deskripsi Singkat', 'color' => self::COLOR_MUTED, 'width' => 35],
        ];

        foreach ($headers as $cell => $meta) {
            $sheetInput->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheetInput->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheetInput, $cell, $meta['color']);
        }
        $sheetInput->getRowDimension(1)->setRowHeight(32);

        // Sample Row (Row 2)
        $sampleRow = [
            'A2' => 'Kopi Susu Gula Aren',
            'B2' => 'KOP-AREN-01',
            'C2' => '899123450001',
            'D2' => 'Minuman Kopi',
            'E2' => 'cup',
            'F2' => 18000,
            'G2' => 7500,
            'H2' => 10,
            'I2' => '[BOM-001] Resep Kopi Susu Aren',
            'J2' => 'Gudang Utama',
            'K2' => 'Espresso double shot + susu fresh + gula aren',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheetInput->setCellValue($cell, $val);
            $this->applyGuideRowStyle($sheetInput, $cell);
        }
        $sheetInput->getRowDimension(2)->setRowHeight(24);

        // -------------------------------------------------------------
        // SHEET 2: DATA REFERENSI TERKAIT (LOOKUP MASTER)
        // -------------------------------------------------------------
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');

        $categories = ProductCategory::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($categories)) {
            $categories = ['Makanan', 'Minuman', 'Produk Retail', 'Jasa'];
        }

        $units = Unit::available()->pluck('name')->toArray();
        if (empty($units)) {
            $units = ['pcs', 'cup', 'porsi', 'botol', 'box', 'gram', 'kg', 'ml', 'liter'];
        }

        $boms = BomHeader::whereHas('costModel', fn ($q) => $q->where('business_id', $business->id))
            ->with('costModel.product')
            ->get()
            ->map(function ($b) {
                $prodName = $b->costModel?->product?->name;
                $bomName = $b->name ?: ($prodName ? "Resep {$prodName}" : 'Resep Standar');
                $code = $b->code ?: 'BOM';
                return "[{$code}] {$bomName}";
            })
            ->toArray();
        array_unshift($boms, '[TANPA_RESEP] Produk Non-Resep');

        $warehouses = Location::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($warehouses)) {
            $warehouses = ['Gudang Utama', 'Display Toko'];
        }

        $refCols = [
            'A' => ['title' => 'Daftar Kategori', 'data' => $categories, 'named' => 'RefKategori'],
            'B' => ['title' => 'Daftar Satuan', 'data' => $units, 'named' => 'RefSatuan'],
            'C' => ['title' => 'Daftar Resep BOM Tersedia', 'data' => $boms, 'named' => 'RefBomTersedia'],
            'D' => ['title' => 'Daftar Gudang', 'data' => $warehouses, 'named' => 'RefGudang'],
        ];

        foreach ($refCols as $col => $info) {
            $sheetRef->setCellValue("{$col}1", $info['title']);
            $this->applyHeaderStyle($sheetRef, "{$col}1", self::COLOR_PRIMARY);
            $sheetRef->getColumnDimension($col)->setWidth(32);

            $r = 2;
            foreach ($info['data'] as $item) {
                $sheetRef->setCellValue("{$col}{$r}", $item);
                $r++;
            }
            $endRow = max(2, $r - 1);
            $spreadsheet->addNamedRange(new NamedRange($info['named'], $sheetRef, "\${$col}\$2:\${$col}\${$endRow}"));
        }

        // Apply Validation Dropdowns (Rows 3 to 1000)
        $this->applyDropdownValidation($sheetInput, 'D3:D1000', '=RefKategori', 'Pilih Kategori');
        $this->applyDropdownValidation($sheetInput, 'E3:E1000', '=RefSatuan', 'Pilih Satuan');
        $this->applyDropdownValidation($sheetInput, 'I3:I1000', '=RefBomTersedia', 'Pilih Formula Resep BOM');
        $this->applyDropdownValidation($sheetInput, 'J3:J1000', '=RefGudang', 'Pilih Gudang Utama');

        // Sheet 3: Panduan
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_IMPORT', [
            ['Kolom Hijau', 'Wajib diisi. Jika nama produk atau harga kosong, baris ditolak.'],
            ['Kolom Biru', 'Data terkait. Klik panah bawah di Excel (dropdown) untuk memilih kategori, satuan, dan resep BOM yang sudah ada.'],
            ['Kolom Kuning', 'Harga beli / HPP perkiraan. Jika produk menggunakan resep BOM, HPP akan otomatis dihitung dari rincian bahan baku.'],
            ['Kolom Abu-Abu', 'Opsional. Boleh dikosongkan jika belum memiliki barcode atau deskripsi.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Import_Produk_{$business->id}.xlsx");
    }

    /**
     * Generate Customer Template with Dropdowns.
     */
    public function generateCustomerTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_PELANGGAN');

        $headers = [
            'A1' => ['text' => 'Nama Pelanggan *', 'color' => self::COLOR_PRIMARY, 'width' => 28],
            'B1' => ['text' => 'Nomor HP / WhatsApp *', 'color' => self::COLOR_PRIMARY, 'width' => 22],
            'C1' => ['text' => 'Email', 'color' => self::COLOR_MUTED, 'width' => 24],
            'D1' => ['text' => 'Kategori / Tier Diskon', 'color' => self::COLOR_LOOKUP, 'width' => 22],
            'E1' => ['text' => 'Perusahaan / Instansi', 'color' => self::COLOR_MUTED, 'width' => 24],
            'F1' => ['text' => 'Limit Piutang (Rp)', 'color' => self::COLOR_WARNING, 'width' => 20],
            'G1' => ['text' => 'Termin Pembayaran (Hari)', 'color' => self::COLOR_WARNING, 'width' => 22],
            'H1' => ['text' => 'Alamat Lengkap', 'color' => self::COLOR_MUTED, 'width' => 35],
            'I1' => ['text' => 'Kota', 'color' => self::COLOR_MUTED, 'width' => 18],
            'J1' => ['text' => 'NPWP', 'color' => self::COLOR_MUTED, 'width' => 20],
        ];

        foreach ($headers as $cell => $meta) {
            $sheet->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheet->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheet, $cell, $meta['color']);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Sample Row (Row 2)
        $sampleRow = [
            'A2' => 'Budi Santoso',
            'B2' => '081234567890',
            'C2' => 'budi.santoso@example.com',
            'D2' => 'Reguler',
            'E2' => 'CV Maju Jaya',
            'F2' => 5000000,
            'G2' => 30,
            'H2' => 'Jl. Sudirman No. 45, RT 02/04',
            'I2' => 'Jakarta Selatan',
            'J2' => '01.234.567.8-012.000',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $this->applyGuideRowStyle($sheet, $cell);
        }
        $sheet->getRowDimension(2)->setRowHeight(24);

        // Sheet 2: Referensi Tier
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');
        $tiers = ['Reguler', 'VIP', 'Grosir', 'Reseller', 'Distributor'];
        $sheetRef->setCellValue('A1', 'Pilihan Tier Diskon');
        $this->applyHeaderStyle($sheetRef, 'A1', self::COLOR_PRIMARY);
        $sheetRef->getColumnDimension('A')->setWidth(26);
        foreach ($tiers as $idx => $t) {
            $sheetRef->setCellValue('A' . ($idx + 2), $t);
        }
        $spreadsheet->addNamedRange(new NamedRange('RefTierPelanggan', $sheetRef, '$A$2:$A$6'));
        $this->applyDropdownValidation($sheet, 'D3:D1000', '=RefTierPelanggan', 'Pilih Tier Pelanggan');

        // Sheet 3: Panduan
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_PELANGGAN', [
            ['Nomor WhatsApp', 'Wajib diawali angka 08 atau 62. Nomor telepon digunakan sebagai kunci pencegahan duplikasi.'],
            ['Limit Piutang', 'Batas maksimal tagihan belum lunas yang diizinkan untuk pelanggan ini (dalam Rupiah). Kosongkan jika tanpa limit.'],
            ['Termin Pembayaran', 'Jumlah hari jatuh tempo pembayaran (misal 30 untuk tempo 30 hari). Masukkan 0 untuk pembayaran tunai di kasir.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Import_Pelanggan_{$business->id}.xlsx");
    }

    /**
     * Generate Supplier Template with Dropdowns.
     */
    public function generateSupplierTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_SUPPLIER');

        $headers = [
            'A1' => ['text' => 'Nama Supplier / Perusahaan *', 'color' => self::COLOR_PRIMARY, 'width' => 30],
            'B1' => ['text' => 'Kode Supplier', 'color' => self::COLOR_MUTED, 'width' => 18],
            'C1' => ['text' => 'Nama Kontak PIC', 'color' => self::COLOR_MUTED, 'width' => 22],
            'D1' => ['text' => 'Nomor Telepon / WA *', 'color' => self::COLOR_PRIMARY, 'width' => 22],
            'E1' => ['text' => 'Email', 'color' => self::COLOR_MUTED, 'width' => 24],
            'F1' => ['text' => 'Termin Pembayaran (Hari)', 'color' => self::COLOR_LOOKUP, 'width' => 24],
            'G1' => ['text' => 'Nama Bank', 'color' => self::COLOR_MUTED, 'width' => 18],
            'H1' => ['text' => 'Nomor Rekening', 'color' => self::COLOR_MUTED, 'width' => 22],
            'I1' => ['text' => 'Atas Nama Rekening', 'color' => self::COLOR_MUTED, 'width' => 24],
            'J1' => ['text' => 'Alamat Kantor / Gudang', 'color' => self::COLOR_MUTED, 'width' => 35],
        ];

        foreach ($headers as $cell => $meta) {
            $sheet->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheet->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheet, $cell, $meta['color']);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Sample Row (Row 2)
        $sampleRow = [
            'A2' => 'PT Sumber Kopi Nusantara',
            'B2' => 'SUP-001',
            'C2' => 'Ibu Maya',
            'D2' => '081398765432',
            'E2' => 'order@sumberkopi.co.id',
            'F2' => 14,
            'G2' => 'BCA',
            'H2' => '1234567890',
            'I2' => 'PT Sumber Kopi Nusantara',
            'J2' => 'Kawasan Industri Sentul Kav. 8, Bogor',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $this->applyGuideRowStyle($sheet, $cell);
        }
        $sheet->getRowDimension(2)->setRowHeight(24);

        // Sheet 2: Referensi Termin
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');
        $topList = [0, 7, 14, 21, 30, 45, 60, 90];
        $sheetRef->setCellValue('A1', 'Pilihan Termin TOP (Hari)');
        $this->applyHeaderStyle($sheetRef, 'A1', self::COLOR_PRIMARY);
        $sheetRef->getColumnDimension('A')->setWidth(26);
        foreach ($topList as $idx => $top) {
            $sheetRef->setCellValue('A' . ($idx + 2), $top);
        }
        $spreadsheet->addNamedRange(new NamedRange('RefTopSupplier', $sheetRef, '$A$2:$A$9'));
        $this->applyDropdownValidation($sheet, 'F3:F1000', '=RefTopSupplier', 'Pilih Termin Hari');

        // Sheet 3: Panduan
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_SUPPLIER', [
            ['Kode Supplier', 'Dapat dikosongkan. Jika kosong, sistem otomatis membuatkan kode SUP-XXXX.'],
            ['Termin Pembayaran', 'Pilih 0 jika transaksi pembelian selalu Tunai/Cash (COD). Pilih 14, 30, atau 60 hari untuk pembelian tempo.'],
            ['Rekening Bank', 'Data bank digunakan saat tim keuangan mencatat pembayaran tagihan vendor (bill payment).'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Import_Supplier_{$business->id}.xlsx");
    }

    /**
     * Generate Smart Material Template with Category & Supplier Dropdowns.
     */
    public function generateMaterialTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // SHEET 1: INPUT DATA
        // -------------------------------------------------------------
        $sheetInput = $spreadsheet->getActiveSheet();
        $sheetInput->setTitle('INPUT_BAHAN_BAKU');

        $headers = [
            'A1' => ['text' => 'Nama Bahan Baku *', 'color' => self::COLOR_PRIMARY, 'width' => 30],
            'B1' => ['text' => 'Kode / SKU', 'color' => self::COLOR_MUTED, 'width' => 18],
            'C1' => ['text' => 'Kategori Bahan *', 'color' => self::COLOR_LOOKUP, 'width' => 22],
            'D1' => ['text' => 'Satuan Dasar *', 'color' => self::COLOR_LOOKUP, 'width' => 16],
            'E1' => ['text' => 'Harga Beli Standar (Rp)', 'color' => self::COLOR_WARNING, 'width' => 24],
            'F1' => ['text' => 'Nama Pemasok / Supplier', 'color' => self::COLOR_LOOKUP, 'width' => 28],
            'G1' => ['text' => 'Stok Minimum Alert', 'color' => self::COLOR_MUTED, 'width' => 18],
            'H1' => ['text' => 'Deskripsi / Catatan', 'color' => self::COLOR_MUTED, 'width' => 35],
        ];

        foreach ($headers as $cell => $meta) {
            $sheetInput->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheetInput->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheetInput, $cell, $meta['color']);
        }
        $sheetInput->getRowDimension(1)->setRowHeight(32);

        // Sample Row (Row 2)
        $sampleRow = [
            'A2' => 'Biji Kopi Arabika Gayo',
            'B2' => 'MAT-KOP-01',
            'C2' => 'Bahan Baku Utama',
            'D2' => 'kg',
            'E2' => 125000,
            'F2' => 'CV Sumber Kopi Nusantara',
            'G2' => 5,
            'H2' => 'Biji sangrai medium roast, grade premium',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheetInput->setCellValue($cell, $val);
            $this->applyGuideRowStyle($sheetInput, $cell);
        }
        $sheetInput->getRowDimension(2)->setRowHeight(24);

        // -------------------------------------------------------------
        // SHEET 2: DATA REFERENSI TERKAIT (LOOKUP MASTER)
        // -------------------------------------------------------------
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');

        $categories = MaterialCategory::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($categories)) {
            $categories = ['Bahan Baku Utama', 'Bahan Penolong', 'Kemasan / Packaging', 'Bumbu & Rempah', 'Lainnya'];
        }

        $units = Unit::available()->pluck('name')->toArray();
        if (empty($units)) {
            $units = ['kg', 'gram', 'liter', 'ml', 'pcs', 'butir', 'lembar', 'pack'];
        }

        $suppliers = Supplier::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($suppliers)) {
            $suppliers = ['Pemasok Umum / Belanja Pasar', 'Supplier Langganan A'];
        }

        $refCols = [
            'A' => ['title' => 'Daftar Kategori Bahan', 'data' => $categories, 'named' => 'RefKategoriBahan'],
            'B' => ['title' => 'Daftar Satuan Master', 'data' => $units, 'named' => 'RefSatuanBahan'],
            'C' => ['title' => 'Daftar Pemasok Terdaftar', 'data' => $suppliers, 'named' => 'RefSupplierBahan'],
        ];

        foreach ($refCols as $col => $info) {
            $sheetRef->setCellValue("{$col}1", $info['title']);
            $this->applyHeaderStyle($sheetRef, "{$col}1", self::COLOR_PRIMARY);
            $sheetRef->getColumnDimension($col)->setWidth(30);

            $r = 2;
            foreach ($info['data'] as $item) {
                $sheetRef->setCellValue("{$col}{$r}", $item);
                $r++;
            }
            $endRow = max(2, $r - 1);
            $spreadsheet->addNamedRange(new NamedRange($info['named'], $sheetRef, "\${$col}\$2:\${$col}\${$endRow}"));
        }

        // Apply Dropdown Validations
        $this->applyDropdownValidation($sheetInput, 'C3:C1000', '=RefKategoriBahan', 'Pilih Kategori');
        $this->applyDropdownValidation($sheetInput, 'D3:D1000', '=RefSatuanBahan', 'Pilih Satuan');
        $this->applyDropdownValidation($sheetInput, 'F3:F1000', '=RefSupplierBahan', 'Pilih Pemasok');

        // -------------------------------------------------------------
        // SHEET 3: PANDUAN IMPORT
        // -------------------------------------------------------------
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_IMPORT', [
            ['Kolom Hijau (*)', 'Wajib diisi. Nama bahan baku tidak boleh kosong.'],
            ['Kolom Biru (Lookup)', 'Pilih dari menu dropdown yang muncul saat sel diklik.'],
            ['Kolom Kuning (HPP)', 'Harga beli standar per satuan dasar. Digunakan untuk perhitungan HPP resep.'],
            ['Kode SKU', 'Jika dikosongkan, sistem akan otomatis men-generate kode bahan baku berurutan.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Import_Bahan_Baku_{$business->id}.xlsx");
    }

    /**
     * Generate Smart Recipe (BOM) Template with Product & Material Dynamic Lookups.
     */
    public function generateRecipeTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // SHEET 1: INPUT DATA
        // -------------------------------------------------------------
        $sheetInput = $spreadsheet->getActiveSheet();
        $sheetInput->setTitle('INPUT_RESEP_BOM');

        $headers = [
            'A1' => ['text' => 'Nama / SKU Produk Jadi *', 'color' => self::COLOR_PRIMARY, 'width' => 32],
            'B1' => ['text' => 'Nama / SKU Bahan Baku *', 'color' => self::COLOR_PRIMARY, 'width' => 32],
            'C1' => ['text' => 'Jumlah Pemakaian *', 'color' => self::COLOR_PRIMARY, 'width' => 20],
            'D1' => ['text' => 'Satuan Bahan *', 'color' => self::COLOR_LOOKUP, 'width' => 16],
            'E1' => ['text' => 'Susut / Waste %', 'color' => self::COLOR_WARNING, 'width' => 18],
            'F1' => ['text' => 'Catatan Bahan', 'color' => self::COLOR_MUTED, 'width' => 35],
        ];

        foreach ($headers as $cell => $meta) {
            $sheetInput->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheetInput->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheetInput, $cell, $meta['color']);
        }
        $sheetInput->getRowDimension(1)->setRowHeight(32);

        // Sample Row (Row 2)
        $sampleRow = [
            'A2' => 'Kopi Susu Gula Aren',
            'B2' => 'Biji Kopi Arabika Gayo',
            'C2' => 18,
            'D2' => 'gram',
            'E2' => 2.5,
            'F2' => 'Espresso shot 18g tamped',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheetInput->setCellValue($cell, $val);
            $this->applyGuideRowStyle($sheetInput, $cell);
        }
        $sheetInput->getRowDimension(2)->setRowHeight(24);

        // -------------------------------------------------------------
        // SHEET 2: DATA REFERENSI TERKAIT (LOOKUP MASTER)
        // -------------------------------------------------------------
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');

        $products = Product::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($products)) {
            $products = ['Produk Contoh A', 'Produk Contoh B'];
        }

        $materials = Material::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($materials)) {
            $materials = ['Bahan Baku Contoh 1', 'Bahan Baku Contoh 2'];
        }

        $units = Unit::available()->pluck('name')->toArray();
        if (empty($units)) {
            $units = ['gram', 'kg', 'ml', 'liter', 'pcs', 'butir', 'sendok'];
        }

        $refCols = [
            'A' => ['title' => 'Daftar Produk Jadi Aktif', 'data' => $products, 'named' => 'RefProdukJadiBOM'],
            'B' => ['title' => 'Daftar Bahan Baku Aktif', 'data' => $materials, 'named' => 'RefBahanBakuBOM'],
            'C' => ['title' => 'Daftar Satuan', 'data' => $units, 'named' => 'RefSatuanBOM'],
        ];

        foreach ($refCols as $col => $info) {
            $sheetRef->setCellValue("{$col}1", $info['title']);
            $this->applyHeaderStyle($sheetRef, "{$col}1", self::COLOR_PRIMARY);
            $sheetRef->getColumnDimension($col)->setWidth(32);

            $r = 2;
            foreach ($info['data'] as $item) {
                $sheetRef->setCellValue("{$col}{$r}", $item);
                $r++;
            }
            $endRow = max(2, $r - 1);
            $spreadsheet->addNamedRange(new NamedRange($info['named'], $sheetRef, "\${$col}\$2:\${$col}\${$endRow}"));
        }

        // Apply Dropdown Validations
        $this->applyDropdownValidation($sheetInput, 'A3:A1000', '=RefProdukJadiBOM', 'Pilih Produk Jadi');
        $this->applyDropdownValidation($sheetInput, 'B3:B1000', '=RefBahanBakuBOM', 'Pilih Bahan Baku');
        $this->applyDropdownValidation($sheetInput, 'D3:D1000', '=RefSatuanBOM', 'Pilih Satuan');

        // -------------------------------------------------------------
        // SHEET 3: PANDUAN IMPORT
        // -------------------------------------------------------------
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_IMPORT', [
            ['Multi-Bahan per Produk', 'Satu produk jadi dapat ditulis pada beberapa baris berturut-turut untuk tiap komponen bahan baku yang digunakan.'],
            ['Bahan Baku Terdaftar', 'Bahan baku yang dimasukkan WAJIB sudah ada di Master Data Bahan Baku. Silakan import bahan baku terlebih dahulu jika belum ada.'],
            ['Susut / Waste %', 'Persentase bahan yang terbuang selama proses produksi (contoh: 5 untuk 5%). Biaya HPP akan memperhitungkan susut ini.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Import_Resep_BOM_{$business->id}.xlsx");
    }

    /**
     * Generate Smart Inventory / Initial Stock Template with Dual-Resolution (Products & Materials).
     */
    public function generateInventoryStockTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // PRELOAD MASTER DATA (PRODUCTS, MATERIALS, LOCATIONS, UNITS)
        // -------------------------------------------------------------
        $products = Product::goods()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->with('outputUnit')
            ->orderBy('name')
            ->get();

        $materials = Material::where('business_id', $business->id)
            ->whereNull('discontinued_at')
            ->with('unit')
            ->orderBy('name')
            ->get();

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        if ($locations->isEmpty()) {
            $locations = Location::where('business_id', $business->id)->get();
        }
        $defaultLocation = $locations->first();
        $defaultLocName = $defaultLocation ? $defaultLocation->name : 'Gudang Utama';

        $warehouses = $locations->pluck('name')->toArray();
        if (empty($warehouses)) {
            $warehouses = ['Gudang Utama', 'Outlet Pusat'];
        }

        $units = Unit::available()->pluck('name')->toArray();
        if (empty($units)) {
            $units = ['pcs', 'kg', 'gram', 'liter', 'ml', 'cup', 'box', 'pack'];
        }

        // -------------------------------------------------------------
        // SHEET 1: INPUT DATA
        // -------------------------------------------------------------
        $sheetInput = $spreadsheet->getActiveSheet();
        $sheetInput->setTitle('INPUT_SALDO_STOK');

        $headers = [
            'A1' => ['text' => 'Nama Item / Produk / Bahan *', 'color' => self::COLOR_PRIMARY, 'width' => 35],
            'B1' => ['text' => 'Kode / SKU Item', 'color' => self::COLOR_MUTED, 'width' => 18],
            'C1' => ['text' => 'Lokasi / Outlet / Gudang *', 'color' => self::COLOR_LOOKUP, 'width' => 24],
            'D1' => ['text' => 'Jumlah Stok *', 'color' => self::COLOR_PRIMARY, 'width' => 18],
            'E1' => ['text' => 'Satuan *', 'color' => self::COLOR_LOOKUP, 'width' => 16],
            'F1' => ['text' => 'Biaya Pokok / HPP per Unit (Rp)', 'color' => self::COLOR_WARNING, 'width' => 24],
            'G1' => ['text' => 'Catatan / Alasan Saldo', 'color' => self::COLOR_MUTED, 'width' => 32],
        ];

        foreach ($headers as $cell => $meta) {
            $sheetInput->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheetInput->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheetInput, $cell, $meta['color']);
        }
        $sheetInput->getRowDimension(1)->setRowHeight(32);

        $rowIdx = 2;
        $hasItems = ($products->isNotEmpty() || $materials->isNotEmpty());

        if ($hasItems) {
            // Pre-fill registered Products (Produk Jadi)
            foreach ($products as $p) {
                $sheetInput->setCellValue("A{$rowIdx}", $p->name);
                $sheetInput->setCellValue("B{$rowIdx}", $p->code ?: '');
                $sheetInput->setCellValue("C{$rowIdx}", $defaultLocName);
                $sheetInput->setCellValue("D{$rowIdx}", ''); // Ready for user's physical stock count
                $sheetInput->setCellValue("E{$rowIdx}", $p->outputUnit?->name ?: 'pcs');
                $sheetInput->setCellValue("F{$rowIdx}", (float) ($p->base_cost ?? 0));
                $sheetInput->setCellValue("G{$rowIdx}", 'Saldo awal cut-off go-live sistem');

                $this->applyDataRowStyle($sheetInput, "A{$rowIdx}:G{$rowIdx}", $rowIdx % 2 === 0);
                $sheetInput->getStyle("D{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheetInput->getStyle("F{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');
                $sheetInput->getRowDimension($rowIdx)->setRowHeight(22);
                $rowIdx++;
            }

            // Pre-fill registered Materials (Bahan Baku)
            foreach ($materials as $m) {
                $sheetInput->setCellValue("A{$rowIdx}", $m->name);
                $sheetInput->setCellValue("B{$rowIdx}", $m->code ?: '');
                $sheetInput->setCellValue("C{$rowIdx}", $defaultLocName);
                $sheetInput->setCellValue("D{$rowIdx}", ''); // Ready for user's physical stock count
                $sheetInput->setCellValue("E{$rowIdx}", $m->unit?->name ?: 'unit');
                $sheetInput->setCellValue("F{$rowIdx}", (float) ($m->base_cost ?? 0));
                $sheetInput->setCellValue("G{$rowIdx}", 'Saldo awal cut-off go-live sistem');

                $this->applyDataRowStyle($sheetInput, "A{$rowIdx}:G{$rowIdx}", $rowIdx % 2 === 0);
                $sheetInput->getStyle("D{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheetInput->getStyle("F{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');
                $sheetInput->getRowDimension($rowIdx)->setRowHeight(22);
                $rowIdx++;
            }
        } else {
            // Fallback sample row for fresh tenants without any products/materials yet
            $sampleRow = [
                'A2' => 'Kopi Susu Gula Aren (Contoh)',
                'B2' => 'KOP-AREN-01',
                'C2' => $defaultLocName,
                'D2' => 50,
                'E2' => 'cup',
                'F2' => 7500,
                'G2' => 'Saldo awal cut-off go-live sistem',
            ];
            foreach ($sampleRow as $cell => $val) {
                $sheetInput->setCellValue($cell, $val);
                $this->applyGuideRowStyle($sheetInput, $cell);
            }
            $sheetInput->getStyle('D2')->getNumberFormat()->setFormatCode('#,##0.00');
            $sheetInput->getStyle('F2')->getNumberFormat()->setFormatCode('#,##0');
            $sheetInput->getRowDimension(2)->setRowHeight(24);
            $rowIdx = 3;
        }

        // -------------------------------------------------------------
        // SHEET 2: DATA REFERENSI TERKAIT (LOOKUP MASTER)
        // -------------------------------------------------------------
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');

        $itemList = [];
        foreach ($products as $p) {
            $sku = $p->code ?: 'NO-SKU';
            $itemList[] = "[PRODUK] {$p->name} ({$sku})";
        }
        foreach ($materials as $m) {
            $sku = $m->code ?: 'NO-SKU';
            $itemList[] = "[BAHAN] {$m->name} ({$sku})";
        }
        if (empty($itemList)) {
            $itemList = ['Contoh Produk Jadi', 'Contoh Bahan Baku'];
        }

        $refCols = [
            'A' => ['title' => 'Daftar Master Item (Produk & Bahan)', 'data' => $itemList, 'named' => 'RefMasterItemStok'],
            'B' => ['title' => 'Daftar Gudang / Outlet', 'data' => $warehouses, 'named' => 'RefGudangStok'],
            'C' => ['title' => 'Daftar Satuan', 'data' => $units, 'named' => 'RefSatuanStok'],
        ];

        foreach ($refCols as $col => $info) {
            $sheetRef->setCellValue("{$col}1", $info['title']);
            $this->applyHeaderStyle($sheetRef, "{$col}1", self::COLOR_PRIMARY);
            $sheetRef->getColumnDimension($col)->setWidth(36);

            $r = 2;
            foreach ($info['data'] as $item) {
                $sheetRef->setCellValue("{$col}{$r}", $item);
                $r++;
            }
            $endRow = max(2, $r - 1);
            $spreadsheet->addNamedRange(new NamedRange($info['named'], $sheetRef, "\${$col}\$2:\${$col}\${$endRow}"));
        }

        // Apply Dropdown Validations across all rows
        $validationMaxRow = max(1000, $rowIdx + 100);
        $this->applyDropdownValidation($sheetInput, "C2:C{$validationMaxRow}", '=RefGudangStok', 'Pilih Gudang / Lokasi');
        $this->applyDropdownValidation($sheetInput, "E2:E{$validationMaxRow}", '=RefSatuanStok', 'Pilih Satuan');

        // -------------------------------------------------------------
        // SHEET 3: PANDUAN IMPORT
        // -------------------------------------------------------------
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_IMPORT', [
            ['Data Master Otomatis Terisi', 'Seluruh barang produk jadi dan bahan baku aktif yang sudah terdaftar di sistem otomatis dimasukkan ke lembar template ini.'],
            ['Isi Jumlah Stok Fisik', 'Anda cukup mengisi kolom D (Jumlah Stok) sesuai stok fisik riil di gudang/outlet. Baris yang tidak diisi stoknya akan diabaikan secara aman.'],
            ['Pilihan Gudang & Satuan', 'Gudang dan Satuan dapat diubah sesuai lokasi penyimpanan menggunakan menu pilihan dropdown yang tersedia.'],
            ['Dual-Resolution SKU', 'Template ini mendukung saldo awal untuk PRODUK JADI dan BAHAN BAKU sekaligus dalam satu lembar file Excel.'],
            ['Mutasi Saldo Awal', 'Setiap baris yang berhasil diimport akan otomatis dicatat ke riwayat mutasi stok (Stock Movement) dengan tipe INITIAL.'],
            ['Strategi Duplikat', 'Jika stok di lokasi tersebut sudah memiliki saldo, Anda dapat memilih strategi Lewati (Skip) atau Sesuaikan (Adjust).'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Import_Saldo_Stok_{$business->id}.xlsx");
    }


    // =========================================================================
    // 2. EXPORT ENGINES (DIRECT TABLE ACTIONS)
    // =========================================================================

    /**
     * Export Products to Styled Excel.
     */
    public function exportProducts(Business $business, $query): StreamedResponse
    {
        $products = $query->with(['category', 'outputUnit', 'costModels.latestVersion'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar_Produk');

        $headers = [
            'A1' => ['text' => 'No', 'width' => 8],
            'B1' => ['text' => 'Nama Produk', 'width' => 30],
            'C1' => ['text' => 'Kode SKU', 'width' => 18],
            'D1' => ['text' => 'Barcode', 'width' => 18],
            'E1' => ['text' => 'Kategori', 'width' => 20],
            'F1' => ['text' => 'Satuan', 'width' => 12],
            'G1' => ['text' => 'Harga Jual (Rp)', 'width' => 18],
            'H1' => ['text' => 'Harga Beli/HPP (Rp)', 'width' => 18],
            'I1' => ['text' => 'Stok Minimum', 'width' => 14],
            'J1' => ['text' => 'Status Aktif', 'width' => 14],
        ];

        foreach ($headers as $cell => $meta) {
            $sheet->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheet->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(30);

        $rowIdx = 2;
        foreach ($products as $idx => $p) {
            $sheet->setCellValue("A{$rowIdx}", $idx + 1);
            $sheet->setCellValue("B{$rowIdx}", $p->name);
            $sheet->setCellValue("C{$rowIdx}", $p->sku ?: '-');
            $sheet->setCellValue("D{$rowIdx}", $p->barcode ?: '-');
            $sheet->setCellValue("E{$rowIdx}", $p->category?->name ?: 'Umum');
            $sheet->setCellValue("F{$rowIdx}", $p->outputUnit?->name ?: ($p->unit ?: 'pcs'));
            $sheet->setCellValue("G{$rowIdx}", (float) $p->price);
            $sheet->setCellValue("H{$rowIdx}", (float) ($p->base_cost ?? $p->costModels?->first()?->latestVersion?->total_cost ?? 0));
            $sheet->setCellValue("I{$rowIdx}", (float) ($p->min_stock ?? 0));
            $sheet->setCellValue("J{$rowIdx}", $p->is_active ? 'Aktif' : 'Non-Aktif');

            $this->applyDataRowStyle($sheet, "A{$rowIdx}:J{$rowIdx}", $rowIdx % 2 === 0);
            $sheet->getRowDimension($rowIdx)->setRowHeight(22);
            $rowIdx++;
        }

        return $this->streamSpreadsheet($spreadsheet, "Export_Produk_{$business->id}_" . date('Ymd_His') . ".xlsx");
    }

    /**
     * Export Materials to Styled Excel.
     */
    public function exportMaterials(Business $business, $query): StreamedResponse
    {
        $materials = $query->with(['category', 'unit', 'supplier', 'prices'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar_Bahan_Baku');

        $headers = [
            'A1' => ['text' => 'No', 'width' => 8],
            'B1' => ['text' => 'Nama Bahan Baku', 'width' => 30],
            'C1' => ['text' => 'Kode / SKU', 'width' => 18],
            'D1' => ['text' => 'Kategori', 'width' => 20],
            'E1' => ['text' => 'Satuan', 'width' => 12],
            'F1' => ['text' => 'Harga Beli Terakhir (Rp)', 'width' => 22],
            'G1' => ['text' => 'Stok Minimum', 'width' => 14],
            'H1' => ['text' => 'Pemasok Default', 'width' => 24],
        ];

        foreach ($headers as $cell => $meta) {
            $sheet->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheet->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(30);

        $rowIdx = 2;
        foreach ($materials as $idx => $m) {
            $latestPrice = $m->prices?->first()?->price ?? $m->base_cost ?? 0;
            $sheet->setCellValue("A{$rowIdx}", $idx + 1);
            $sheet->setCellValue("B{$rowIdx}", $m->name);
            $sheet->setCellValue("C{$rowIdx}", $m->code ?: ($m->sku ?: '-'));
            $sheet->setCellValue("D{$rowIdx}", $m->category?->name ?: 'Umum');
            $sheet->setCellValue("E{$rowIdx}", $m->unit?->name ?: 'pcs');
            $sheet->setCellValue("F{$rowIdx}", (float) $latestPrice);
            $sheet->setCellValue("G{$rowIdx}", (float) ($m->minimum_stock ?? 0));
            $sheet->setCellValue("H{$rowIdx}", $m->supplier?->name ?: '-');

            $this->applyDataRowStyle($sheet, "A{$rowIdx}:H{$rowIdx}", $rowIdx % 2 === 0);
            $sheet->getRowDimension($rowIdx)->setRowHeight(22);
            $rowIdx++;
        }

        return $this->streamSpreadsheet($spreadsheet, "Export_Bahan_Baku_{$business->id}_" . date('Ymd_His') . ".xlsx");
    }

    /**
     * Export Customers to Styled Excel.
     */
    public function exportCustomers(Business $business, $query): StreamedResponse
    {
        $customers = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar_Pelanggan');

        $headers = [
            'A1' => ['text' => 'No', 'width' => 8],
            'B1' => ['text' => 'Nama Pelanggan', 'width' => 28],
            'C1' => ['text' => 'No. Telepon / WA', 'width' => 20],
            'D1' => ['text' => 'Email', 'width' => 24],
            'E1' => ['text' => 'Perusahaan', 'width' => 24],
            'F1' => ['text' => 'Limit Piutang (Rp)', 'width' => 18],
            'G1' => ['text' => 'Saldo Piutang Berjalan', 'width' => 20],
            'H1' => ['text' => 'Poin Loyalitas', 'width' => 14],
            'I1' => ['text' => 'Kota', 'width' => 18],
            'J1' => ['text' => 'Alamat', 'width' => 35],
        ];

        foreach ($headers as $cell => $meta) {
            $sheet->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheet->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(30);

        $rowIdx = 2;
        foreach ($customers as $idx => $c) {
            $sheet->setCellValue("A{$rowIdx}", $idx + 1);
            $sheet->setCellValue("B{$rowIdx}", \App\Support\Security\CsvSanitizer::sanitize((string) $c->name));
            $sheet->setCellValue("C{$rowIdx}", $c->phone ?: '-');
            $sheet->setCellValue("D{$rowIdx}", $c->email ?: '-');
            $sheet->setCellValue("E{$rowIdx}", \App\Support\Security\CsvSanitizer::sanitize((string) ($c->company_name ?: '-')));
            $sheet->setCellValue("F{$rowIdx}", (float) ($c->credit_limit ?? 0));
            $sheet->setCellValue("G{$rowIdx}", (float) ($c->current_credit_balance ?? 0));
            $sheet->setCellValue("H{$rowIdx}", (int) ($c->points_balance ?? 0));
            $sheet->setCellValue("I{$rowIdx}", \App\Support\Security\CsvSanitizer::sanitize((string) ($c->city ?: '-')));
            $sheet->setCellValue("J{$rowIdx}", \App\Support\Security\CsvSanitizer::sanitize((string) ($c->address ?: '-')));

            $this->applyDataRowStyle($sheet, "A{$rowIdx}:J{$rowIdx}", $rowIdx % 2 === 0);
            $sheet->getRowDimension($rowIdx)->setRowHeight(22);
            $rowIdx++;
        }

        return $this->streamSpreadsheet($spreadsheet, "Export_Pelanggan_{$business->id}_" . date('Ymd_His') . ".xlsx");
    }

    /**
     * Export Suppliers to Styled Excel.
     */
    public function exportSuppliers(Business $business, $query): StreamedResponse
    {
        $suppliers = $query->withCount('materials')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar_Supplier');

        $headers = [
            'A1' => ['text' => 'No', 'width' => 8],
            'B1' => ['text' => 'Nama Supplier', 'width' => 28],
            'C1' => ['text' => 'Kode Supplier', 'width' => 18],
            'D1' => ['text' => 'Kontak PIC', 'width' => 20],
            'E1' => ['text' => 'No. Telepon / WA', 'width' => 20],
            'F1' => ['text' => 'Email', 'width' => 24],
            'G1' => ['text' => 'Termin (Hari)', 'width' => 14],
            'H1' => ['text' => 'Bank & No Rekening', 'width' => 24],
            'I1' => ['text' => 'Total Bahan Baku', 'width' => 16],
            'J1' => ['text' => 'Alamat', 'width' => 35],
        ];

        foreach ($headers as $cell => $meta) {
            $sheet->setCellValue($cell, $meta['text']);
            $colLetter = preg_replace('/[0-9]/', '', $cell);
            $sheet->getColumnDimension($colLetter)->setWidth($meta['width']);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(30);

        $rowIdx = 2;
        foreach ($suppliers as $idx => $s) {
            $bankInfo = ($s->bank_name ? "{$s->bank_name} - " : '') . ($s->bank_account_number ?: '-');
            $sheet->setCellValue("A{$rowIdx}", $idx + 1);
            $sheet->setCellValue("B{$rowIdx}", $s->name);
            $sheet->setCellValue("C{$rowIdx}", $s->code ?: '-');
            $sheet->setCellValue("D{$rowIdx}", $s->contact_person ?: '-');
            $sheet->setCellValue("E{$rowIdx}", $s->phone ?: '-');
            $sheet->setCellValue("F{$rowIdx}", $s->email ?: '-');
            $sheet->setCellValue("G{$rowIdx}", (int) ($s->payment_terms_days ?? 0));
            $sheet->setCellValue("H{$rowIdx}", $bankInfo);
            $sheet->setCellValue("I{$rowIdx}", (int) $s->materials_count);
            $sheet->setCellValue("J{$rowIdx}", $s->address ?: '-');

            $this->applyDataRowStyle($sheet, "A{$rowIdx}:J{$rowIdx}", $rowIdx % 2 === 0);
            $sheet->getRowDimension($rowIdx)->setRowHeight(22);
            $rowIdx++;
        }

        return $this->streamSpreadsheet($spreadsheet, "Export_Supplier_{$business->id}_" . date('Ymd_His') . ".xlsx");
    }

    // =========================================================================
    // 3. PARSE & PREVIEW ENGINES (NO PREVIEW, NO COMMIT)
    // =========================================================================

    /**
     * Parse and preview Customer import file.
     */
    public function parseAndPreviewCustomers(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        // Preload existing customer phones in business
        $existingCustomers = Customer::where('business_id', $business->id)->get();
        $phoneMap = [];
        $nameMap = [];
        foreach ($existingCustomers as $c) {
            if ($c->phone) {
                $cleaned = preg_replace('/[^0-9]/', '', $c->phone);
                $phoneMap[$cleaned] = $c;
            }
            $nameMap[strtolower(trim($c->name))] = $c;
        }

        $parsedRows = [];
        $seenPhones = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $name = trim((string) ($row['nama_pelanggan'] ?? $row['name'] ?? $row['nama'] ?? ''));
            $phone = trim((string) ($row['nomor_hp_whatsapp'] ?? $row['nomor_whatsapp_hp'] ?? $row['phone'] ?? $row['nomor_telepon'] ?? $row['wa'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $company = trim((string) ($row['perusahaan_instansi'] ?? $row['company_name'] ?? $row['perusahaan'] ?? ''));
            $tier = trim((string) ($row['kategori_tier_diskon'] ?? $row['tier_pelanggan'] ?? $row['tier'] ?? $row['kategori'] ?? 'Reguler'));
            $limit = (float) ($row['limit_piutang_rp'] ?? $row['limit_kredit_rp'] ?? $row['limit_kredit'] ?? $row['credit_limit'] ?? 0);
            $terms = (int) ($row['termin_pembayaran_hari'] ?? $row['syarat_bayar_top_hari'] ?? $row['syarat_bayar_top'] ?? $row['payment_terms_days'] ?? 0);
            $address = trim((string) ($row['alamat_lengkap'] ?? $row['address'] ?? ''));
            $city = trim((string) ($row['kota'] ?? $row['city'] ?? ''));
            $npwp = trim((string) ($row['npwp'] ?? ''));

            $status = 'valid';
            $statusLabel = 'Siap Impor';
            $reason = 'Data lengkap & valid.';
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            if ($name === '') {
                $status = 'error';
                $statusLabel = 'Format Salah';
                $reason = 'Nama Pelanggan wajib diisi.';
            } elseif ($phone === '') {
                $status = 'error';
                $statusLabel = 'Format Salah';
                $reason = 'Nomor HP/WhatsApp wajib diisi.';
            } elseif (strlen($cleanPhone) < 8) {
                $status = 'error';
                $statusLabel = 'Format Salah';
                $reason = "Nomor HP '{$phone}' terlalu pendek (minimal 8 digit).";
            } elseif (isset($phoneMap[$cleanPhone])) {
                $dup = $phoneMap[$cleanPhone];
                $status = 'duplicate';
                $statusLabel = 'Duplikat';
                $reason = "Nomor HP sudah terdaftar atas nama pelanggan '{$dup->name}'.";
            } elseif (isset($seenPhones[$cleanPhone])) {
                $status = 'duplicate';
                $statusLabel = 'Duplikat di File';
                $reason = 'Nomor HP sama ditemukan di baris sebelumnya dalam file ini.';
            }

            if ($status === 'valid') {
                $validCount++;
            } elseif ($status === 'duplicate') {
                $duplicateCount++;
            } else {
                $errorCount++;
            }

            $seenPhones[$cleanPhone] = true;

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => $status,
                'status_label' => $statusLabel,
                'name' => $name,
                'code' => $phone,
                'category' => $tier ?: 'Reguler',
                'company_name' => $company,
                'email' => $email,
                'phone' => $phone,
                'credit_limit' => $limit,
                'payment_terms_days' => $terms,
                'address' => $address,
                'city' => $city,
                'npwp' => $npwp,
                'formatted_price' => $limit > 0 ? 'Rp ' . number_format($limit, 0, ',', '.') : '-',
                'reason' => $reason,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'can_proceed' => ($validCount + $duplicateCount) > 0,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Customer Import to Database.
     */
    public function executeCustomerImport(Business $business, array $rows, string $duplicateStrategy = 'skip'): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, &$imported, &$updated, &$skipped): void {
            foreach ($rows as $row) {
                if (($row['status'] ?? '') === 'error') {
                    $skipped++;
                    continue;
                }

                $cleanPhone = preg_replace('/[^0-9]/', '', (string) ($row['phone'] ?? ''));
                if (empty($row['name']) || empty($cleanPhone)) {
                    $skipped++;
                    continue;
                }

                $existing = Customer::where('business_id', $business->id)
                    ->where(function ($q) use ($cleanPhone, $row): void {
                        $q->where('phone', $cleanPhone)
                            ->orWhere('phone', $row['phone'])
                            ->orWhere('name', $row['name']);
                    })
                    ->first();

                if ($existing) {
                    if ($duplicateStrategy === 'update') {
                        $existing->update([
                            'name' => $row['name'],
                            'email' => !empty($row['email']) ? $row['email'] : $existing->email,
                            'company_name' => !empty($row['company_name']) ? $row['company_name'] : $existing->company_name,
                            'credit_limit' => isset($row['credit_limit']) ? (float) $row['credit_limit'] : $existing->credit_limit,
                            'payment_terms_days' => isset($row['payment_terms_days']) ? (int) $row['payment_terms_days'] : $existing->payment_terms_days,
                            'address' => !empty($row['address']) ? $row['address'] : $existing->address,
                            'city' => !empty($row['city']) ? $row['city'] : $existing->city,
                            'tax_identification_number' => !empty($row['npwp']) ? $row['npwp'] : $existing->tax_identification_number,
                        ]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                Customer::create([
                    'business_id' => $business->id,
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'email' => !empty($row['email']) ? $row['email'] : null,
                    'company_name' => !empty($row['company_name']) ? $row['company_name'] : null,
                    'credit_limit' => (float) ($row['credit_limit'] ?? 0),
                    'payment_terms_days' => (int) ($row['payment_terms_days'] ?? 0),
                    'address' => !empty($row['address']) ? $row['address'] : null,
                    'city' => !empty($row['city']) ? $row['city'] : null,
                    'tax_identification_number' => !empty($row['npwp']) ? $row['npwp'] : null,
                ]);
                $imported++;
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => Customer::class,
                'auditable_id' => $business->id,
                'action' => 'import_customers',
                'notes' => "Import {$imported} pelanggan baru, {$updated} diperbarui, {$skipped} dilewati.",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Parse and preview Supplier import file.
     */
    public function parseAndPreviewSuppliers(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        $existingSuppliers = Supplier::where('business_id', $business->id)->get();
        $nameMap = [];
        $phoneMap = [];
        foreach ($existingSuppliers as $s) {
            $nameMap[strtolower(trim($s->name))] = $s;
            if ($s->phone) {
                $phoneMap[preg_replace('/[^0-9]/', '', $s->phone)] = $s;
            }
        }

        $parsedRows = [];
        $seenKeys = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $name = trim((string) ($row['nama_supplier_perusahaan'] ?? $row['nama_pemasok'] ?? $row['nama_supplier'] ?? $row['nama'] ?? $row['name'] ?? ''));
            $code = trim((string) ($row['kode_supplier'] ?? $row['kode_pemasok'] ?? $row['code'] ?? ''));
            $pic = trim((string) ($row['nama_kontak_pic'] ?? $row['kontak_person'] ?? $row['contact_person'] ?? $row['pic'] ?? ''));
            $phone = trim((string) ($row['nomor_telepon_wa'] ?? $row['telepon_whatsapp'] ?? $row['telepon'] ?? $row['phone'] ?? $row['nomor_telepon'] ?? $row['wa'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $top = (int) ($row['termin_pembayaran_hari'] ?? $row['syarat_pembayaran_top'] ?? $row['syarat_pembayaran'] ?? $row['termin_pembayaran'] ?? $row['payment_terms_days'] ?? $row['top'] ?? 0);
            $bankName = trim((string) ($row['nama_bank'] ?? $row['bank_name'] ?? ''));
            $bankAcc = trim((string) ($row['nomor_rekening'] ?? $row['bank_account_number'] ?? ''));
            $bankHolder = trim((string) ($row['atas_nama_rekening'] ?? $row['bank_account_holder'] ?? ''));
            $address = trim((string) ($row['alamat_kantor_gudang'] ?? $row['alamat_lengkap'] ?? $row['alamat'] ?? $row['address'] ?? ''));

            $status = 'valid';
            $statusLabel = 'Siap Impor';
            $reason = 'Data vendor lengkap & valid.';
            $cleanName = strtolower($name);
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            if ($name === '') {
                $status = 'error';
                $statusLabel = 'Format Salah';
                $reason = 'Nama Supplier / Perusahaan wajib diisi.';
            } elseif ($phone === '') {
                $status = 'error';
                $statusLabel = 'Format Salah';
                $reason = 'Nomor Telepon / WhatsApp wajib diisi.';
            } elseif (isset($nameMap[$cleanName])) {
                $status = 'duplicate';
                $statusLabel = 'Duplikat';
                $reason = "Supplier dengan nama '{$name}' sudah ada di database.";
            } elseif (isset($seenKeys[$cleanName])) {
                $status = 'duplicate';
                $statusLabel = 'Duplikat di File';
                $reason = 'Nama supplier sama ditemukan di baris sebelumnya dalam file.';
            }

            if ($status === 'valid') {
                $validCount++;
            } elseif ($status === 'duplicate') {
                $duplicateCount++;
            } else {
                $errorCount++;
            }

            $seenKeys[$cleanName] = true;

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => $status,
                'status_label' => $statusLabel,
                'name' => $name,
                'code' => $code ?: 'SUP-AUTO',
                'contact_person' => $pic,
                'phone' => $phone,
                'email' => $email,
                'payment_terms_days' => $top,
                'bank_name' => $bankName,
                'bank_account_number' => $bankAcc,
                'bank_account_holder' => $bankHolder,
                'address' => $address,
                'formatted_price' => "TOP {$top} Hari",
                'reason' => $reason,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'can_proceed' => ($validCount + $duplicateCount) > 0,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Supplier Import to Database.
     */
    public function executeSupplierImport(Business $business, array $rows, string $duplicateStrategy = 'skip'): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, &$imported, &$updated, &$skipped): void {
            foreach ($rows as $row) {
                if (($row['status'] ?? '') === 'error') {
                    $skipped++;
                    continue;
                }

                if (empty($row['name']) || empty($row['phone'])) {
                    $skipped++;
                    continue;
                }

                $existing = Supplier::where('business_id', $business->id)
                    ->where(function ($q) use ($row): void {
                        $q->where('name', $row['name']);
                        if (!empty($row['phone'])) {
                            $q->orWhere('phone', $row['phone']);
                        }
                    })
                    ->first();

                if ($existing) {
                    if ($duplicateStrategy === 'update') {
                        $existing->update([
                            'contact_person' => !empty($row['contact_person']) ? $row['contact_person'] : $existing->contact_person,
                            'phone' => !empty($row['phone']) ? $row['phone'] : $existing->phone,
                            'email' => !empty($row['email']) ? $row['email'] : $existing->email,
                            'payment_terms_days' => isset($row['payment_terms_days']) ? (int) $row['payment_terms_days'] : $existing->payment_terms_days,
                            'bank_name' => !empty($row['bank_name']) ? $row['bank_name'] : $existing->bank_name,
                            'bank_account_number' => !empty($row['bank_account_number']) ? $row['bank_account_number'] : $existing->bank_account_number,
                            'bank_account_holder' => !empty($row['bank_account_holder']) ? $row['bank_account_holder'] : $existing->bank_account_holder,
                            'address' => !empty($row['address']) ? $row['address'] : $existing->address,
                        ]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                Supplier::create([
                    'business_id' => $business->id,
                    'name' => $row['name'],
                    'code' => (!empty($row['code']) && $row['code'] !== 'SUP-AUTO') ? $row['code'] : null,
                    'contact_person' => !empty($row['contact_person']) ? $row['contact_person'] : null,
                    'phone' => $row['phone'],
                    'email' => !empty($row['email']) ? $row['email'] : null,
                    'payment_terms_days' => (int) ($row['payment_terms_days'] ?? 0),
                    'bank_name' => !empty($row['bank_name']) ? $row['bank_name'] : null,
                    'bank_account_number' => !empty($row['bank_account_number']) ? $row['bank_account_number'] : null,
                    'bank_account_holder' => !empty($row['bank_account_holder']) ? $row['bank_account_holder'] : null,
                    'address' => !empty($row['address']) ? $row['address'] : null,
                ]);
                $imported++;
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => Supplier::class,
                'auditable_id' => $business->id,
                'action' => 'import_suppliers',
                'notes' => "Import {$imported} supplier baru, {$updated} diperbarui, {$skipped} dilewati.",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

// =========================================================================
    // SPRINT 2: STOCK OPNAME (HITUNG FISIK & REKONSILIASI)
    // =========================================================================

    /**
     * Export Blank Stock Opname Sheet for physical counting.
     */
    public function exportBlankOpnameSheet(Business $business, ?string $locationId = null): StreamedResponse
    {
        $location = null;
        if ($locationId) {
            $location = Location::where('business_id', $business->id)->find($locationId);
        }
        if (! $location) {
            $location = Location::where('business_id', $business->id)->where('is_active', true)->first();
        }
        if (! $location) {
            $location = Location::where('business_id', $business->id)->first();
        }

        $locName = $location ? $location->name : 'Semua Lokasi';
        $targetLocId = $location ? $location->id : null;

        $products = Product::goods()->where('business_id', $business->id)->where('is_active', true)->with('outputUnit')->get();
        $materials = Material::where('business_id', $business->id)->whereNull('discontinued_at')->with('unit')->get();

        $stocksQuery = InventoryStock::where('business_id', $business->id);
        if ($targetLocId) {
            $stocksQuery->where('location_id', $targetLocId);
        }
        $stocks = $stocksQuery->get();

        $stockMap = [];
        foreach ($stocks as $s) {
            if ($s->product_id) {
                $stockMap['product_' . $s->product_id] = (float) $s->quantity;
            }
            if ($s->material_id) {
                $stockMap['material_' . $s->material_id] = (float) $s->quantity;
            }
        }

        $spreadsheet = new Spreadsheet();

        // SHEET 1: LEMBAR_HITUNG_FISIK
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('LEMBAR_HITUNG_FISIK');

        $headers = [
            'A1' => 'No',
            'B1' => 'Tipe Item',
            'C1' => 'Kode SKU / Unik',
            'D1' => 'Nama Barang / Bahan Baku',
            'E1' => 'Satuan',
            'F1' => 'Stok Sistem (Info)',
            'G1' => 'HASIL HITUNG FISIK *',
            'H1' => 'Catatan Kondisi / Kerusakan',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $bgColor = ($cell === 'G1') ? self::COLOR_WARNING : self::COLOR_PRIMARY;
            $this->applyHeaderStyle($sheet, $cell, $bgColor);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        $counter = 1;

        // Write Products
        foreach ($products as $p) {
            $systemQty = $stockMap['product_' . $p->id] ?? 0.0;
            $sheet->setCellValue("A{$rowNum}", $counter++);
            $sheet->setCellValue("B{$rowNum}", 'Produk Jadi');
            $sheet->setCellValue("C{$rowNum}", $p->code ?: '-');
            $sheet->setCellValue("D{$rowNum}", $p->name);
            $sheet->setCellValue("E{$rowNum}", $p->outputUnit?->name ?: 'pcs');
            $sheet->setCellValue("F{$rowNum}", $systemQty);
            $sheet->setCellValue("G{$rowNum}", ''); // Blank for counting
            $sheet->setCellValue("H{$rowNum}", '');

            $this->applyDataRowStyle($sheet, "A{$rowNum}:H{$rowNum}", $rowNum % 2 === 0);
            $sheet->getStyle("F{$rowNum}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("G{$rowNum}")->getNumberFormat()->setFormatCode('#,##0.00');
            $rowNum++;
        }

        // Write Materials
        foreach ($materials as $m) {
            $systemQty = $stockMap['material_' . $m->id] ?? 0.0;
            $sheet->setCellValue("A{$rowNum}", $counter++);
            $sheet->setCellValue("B{$rowNum}", 'Bahan Baku');
            $sheet->setCellValue("C{$rowNum}", $m->code);
            $sheet->setCellValue("D{$rowNum}", $m->name);
            $sheet->setCellValue("E{$rowNum}", $m->unit?->name ?: 'unit');
            $sheet->setCellValue("F{$rowNum}", $systemQty);
            $sheet->setCellValue("G{$rowNum}", ''); // Blank for counting
            $sheet->setCellValue("H{$rowNum}", '');

            $this->applyDataRowStyle($sheet, "A{$rowNum}:H{$rowNum}", $rowNum % 2 === 0);
            $sheet->getStyle("F{$rowNum}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("G{$rowNum}")->getNumberFormat()->setFormatCode('#,##0.00');
            $rowNum++;
        }

        foreach (['A' => 6, 'B' => 14, 'C' => 18, 'D' => 32, 'E' => 12, 'F' => 18, 'G' => 24, 'H' => 28] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        // SHEET 2: PANDUAN_OPNAME
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_OPNAME', [
            ['Kolom Kuning (HASIL HITUNG FISIK)', 'Wajib diisi oleh staf penghitung fisik di gudang/toko dengan jumlah riil barang di rak.'],
            ['Kolom Abu-abu (Stok Sistem)', 'Sebagai pembanding jumlah yang tercatat di sistem COOCA saat lembar ini diunduh.'],
            ['Baris Kosong', 'Jika barang tidak dihitung atau tidak ada perubahan, kolom hasil fisik boleh dikosongkan.'],
            ['Upload Kembali', 'Setelah selesai menghitung, simpan file ini lalu unggah ke menu Stock Opname -> Import Hitungan Fisik.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        $safeLocName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $locName);
        return $this->streamSpreadsheet($spreadsheet, "Lembar_Opname_{$safeLocName}_{$business->id}.xlsx");
    }

    /**
     * Parse and preview Stock Opname physical counts.
     */
    public function parseAndPreviewOpname(Business $business, UploadedFile $file, string $locationId): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'match_count' => 0,
                'surplus_count' => 0,
                'deficit_count' => 0,
                'error_count' => 0,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        $products = Product::where('business_id', $business->id)->where('type', '!=', Product::TYPE_SERVICE)->get();
        $materials = Material::where('business_id', $business->id)->get();

        $prodMap = [];
        foreach ($products as $p) {
            if ($p->code) $prodMap[strtolower(trim($p->code))] = $p;
            if ($p->sku) $prodMap[strtolower(trim($p->sku))] = $p;
            $prodMap[strtolower(trim($p->name))] = $p;
        }

        $matMap = [];
        foreach ($materials as $m) {
            if ($m->code) $matMap[strtolower(trim($m->code))] = $m;
            $matMap[strtolower(trim($m->name))] = $m;
        }

        $stocks = InventoryStock::where('business_id', $business->id)
            ->where('location_id', $locationId)
            ->get();
        $stockMap = [];
        foreach ($stocks as $s) {
            if ($s->product_id) $stockMap['product_' . $s->product_id] = (float) $s->quantity;
            if ($s->material_id) $stockMap['material_' . $s->material_id] = (float) $s->quantity;
        }

        $parsedRows = [];
        $validCount = 0;
        $matchCount = 0;
        $surplusCount = 0;
        $deficitCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $sku = trim((string) ($row['kode_sku_unik'] ?? $row['kode_sku'] ?? $row['kode'] ?? $row['sku'] ?? ''));
            $name = trim((string) ($row['nama_barang_bahan_baku'] ?? $row['nama_item'] ?? $row['nama_barang'] ?? $row['nama'] ?? ''));
            $type = trim((string) ($row['tipe_item'] ?? $row['tipe_bahan_produk'] ?? $row['tipe'] ?? ''));
            $uom = trim((string) ($row['satuan'] ?? $row['uom'] ?? ''));
            $physicalRaw = $row['hasil_hitung_fisik'] ?? $row['hitungan_fisik_isi_di_sini'] ?? $row['hitungan_fisik'] ?? $row['hitung_fisik'] ?? $row['fisik'] ?? null;
            $notes = trim((string) ($row['catatan_kondisi_kerusakan'] ?? $row['catatan_auditor'] ?? $row['catatan'] ?? ''));

            if ($physicalRaw === null || $physicalRaw === '') {
                // If physical count is omitted, skip row from variance processing
                continue;
            }

            if (! is_numeric($physicalRaw) || (float) $physicalRaw < 0) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Nilai Negatif / Salah',
                    'name' => $name ?: ($sku ?: 'Baris ' . $rowNumber),
                    'code' => $sku ?: '-',
                    'category' => $type ?: 'Unknown',
                    'system_quantity' => 0,
                    'physical_quantity' => 0,
                    'variance' => 0,
                    'variance_type' => 'error',
                    'formatted_price' => 'Fisik: ' . $physicalRaw,
                    'reason' => 'Hasil hitung fisik harus berupa angka lebih besar atau sama dengan 0.',
                ];
                continue;
            }

            $physicalQty = (float) $physicalRaw;

            // Resolve item
            $resolvedProduct = null;
            $resolvedMaterial = null;

            if ($sku !== '') {
                $resolvedProduct = $prodMap[strtolower($sku)] ?? null;
                $resolvedMaterial = $matMap[strtolower($sku)] ?? null;
            }
            if (! $resolvedProduct && ! $resolvedMaterial && $name !== '') {
                $resolvedProduct = $prodMap[strtolower($name)] ?? null;
                $resolvedMaterial = $matMap[strtolower($name)] ?? null;
            }

            if (! $resolvedProduct && ! $resolvedMaterial) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Item Tidak Dikenal',
                    'name' => $name ?: 'Barang Tidak Diketahui',
                    'code' => $sku ?: '-',
                    'category' => $type ?: 'Unknown',
                    'system_quantity' => 0,
                    'physical_quantity' => $physicalQty,
                    'variance' => 0,
                    'variance_type' => 'error',
                    'formatted_price' => "Fisik: {$physicalQty}",
                    'reason' => "SKU / Nama '{$sku}' tidak ditemukan di database produk atau bahan baku.",
                ];
                continue;
            }

            $systemQty = 0.0;
            $itemId = null;
            $itemType = '';
            $itemName = '';
            $itemCode = '';

            if ($resolvedProduct) {
                $itemId = $resolvedProduct->id;
                $itemType = 'product';
                $itemName = $resolvedProduct->name;
                $itemCode = $resolvedProduct->code ?: ($resolvedProduct->sku ?: '-');
                $systemQty = $stockMap['product_' . $resolvedProduct->id] ?? 0.0;
            } else {
                $itemId = $resolvedMaterial->id;
                $itemType = 'material';
                $itemName = $resolvedMaterial->name;
                $itemCode = $resolvedMaterial->code ?: '-';
                $systemQty = $stockMap['material_' . $resolvedMaterial->id] ?? 0.0;
            }

            $variance = round($physicalQty - $systemQty, 4);

            if ($variance == 0) {
                $matchCount++;
                $varType = 'match';
                $statusLabel = 'Cocok (0)';
                $reason = 'Stok fisik sesuai dengan catatan sistem.';
            } elseif ($variance > 0) {
                $surplusCount++;
                $varType = 'surplus';
                $statusLabel = 'Surplus (+' . $variance . ')';
                $reason = "Ditemukan surplus fisik sebanyak +{$variance} {$uom}.";
            } else {
                $deficitCount++;
                $varType = 'deficit';
                $statusLabel = 'Defisit (' . $variance . ')';
                $reason = "Selisih kurang / defisit fisik sebanyak {$variance} {$uom}.";
            }

            $validCount++;
            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => 'valid',
                'status_label' => $statusLabel,
                'product_id' => ($itemType === 'product') ? $itemId : null,
                'material_id' => ($itemType === 'material') ? $itemId : null,
                'item_type' => $itemType,
                'name' => $itemName,
                'code' => $itemCode,
                'category' => ($itemType === 'product') ? 'Produk Jadi' : 'Bahan Baku',
                'uom' => $uom,
                'system_quantity' => $systemQty,
                'physical_quantity' => $physicalQty,
                'variance' => $variance,
                'variance_type' => $varType,
                'formatted_price' => "Fisik: {$physicalQty} (Sys: {$systemQty})",
                'notes' => $notes,
                'reason' => $reason,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'match_count' => $matchCount,
            'surplus_count' => $surplusCount,
            'deficit_count' => $deficitCount,
            'error_count' => $errorCount,
            'can_proceed' => $validCount > 0 && $errorCount === 0,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Stock Opname Import to database.
     */
    public function executeOpnameImport(Business $business, array $rows, string $locationId, ?string $notes = null): array
    {
        $validRows = array_filter($rows, fn ($r) => ($r['status'] ?? '') === 'valid');
        if (empty($validRows)) {
            return [
                'success' => false,
                'message' => 'Tidak ada baris valid untuk diimpor.',
            ];
        }

        return DB::transaction(function () use ($business, $validRows, $locationId, $notes): array {
            $user = auth()->user() ?? User::where('business_id', $business->id)->first();
            $opnameNumber = DocumentNumberGenerator::generateStockOpnameNumber($business->id);

            $opname = StockOpname::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'opname_number' => $opnameNumber,
                'opname_date' => Carbon::now()->toDateString(),
                'status' => StockOpname::STATUS_COMPLETED,
                'notes' => $notes ?: 'Import lembar hitung fisik Excel.',
                'conducted_by' => $user?->id,
            ]);

            $createdCount = 0;
            foreach ($validRows as $row) {
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $row['product_id'] ?? null,
                    'material_id' => $row['material_id'] ?? null,
                    'system_quantity' => (float) ($row['system_quantity'] ?? 0),
                    'physical_quantity' => (float) ($row['physical_quantity'] ?? 0),
                    'difference_quantity' => (float) ($row['variance'] ?? 0),
                    'notes' => $row['notes'] ?? null,
                ]);
                $createdCount++;
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'auditable_type' => StockOpname::class,
                'auditable_id' => $opname->id,
                'action' => 'import_stock_opname',
                'notes' => "Import {$createdCount} item pada Stock Opname #{$opnameNumber}.",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return [
                'success' => true,
                'opname_id' => $opname->id,
                'opname_number' => $opnameNumber,
                'total_items' => $createdCount,
            ];
        });
    }

    // =========================================================================
    // SPRINT 2: CHART OF ACCOUNTS (BAGAN AKUN / COA)
    // =========================================================================

    /**
     * Generate Chart of Accounts Template with Categories & Rules.
     */
    public function generateCoaTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: INPUT_COA
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_COA');

        $headers = [
            'A1' => 'Kode Akun *',
            'B1' => 'Nama Akun *',
            'C1' => 'Klasifikasi Akun *',
            'D1' => 'Kode Akun Induk (Parent)',
            'E1' => 'Posisi Normal *',
            'F1' => 'Deskripsi / Catatan',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $bgColor = in_array($cell, ['A1', 'B1', 'C1', 'E1']) ? self::COLOR_PRIMARY : self::COLOR_MUTED;
            $this->applyHeaderStyle($sheet, $cell, $bgColor);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Sample Row
        $sampleRow = [
            'A2' => '1111',
            'B2' => 'Kas Kecil Operasional',
            'C2' => 'Aset Lancar',
            'D2' => '1110',
            'E2' => 'Debit',
            'F2' => 'Kas kecil untuk operasional toko harian',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '64748B'], 'size' => 10, 'name' => 'Segoe UI'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER_COLOR]]],
            ]);
        }
        $sheet->getRowDimension(2)->setRowHeight(24);

        // SHEET 2: DATA_REFERENSI
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');

        $categories = [
            'Aset Lancar',
            'Aset Tidak Lancar',
            'Kewajiban Jangka Pendek',
            'Kewajiban Jangka Panjang',
            'Ekuitas / Modal',
            'Pendapatan',
            'Beban Pokok Penjualan (HPP)',
            'Beban Operasional',
            'Pendapatan / Beban Lainnya',
        ];

        $normalBalances = ['Debit', 'Kredit'];

        $parents = ChartOfAccount::where('business_id', $business->id)
            ->whereNull('parent_id')
            ->pluck('code')
            ->toArray();
        if (empty($parents)) {
            $parents = ['1100', '1200', '2100', '3100', '4100', '5100', '6100'];
        }

        $refCols = [
            'A' => ['title' => 'Klasifikasi Akun', 'data' => $categories, 'named' => 'RefKlasifikasiAkun'],
            'B' => ['title' => 'Posisi Normal', 'data' => $normalBalances, 'named' => 'RefPosisiNormal'],
            'C' => ['title' => 'Daftar Akun Induk', 'data' => $parents, 'named' => 'RefAkunInduk'],
        ];

        foreach ($refCols as $col => $info) {
            $sheetRef->setCellValue("{$col}1", $info['title']);
            $this->applyHeaderStyle($sheetRef, "{$col}1", self::COLOR_PRIMARY);
            $sheetRef->getColumnDimension($col)->setWidth(30);

            $r = 2;
            foreach ($info['data'] as $item) {
                $sheetRef->setCellValue("{$col}{$r}", $item);
                $r++;
            }
            $endRow = max(2, $r - 1);
            $spreadsheet->addNamedRange(new NamedRange($info['named'], $sheetRef, "\${$col}\$2:\${$col}\${$endRow}"));
        }

        // Apply validations
        $this->applyDropdownValidation($sheet, 'C3:C1000', '=RefKlasifikasiAkun', 'Pilih Klasifikasi Akun');
        $this->applyDropdownValidation($sheet, 'E3:E1000', '=RefPosisiNormal', 'Pilih Posisi Normal');

        foreach (['A' => 16, 'B' => 32, 'C' => 26, 'D' => 22, 'E' => 16, 'F' => 36] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        // SHEET 3: PANDUAN_COA
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_COA', [
            ['Kode Akun', 'Kode unik akun (misal: 1111, 2110). Kepala 1 = Aset, 2 = Kewajiban, 3 = Ekuitas, 4 = Pendapatan, 5 = HPP, 6 = Beban.'],
            ['Klasifikasi Akun', 'Pilih dari dropdown: Aset Lancar, Kewajiban, Ekuitas, Pendapatan, HPP, atau Beban Operasional.'],
            ['Kode Akun Induk', 'Kosongkan jika akun ini adalah akun level tertinggi (root). Jika merupakan sub-akun, isi dengan kode akun induknya.'],
            ['Posisi Normal', 'Pilih Debit untuk Akun Aset dan Beban. Pilih Kredit untuk Akun Kewajiban, Ekuitas, dan Pendapatan.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Import_COA_{$business->id}.xlsx");
    }

    /**
     * Export tenant Chart of Accounts to Excel.
     */
    public function exportCoa(Business $business): StreamedResponse
    {
        $accounts = ChartOfAccount::where('business_id', $business->id)
            ->with('parent')
            ->orderBy('code')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BAGAN_AKUN');

        $headers = [
            'A1' => 'Kode Akun',
            'B1' => 'Nama Akun',
            'C1' => 'Klasifikasi',
            'D1' => 'Akun Induk (Parent)',
            'E1' => 'Posisi Normal',
            'F1' => 'Tipe Akun',
            'G1' => 'Status',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        foreach ($accounts as $acc) {
            $sheet->setCellValue("A{$rowNum}", CsvSanitizer::sanitize($acc->code));
            $sheet->setCellValue("B{$rowNum}", CsvSanitizer::sanitize(($acc->parent_id ? '  ' : '') . $acc->name));
            $sheet->setCellValue("C{$rowNum}", $acc->getTypeLabel());
            $sheet->setCellValue("D{$rowNum}", $acc->parent ? CsvSanitizer::sanitize("[{$acc->parent->code}] {$acc->parent->name}") : '-');
            $sheet->setCellValue("E{$rowNum}", strtoupper($acc->normal_balance));
            $sheet->setCellValue("F{$rowNum}", $acc->is_system ? 'Sistem' : 'Kustom');
            $sheet->setCellValue("G{$rowNum}", $acc->is_active ? 'Aktif' : 'Non-Aktif');

            $this->applyDataRowStyle($sheet, "A{$rowNum}:G{$rowNum}", $rowNum % 2 === 0);
            $rowNum++;
        }

        foreach (['A' => 16, 'B' => 32, 'C' => 24, 'D' => 28, 'E' => 16, 'F' => 14, 'G' => 14] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        return $this->streamSpreadsheet($spreadsheet, "Bagan_Akun_{$business->id}.xlsx");
    }

    /**
     * Parse and preview Chart of Accounts import.
     */
    public function parseAndPreviewCoa(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        $existing = ChartOfAccount::where('business_id', $business->id)->get();
        $codeMap = [];
        foreach ($existing as $acc) {
            $codeMap[strtolower(trim($acc->code))] = $acc;
        }

        $typeMapping = [
            'aset lancar' => ChartOfAccount::TYPE_ASSET,
            'aset tidak lancar' => ChartOfAccount::TYPE_ASSET,
            'aset / aktiva' => ChartOfAccount::TYPE_ASSET,
            'aset' => ChartOfAccount::TYPE_ASSET,
            'aktiva' => ChartOfAccount::TYPE_ASSET,
            'kewajiban jangka pendek' => ChartOfAccount::TYPE_LIABILITY,
            'kewajiban jangka panjang' => ChartOfAccount::TYPE_LIABILITY,
            'kewajiban / hutang' => ChartOfAccount::TYPE_LIABILITY,
            'kewajiban' => ChartOfAccount::TYPE_LIABILITY,
            'hutang' => ChartOfAccount::TYPE_LIABILITY,
            'ekuitas / modal' => ChartOfAccount::TYPE_EQUITY,
            'ekuitas' => ChartOfAccount::TYPE_EQUITY,
            'modal' => ChartOfAccount::TYPE_EQUITY,
            'pendapatan' => ChartOfAccount::TYPE_REVENUE,
            'beban pokok penjualan (hpp)' => ChartOfAccount::TYPE_COGS,
            'beban pokok penjualan' => ChartOfAccount::TYPE_COGS,
            'hpp' => ChartOfAccount::TYPE_COGS,
            'beban operasional' => ChartOfAccount::TYPE_EXPENSE,
            'beban' => ChartOfAccount::TYPE_EXPENSE,
            'pendapatan / beban lainnya' => ChartOfAccount::TYPE_EXPENSE,
        ];

        $parsedRows = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $code = trim((string) ($row['kode_akun'] ?? $row['kode'] ?? ''));
            $name = trim((string) ($row['nama_akun'] ?? $row['nama'] ?? ''));
            $catRaw = strtolower(trim((string) ($row['klasifikasi_akun'] ?? $row['kategori_akun'] ?? $row['klasifikasi'] ?? $row['kategori'] ?? '')));
            $parentCode = trim((string) ($row['kode_akun_induk_parent'] ?? $row['kode_induk_parent'] ?? $row['kode_akun_induk'] ?? $row['kode_induk'] ?? $row['induk'] ?? $row['parent_code'] ?? $row['parent'] ?? ''));
            $normRaw = strtolower(trim((string) ($row['posisi_normal'] ?? $row['normal_balance'] ?? '')));
            $desc = trim((string) ($row['deskripsi_catatan'] ?? $row['deskripsi'] ?? ''));

            if (empty($code) || empty($name)) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Wajib Diisi',
                    'name' => $name ?: 'Tanpa Nama',
                    'code' => $code ?: '-',
                    'category' => $catRaw ?: '-',
                    'formatted_price' => '-',
                    'reason' => 'Kode Akun dan Nama Akun wajib diisi.',
                ];
                continue;
            }

            $mappedType = $typeMapping[$catRaw] ?? null;
            if (! $mappedType) {
                // Infer from code first digit if possible
                $firstChar = substr($code, 0, 1);
                $mappedType = match ($firstChar) {
                    '1' => ChartOfAccount::TYPE_ASSET,
                    '2' => ChartOfAccount::TYPE_LIABILITY,
                    '3' => ChartOfAccount::TYPE_EQUITY,
                    '4' => ChartOfAccount::TYPE_REVENUE,
                    '5' => ChartOfAccount::TYPE_COGS,
                    '6', '7', '8', '9' => ChartOfAccount::TYPE_EXPENSE,
                    default => ChartOfAccount::TYPE_ASSET,
                };
            }

            $normalBalance = in_array($normRaw, ['kredit', 'credit']) ? 'credit' : 'debit';
            if (empty($normRaw)) {
                $normalBalance = in_array($mappedType, [ChartOfAccount::TYPE_LIABILITY, ChartOfAccount::TYPE_EQUITY, ChartOfAccount::TYPE_REVENUE]) ? 'credit' : 'debit';
            }

            $isDuplicate = isset($codeMap[strtolower($code)]);

            if ($isDuplicate) {
                $duplicateCount++;
                $status = 'duplicate';
                $statusLabel = 'Duplikat (Ada)';
                $reason = "Kode akun '{$code}' sudah ada di database. Pilih opsi perbarui data jika ingin mengupdate nama.";
            } else {
                $validCount++;
                $status = 'valid';
                $statusLabel = 'Siap Impor';
                $reason = 'Data COA valid.';
            }

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => $status,
                'status_label' => $statusLabel,
                'code' => $code,
                'name' => $name,
                'type' => $mappedType,
                'parent_code' => $parentCode ?: null,
                'normal_balance' => $normalBalance,
                'category' => ucfirst($mappedType),
                'formatted_price' => strtoupper($normalBalance),
                'description' => $desc,
                'reason' => $reason,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'can_proceed' => ($validCount + $duplicateCount) > 0,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Chart of Accounts Import.
     */
    public function executeCoaImport(Business $business, array $rows, string $duplicateStrategy = 'skip'): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, &$imported, &$updated, &$skipped): void {
            // First pass: insert accounts without parents or roots
            foreach ($rows as $row) {
                if (($row['status'] ?? '') === 'error' || empty($row['code']) || empty($row['name'])) {
                    $skipped++;
                    continue;
                }

                $existing = ChartOfAccount::where('business_id', $business->id)
                    ->where('code', $row['code'])
                    ->first();

                if ($existing) {
                    if ($duplicateStrategy === 'update') {
                        $existing->update([
                            'name' => $row['name'],
                            'type' => $row['type'] ?? $existing->type,
                            'normal_balance' => $row['normal_balance'] ?? $existing->normal_balance,
                        ]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                ChartOfAccount::create([
                    'business_id' => $business->id,
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'type' => $row['type'] ?? ChartOfAccount::TYPE_ASSET,
                    'normal_balance' => $row['normal_balance'] ?? 'debit',
                    'is_system' => false,
                    'is_active' => true,
                ]);
                $imported++;
            }

            // Second pass: resolve parent_id if parent_code was provided
            foreach ($rows as $row) {
                if (! empty($row['parent_code']) && ! empty($row['code'])) {
                    $parent = ChartOfAccount::where('business_id', $business->id)->where('code', $row['parent_code'])->first();
                    if ($parent) {
                        ChartOfAccount::where('business_id', $business->id)
                            ->where('code', $row['code'])
                            ->update(['parent_id' => $parent->id]);
                    }
                }
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => ChartOfAccount::class,
                'auditable_id' => $business->id,
                'action' => 'import_coa',
                'notes' => "Import {$imported} akun COA baru, {$updated} diperbarui, {$skipped} dilewati.",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    // =========================================================================
    // SPRINT 2: SALDO AWAL NERACA (OPENING BALANCE SHEET JOURNAL)
    // =========================================================================

    /**
     * Generate Opening Balance Sheet Template with pre-filled tenant accounts.
     */
    public function generateOpeningBalanceTemplate(Business $business): StreamedResponse
    {
        $accounts = ChartOfAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $spreadsheet = new Spreadsheet();

        // SHEET 1: SALDO_AWAL_NERACA
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SALDO_AWAL_NERACA');

        $headers = [
            'A1' => 'Kode Akun *',
            'B1' => 'Nama Akun',
            'C1' => 'Klasifikasi',
            'D1' => 'Posisi Normal',
            'E1' => 'Saldo Debet (Rp)',
            'F1' => 'Saldo Kredit (Rp)',
            'G1' => 'Catatan / Memo',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $bgColor = in_array($cell, ['E1', 'F1']) ? self::COLOR_WARNING : self::COLOR_PRIMARY;
            $this->applyHeaderStyle($sheet, $cell, $bgColor);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        foreach ($accounts as $acc) {
            $sheet->setCellValue("A{$rowNum}", $acc->code);
            $sheet->setCellValue("B{$rowNum}", $acc->name);
            $sheet->setCellValue("C{$rowNum}", $acc->getTypeLabel());
            $sheet->setCellValue("D{$rowNum}", strtoupper($acc->normal_balance));
            $sheet->setCellValue("E{$rowNum}", 0);
            $sheet->setCellValue("F{$rowNum}", 0);
            $sheet->setCellValue("G{$rowNum}", '');

            $this->applyDataRowStyle($sheet, "A{$rowNum}:G{$rowNum}", $rowNum % 2 === 0);
            $sheet->getStyle("E{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $rowNum++;
        }

        foreach (['A' => 16, 'B' => 32, 'C' => 24, 'D' => 16, 'E' => 20, 'F' => 20, 'G' => 32] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        // SHEET 2: PANDUAN_NERACA
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_NERACA', [
            ['Prinsip Keseimbangan (Balancing)', 'Total kolom Saldo Debet WAJIB sama dengan Total kolom Saldo Kredit (Debet = Kredit).'],
            ['Satu Kolom per Baris', 'Untuk setiap akun, isi hanya SALAH SATU antara kolom Debet ATAU Kredit sesuai saldo cut-off riil Anda.'],
            ['Akun Bersaldo Nol', 'Biarkan nilai 0 atau kosong jika akun tersebut belum memiliki saldo saat tanggal cut-off migrasi.'],
            ['Tanggal Jurnal', 'Saat mengunggah kembali ke sistem, tentukan tanggal cut-off neraca awal (misal: 31 Desember atau 1 Januari).'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Saldo_Awal_Neraca_{$business->id}.xlsx");
    }

    /**
     * Parse and preview Opening Balance Sheet import with live balance check.
     */
    public function parseAndPreviewOpeningBalance(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'error_count' => 0,
                'total_debit' => 0,
                'total_credit' => 0,
                'difference' => 0,
                'is_balanced' => false,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        $accounts = ChartOfAccount::where('business_id', $business->id)->get();
        $accMap = [];
        foreach ($accounts as $acc) {
            $accMap[strtolower(trim($acc->code))] = $acc;
        }

        $parsedRows = [];
        $validCount = 0;
        $errorCount = 0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $code = trim((string) ($row['kode_akun'] ?? $row['kode'] ?? ''));
            $name = trim((string) ($row['nama_akun'] ?? $row['nama'] ?? ''));
            $debitRaw = $row['saldo_debet_rp'] ?? $row['debet_rp'] ?? $row['debit_rp'] ?? $row['saldo_debet'] ?? $row['debet'] ?? $row['debit'] ?? 0;
            $creditRaw = $row['saldo_kredit_rp'] ?? $row['kredit_rp'] ?? $row['credit_rp'] ?? $row['saldo_kredit'] ?? $row['kredit'] ?? $row['credit'] ?? 0;
            $notes = trim((string) ($row['catatan_memo'] ?? $row['catatan'] ?? $row['memo'] ?? ''));

            $debit = is_numeric($debitRaw) ? (float) $debitRaw : 0.0;
            $credit = is_numeric($creditRaw) ? (float) $creditRaw : 0.0;

            if ($debit == 0 && $credit == 0) {
                // Skip zero-balance accounts
                continue;
            }

            if ($debit < 0 || $credit < 0) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Nilai Negatif',
                    'name' => $name ?: $code,
                    'code' => $code,
                    'category' => '-',
                    'formatted_price' => "D: {$debit} | K: {$credit}",
                    'reason' => 'Saldo tidak boleh bernilai negatif.',
                ];
                continue;
            }

            if ($debit > 0 && $credit > 0) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Debet & Kredit Terisi',
                    'name' => $name ?: $code,
                    'code' => $code,
                    'category' => '-',
                    'formatted_price' => "D: {$debit} | K: {$credit}",
                    'reason' => 'Pilih salah satu posisi: Debet ATAU Kredit, tidak boleh keduanya terisi bersamaan.',
                ];
                continue;
            }

            $account = $accMap[strtolower($code)] ?? null;
            if (! $account) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Akun Tidak Ditemukan',
                    'name' => $name ?: 'Unknown',
                    'code' => $code,
                    'category' => '-',
                    'formatted_price' => $debit > 0 ? "D: Rp " . number_format($debit, 0, ',', '.') : "K: Rp " . number_format($credit, 0, ',', '.'),
                    'reason' => "Kode akun '{$code}' tidak terdaftar di Bagan Akun (COA).",
                ];
                continue;
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
            $validCount++;

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => 'valid',
                'status_label' => $debit > 0 ? 'Debet' : 'Kredit',
                'account_id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'category' => $account->getTypeLabel(),
                'debit' => $debit,
                'credit' => $credit,
                'formatted_price' => $debit > 0 ? 'D: Rp ' . number_format($debit, 0, ',', '.') : 'K: Rp ' . number_format($credit, 0, ',', '.'),
                'notes' => $notes,
                'reason' => 'Akun valid terpetakan.',
            ];
        }

        $diff = round(abs($totalDebit - $totalCredit), 2);
        $isBalanced = ($diff < 0.01 && ($totalDebit > 0 || $totalCredit > 0));

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => $diff,
            'is_balanced' => $isBalanced,
            'can_proceed' => $validCount > 0 && $errorCount === 0 && $isBalanced,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Opening Balance Sheet Journal Import.
     */
    public function executeOpeningBalanceImport(Business $business, array $rows, ?string $entryDate = null, ?string $description = null): array
    {
        $validRows = array_filter($rows, fn ($r) => ($r['status'] ?? '') === 'valid');
        if (empty($validRows)) {
            return ['success' => false, 'message' => 'Tidak ada baris saldo valid.'];
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($validRows as $r) {
            $totalDebit += (float) ($r['debit'] ?? 0);
            $totalCredit += (float) ($r['credit'] ?? 0);
        }

        if (round(abs($totalDebit - $totalCredit), 2) > 0.01) {
            return [
                'success' => false,
                'message' => 'Jurnal tidak seimbang! Total Debet (Rp ' . number_format($totalDebit, 0, ',', '.') . ') tidak sama dengan Total Kredit (Rp ' . number_format($totalCredit, 0, ',', '.') . ').',
            ];
        }

        return DB::transaction(function () use ($business, $validRows, $totalDebit, $totalCredit, $entryDate, $description): array {
            $user = auth()->user() ?? User::where('business_id', $business->id)->first();
            $entryNumber = 'OB-' . date('Ymd') . '-' . rand(1000, 9999);

            $entry = JournalEntry::create([
                'business_id' => $business->id,
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate ?: Carbon::now()->toDateString(),
                'reference_type' => JournalEntry::REF_MANUAL,
                'description' => $description ?: 'Saldo Awal Neraca Cut-Off Migrasi Sistem',
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'created_by' => $user?->id,
            ]);

            $linesCount = 0;
            foreach ($validRows as $r) {
                $accId = $r['account_id'] ?? null;
                if (! $accId && ! empty($r['code'])) {
                    $acc = ChartOfAccount::where('business_id', $business->id)->where('code', $r['code'])->first();
                    $accId = $acc?->id;
                }
                if (! $accId) continue;

                $debit = (float) ($r['debit'] ?? 0);
                $credit = (float) ($r['credit'] ?? 0);

                if ($debit > 0) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $accId,
                        'type' => JournalEntryLine::TYPE_DEBIT,
                        'amount' => $debit,
                        'notes' => $r['notes'] ?? 'Saldo Awal Debet',
                    ]);
                    $linesCount++;
                } elseif ($credit > 0) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $accId,
                        'type' => JournalEntryLine::TYPE_CREDIT,
                        'amount' => $credit,
                        'notes' => $r['notes'] ?? 'Saldo Awal Kredit',
                    ]);
                    $linesCount++;
                }
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'auditable_type' => JournalEntry::class,
                'auditable_id' => $entry->id,
                'action' => 'import_opening_balance',
                'notes' => "Jurnal Saldo Awal #{$entryNumber} berhasil dibuat dengan {$linesCount} baris (Total: Rp " . number_format($totalDebit, 0, ',', '.') . ").",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return [
                'success' => true,
                'entry_id' => $entry->id,
                'entry_number' => $entryNumber,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'lines_count' => $linesCount,
            ];
        });
    }

    // =========================================================================
    // SPRINT 2: SALDO AWAL PIUTANG (AR) & HUTANG (AP)
    // =========================================================================

    /**
     * Generate Receivables (AR) Template.
     */
    public function generateReceivablesTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: INPUT_PIUTANG
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_PIUTANG');

        $headers = [
            'A1' => 'No. Faktur / Invoice *',
            'B1' => 'Nama Pelanggan *',
            'C1' => 'Tanggal Faktur * (YYYY-MM-DD)',
            'D1' => 'Jatuh Tempo * (YYYY-MM-DD)',
            'E1' => 'Total Tagihan (Rp) *',
            'F1' => 'Sudah Dibayar (Rp)',
            'G1' => 'Sisa Piutang (Rp)',
            'H1' => 'Catatan / Memo',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $bgColor = in_array($cell, ['A1', 'B1', 'C1', 'D1', 'E1']) ? self::COLOR_PRIMARY : self::COLOR_MUTED;
            $this->applyHeaderStyle($sheet, $cell, $bgColor);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Sample row
        $sampleRow = [
            'A2' => 'INV-2026-0001',
            'B2' => 'Toko Sembako Berkah',
            'C2' => date('Y-m-d', strtotime('-15 days')),
            'D2' => date('Y-m-d', strtotime('+15 days')),
            'E2' => 5000000,
            'F2' => 2000000,
            'G2' => 3000000,
            'H2' => 'Saldo piutang faktur lama cut-off migrasi',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '64748B'], 'size' => 10, 'name' => 'Segoe UI'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER_COLOR]]],
            ]);
        }
        $sheet->getRowDimension(2)->setRowHeight(24);

        // SHEET 2: DATA_REFERENSI
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');

        $customers = Customer::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($customers)) $customers = ['Pelanggan Umum', 'Toko Berkah'];

        $sheetRef->setCellValue('A1', 'Daftar Pelanggan Aktif');
        $this->applyHeaderStyle($sheetRef, 'A1', self::COLOR_PRIMARY);
        $sheetRef->getColumnDimension('A')->setWidth(35);
        $r = 2;
        foreach ($customers as $c) {
            $sheetRef->setCellValue("A{$r}", $c);
            $r++;
        }
        $endRow = max(2, $r - 1);
        $spreadsheet->addNamedRange(new NamedRange('RefPelangganPiutang', $sheetRef, "\$A\$2:\$A\${$endRow}"));

        $this->applyDropdownValidation($sheet, 'B3:B1000', '=RefPelangganPiutang', 'Pilih Pelanggan');

        foreach (['A' => 22, 'B' => 32, 'C' => 22, 'D' => 22, 'E' => 20, 'F' => 20, 'G' => 20, 'H' => 30] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        // SHEET 3: PANDUAN_PIUTANG
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_PIUTANG', [
            ['No. Faktur', 'Nomor invoice resmi dari sistem lama Anda.'],
            ['Nama Pelanggan', 'Pilih nama pelanggan yang sudah terdaftar di COOCA (atau ketik persis sama).'],
            ['Sisa Piutang', 'Jika dikosongkan, sistem otomatis menghitung: Sisa Piutang = Total Tagihan - Sudah Dibayar.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Saldo_Awal_Piutang_{$business->id}.xlsx");
    }

    /**
     * Export open receivables to Excel.
     */
    public function exportReceivables(Business $business): StreamedResponse
    {
        $invoices = Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_UNPAID, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE, Invoice::STATUS_SENT])
            ->with('customer')
            ->orderBy('due_date')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PIUTANG_USAHA');

        $headers = [
            'A1' => 'No. Faktur',
            'B1' => 'Pelanggan',
            'C1' => 'Tgl Faktur',
            'D1' => 'Jatuh Tempo',
            'E1' => 'Total Tagihan (Rp)',
            'F1' => 'Terbayar (Rp)',
            'G1' => 'Sisa Piutang (Rp)',
            'H1' => 'Status',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        foreach ($invoices as $inv) {
            $sheet->setCellValue("A{$rowNum}", CsvSanitizer::sanitize($inv->invoice_number));
            $sheet->setCellValue("B{$rowNum}", CsvSanitizer::sanitize($inv->customer?->name ?: ($inv->recipient_name ?: '-')));
            $sheet->setCellValue("C{$rowNum}", $inv->invoice_date ? Carbon::parse($inv->invoice_date)->format('Y-m-d') : '-');
            $sheet->setCellValue("D{$rowNum}", $inv->due_date ? Carbon::parse($inv->due_date)->format('Y-m-d') : '-');
            $sheet->setCellValue("E{$rowNum}", (float) $inv->total_amount);
            $sheet->setCellValue("F{$rowNum}", (float) $inv->paid_amount);
            $sheet->setCellValue("G{$rowNum}", (float) $inv->balance_due);
            $sheet->setCellValue("H{$rowNum}", strtoupper($inv->status));

            $this->applyDataRowStyle($sheet, "A{$rowNum}:H{$rowNum}", $rowNum % 2 === 0);
            $sheet->getStyle("E{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("G{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $rowNum++;
        }

        foreach (['A' => 20, 'B' => 32, 'C' => 16, 'D' => 16, 'E' => 20, 'F' => 20, 'G' => 20, 'H' => 16] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        return $this->streamSpreadsheet($spreadsheet, "Piutang_Usaha_{$business->id}.xlsx");
    }

    /**
     * Parse and preview Receivables import.
     */
    public function parseAndPreviewReceivables(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'error_count' => 0,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        $customers = Customer::where('business_id', $business->id)->get();
        $custMap = [];
        foreach ($customers as $c) {
            $custMap[strtolower(trim($c->name))] = $c;
        }

        $existingInvoices = Invoice::where('business_id', $business->id)->pluck('invoice_number')->toArray();
        $invSet = array_flip(array_map('strtolower', $existingInvoices));

        $parsedRows = [];
        $validCount = 0;
        $errorCount = 0;
        $totalBalanceDue = 0.0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $invNum = trim((string) ($row['no_faktur_invoice'] ?? $row['nomor_faktur'] ?? $row['no_faktur'] ?? $row['invoice_number'] ?? $row['no_invoice'] ?? ''));
            $custName = trim((string) ($row['nama_pelanggan'] ?? $row['pelanggan'] ?? $row['customer'] ?? ''));
            $invDate = trim((string) ($row['tanggal_faktur_yyyy_mm_dd'] ?? $row['tanggal_faktur'] ?? $row['tgl_faktur'] ?? $row['invoice_date'] ?? ''));
            $dueDate = trim((string) ($row['jatuh_tempo_yyyy_mm_dd'] ?? $row['jatuh_tempo'] ?? $row['due_date'] ?? ''));
            $total = (float) ($row['total_tagihan_rp'] ?? $row['total_tagihan'] ?? $row['total'] ?? 0);
            $paid = (float) ($row['sudah_dibayar_rp'] ?? $row['sudah_dibayar'] ?? $row['paid'] ?? 0);
            $balanceDueRaw = $row['sisa_piutang_rp'] ?? $row['sisa_piutang'] ?? null;
            $balanceDue = ($balanceDueRaw !== null && is_numeric($balanceDueRaw)) ? (float) $balanceDueRaw : max(0.0, $total - $paid);
            if ($paid <= 0 && $balanceDue < $total) {
                $paid = max(0.0, $total - $balanceDue);
            }
            $notes = trim((string) ($row['catatan_memo'] ?? $row['catatan'] ?? ''));

            if (empty($invNum) || empty($custName) || $total <= 0) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Format Salah',
                    'name' => $custName ?: 'Tanpa Pelanggan',
                    'code' => $invNum ?: '-',
                    'category' => '-',
                    'formatted_price' => 'Rp ' . number_format($total, 0, ',', '.'),
                    'reason' => 'No. Faktur, Nama Pelanggan, dan Total Tagihan (> 0) wajib diisi.',
                ];
                continue;
            }

            if (isset($invSet[strtolower($invNum)])) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'No. Faktur Ada',
                    'name' => $custName,
                    'code' => $invNum,
                    'category' => '-',
                    'formatted_price' => 'Rp ' . number_format($total, 0, ',', '.'),
                    'reason' => "No. Faktur '{$invNum}' sudah pernah tercatat di sistem.",
                ];
                continue;
            }

            $customer = $custMap[strtolower($custName)] ?? null;

            $validCount++;
            $totalBalanceDue += $balanceDue;
            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => 'valid',
                'status_label' => 'Siap Impor',
                'customer_id' => $customer?->id,
                'customer_name' => $custName,
                'invoice_number' => $invNum,
                'invoice_date' => !empty($invDate) ? date('Y-m-d', strtotime($invDate)) : date('Y-m-d'),
                'due_date' => !empty($dueDate) ? date('Y-m-d', strtotime($dueDate)) : date('Y-m-d', strtotime('+30 days')),
                'total_amount' => $total,
                'paid_amount' => $paid,
                'balance_due' => $balanceDue,
                'name' => $custName,
                'code' => $invNum,
                'category' => $customer ? 'Pelanggan Terdaftar' : 'Pelanggan Baru (Auto-Create)',
                'formatted_price' => 'Sisa: Rp ' . number_format($balanceDue, 0, ',', '.'),
                'notes' => $notes,
                'reason' => 'Faktur piutang valid.',
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'total_balance_due' => $totalBalanceDue,
            'can_proceed' => $validCount > 0 && $errorCount === 0,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Receivables import.
     */
    public function executeReceivablesImport(Business $business, array $rows): array
    {
        $validRows = array_filter($rows, fn ($r) => ($r['status'] ?? '') === 'valid');
        $imported = 0;

        DB::transaction(function () use ($business, $validRows, &$imported): void {
            foreach ($validRows as $r) {
                $custId = $r['customer_id'] ?? null;
                if (! $custId) {
                    $c = Customer::firstOrCreate([
                        'business_id' => $business->id,
                        'name' => $r['customer_name'],
                    ], [
                        'phone' => '08000000' . rand(1000, 9999),
                    ]);
                    $custId = $c->id;
                }

                $balanceDue = (float) $r['balance_due'];
                $paidAmount = (float) $r['paid_amount'];
                if ($paidAmount <= 0 && $balanceDue < (float) $r['total_amount']) {
                    $paidAmount = max(0.0, (float) $r['total_amount'] - $balanceDue);
                }
                $status = ($balanceDue <= 0) ? Invoice::STATUS_PAID : (($paidAmount > 0) ? Invoice::STATUS_PARTIALLY_PAID : Invoice::STATUS_UNPAID);

                Invoice::create([
                    'business_id' => $business->id,
                    'customer_id' => $custId,
                    'invoice_number' => $r['invoice_number'],
                    'invoice_date' => $r['invoice_date'],
                    'due_date' => $r['due_date'],
                    'subtotal' => (float) $r['total_amount'],
                    'total_amount' => (float) $r['total_amount'],
                    'paid_amount' => $paidAmount,
                    'balance_due' => $balanceDue,
                    'status' => $status,
                    'notes' => $r['notes'] ?? 'Saldo awal piutang cut-off migrasi.',
                ]);
                $imported++;
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => Invoice::class,
                'auditable_id' => $business->id,
                'action' => 'import_receivables',
                'notes' => "Import {$imported} faktur saldo awal piutang.",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        });

        return ['imported' => $imported];
    }

    /**
     * Generate Payables (AP) Template.
     */
    public function generatePayablesTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: INPUT_HUTANG
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_HUTANG');

        $headers = [
            'A1' => 'No. Tagihan Pemasok *',
            'B1' => 'Nama Pemasok *',
            'C1' => 'Tanggal Tagihan * (YYYY-MM-DD)',
            'D1' => 'Jatuh Tempo * (YYYY-MM-DD)',
            'E1' => 'Total Tagihan (Rp) *',
            'F1' => 'Sudah Dibayar (Rp)',
            'G1' => 'Sisa Hutang (Rp)',
            'H1' => 'Catatan / Memo',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $bgColor = in_array($cell, ['A1', 'B1', 'C1', 'D1', 'E1']) ? self::COLOR_PRIMARY : self::COLOR_MUTED;
            $this->applyHeaderStyle($sheet, $cell, $bgColor);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Sample row
        $sampleRow = [
            'A2' => 'BILL-2026-0001',
            'B2' => 'PT Gandum Sejahtera',
            'C2' => date('Y-m-d', strtotime('-10 days')),
            'D2' => date('Y-m-d', strtotime('+20 days')),
            'E2' => 8500000,
            'F2' => 3500000,
            'G2' => 5000000,
            'H2' => 'Saldo hutang bahan baku cut-off migrasi',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '64748B'], 'size' => 10, 'name' => 'Segoe UI'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER_COLOR]]],
            ]);
        }
        $sheet->getRowDimension(2)->setRowHeight(24);

        // SHEET 2: DATA_REFERENSI
        $sheetRef = $spreadsheet->createSheet();
        $sheetRef->setTitle('DATA_REFERENSI');

        $suppliers = Supplier::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($suppliers)) $suppliers = ['PT Supplier Utama', 'UD Berkah'];

        $sheetRef->setCellValue('A1', 'Daftar Pemasok Aktif');
        $this->applyHeaderStyle($sheetRef, 'A1', self::COLOR_PRIMARY);
        $sheetRef->getColumnDimension('A')->setWidth(35);
        $r = 2;
        foreach ($suppliers as $s) {
            $sheetRef->setCellValue("A{$r}", $s);
            $r++;
        }
        $endRow = max(2, $r - 1);
        $spreadsheet->addNamedRange(new NamedRange('RefSupplierHutang', $sheetRef, "\$A\$2:\$A\${$endRow}"));

        $this->applyDropdownValidation($sheet, 'B3:B1000', '=RefSupplierHutang', 'Pilih Pemasok');

        foreach (['A' => 22, 'B' => 32, 'C' => 22, 'D' => 22, 'E' => 20, 'F' => 20, 'G' => 20, 'H' => 30] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        // SHEET 3: PANDUAN_HUTANG
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_HUTANG', [
            ['No. Tagihan Pemasok', 'Nomor faktur/surat jalan tagihan dari pihak vendor.'],
            ['Nama Pemasok', 'Pilih nama pemasok yang sudah terdaftar di COOCA (atau ketik persis sama).'],
            ['Sisa Hutang', 'Jika dikosongkan, sistem otomatis menghitung: Sisa Hutang = Total Tagihan - Sudah Dibayar.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Saldo_Awal_Hutang_{$business->id}.xlsx");
    }

    /**
     * Export open payables to Excel.
     */
    public function exportPayables(Business $business): StreamedResponse
    {
        $invoices = SupplierInvoice::where('business_id', $business->id)
            ->whereIn('status', [SupplierInvoice::STATUS_UNPAID, SupplierInvoice::STATUS_PARTIAL])
            ->with('supplier')
            ->orderBy('due_date')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('HUTANG_USAHA');

        $headers = [
            'A1' => 'No. Tagihan',
            'B1' => 'Pemasok / Vendor',
            'C1' => 'Tgl Tagihan',
            'D1' => 'Jatuh Tempo',
            'E1' => 'Total Tagihan (Rp)',
            'F1' => 'Terbayar (Rp)',
            'G1' => 'Sisa Hutang (Rp)',
            'H1' => 'Status',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        foreach ($invoices as $inv) {
            $sheet->setCellValue("A{$rowNum}", CsvSanitizer::sanitize($inv->invoice_number));
            $sheet->setCellValue("B{$rowNum}", CsvSanitizer::sanitize($inv->supplier?->name ?: '-'));
            $sheet->setCellValue("C{$rowNum}", $inv->invoice_date ? Carbon::parse($inv->invoice_date)->format('Y-m-d') : '-');
            $sheet->setCellValue("D{$rowNum}", $inv->due_date ? Carbon::parse($inv->due_date)->format('Y-m-d') : '-');
            $sheet->setCellValue("E{$rowNum}", (float) $inv->total_amount);
            $sheet->setCellValue("F{$rowNum}", (float) $inv->paid_amount);
            $sheet->setCellValue("G{$rowNum}", (float) $inv->balance_due);
            $sheet->setCellValue("H{$rowNum}", strtoupper($inv->status));

            $this->applyDataRowStyle($sheet, "A{$rowNum}:H{$rowNum}", $rowNum % 2 === 0);
            $sheet->getStyle("E{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("G{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $rowNum++;
        }

        foreach (['A' => 20, 'B' => 32, 'C' => 16, 'D' => 16, 'E' => 20, 'F' => 20, 'G' => 20, 'H' => 16] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        return $this->streamSpreadsheet($spreadsheet, "Hutang_Usaha_{$business->id}.xlsx");
    }

    /**
     * Parse and preview Payables import.
     */
    public function parseAndPreviewPayables(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'error_count' => 0,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        $suppliers = Supplier::where('business_id', $business->id)->get();
        $supMap = [];
        foreach ($suppliers as $s) {
            $supMap[strtolower(trim($s->name))] = $s;
        }

        $existingInvoices = SupplierInvoice::where('business_id', $business->id)->pluck('invoice_number')->toArray();
        $invSet = array_flip(array_map('strtolower', $existingInvoices));

        $parsedRows = [];
        $validCount = 0;
        $errorCount = 0;
        $totalBalanceDue = 0.0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $invNum = trim((string) ($row['no_tagihan_pemasok'] ?? $row['nomor_tagihan'] ?? $row['no_tagihan'] ?? $row['invoice_number'] ?? $row['no_bill'] ?? ''));
            $supName = trim((string) ($row['nama_pemasok'] ?? $row['pemasok'] ?? $row['supplier'] ?? ''));
            $invDate = trim((string) ($row['tanggal_tagihan_yyyy_mm_dd'] ?? $row['tanggal_tagihan'] ?? $row['tgl_tagihan'] ?? $row['invoice_date'] ?? $row['bill_date'] ?? ''));
            $dueDate = trim((string) ($row['jatuh_tempo_yyyy_mm_dd'] ?? $row['jatuh_tempo'] ?? $row['due_date'] ?? ''));
            $total = (float) ($row['total_tagihan_rp'] ?? $row['total_tagihan'] ?? $row['total'] ?? 0);
            $paid = (float) ($row['sudah_dibayar_rp'] ?? $row['sudah_dibayar'] ?? $row['paid'] ?? 0);
            $balanceDueRaw = $row['sisa_hutang_rp'] ?? $row['sisa_hutang'] ?? null;
            $balanceDue = ($balanceDueRaw !== null && is_numeric($balanceDueRaw)) ? (float) $balanceDueRaw : max(0.0, $total - $paid);
            if ($paid <= 0 && $balanceDue < $total) {
                $paid = max(0.0, $total - $balanceDue);
            }
            $notes = trim((string) ($row['catatan_memo'] ?? $row['catatan'] ?? ''));

            if (empty($invNum) || empty($supName) || $total <= 0) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Format Salah',
                    'name' => $supName ?: 'Tanpa Pemasok',
                    'code' => $invNum ?: '-',
                    'category' => '-',
                    'formatted_price' => 'Rp ' . number_format($total, 0, ',', '.'),
                    'reason' => 'No. Tagihan, Nama Pemasok, dan Total Tagihan (> 0) wajib diisi.',
                ];
                continue;
            }

            if (isset($invSet[strtolower($invNum)])) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'No. Tagihan Ada',
                    'name' => $supName,
                    'code' => $invNum,
                    'category' => '-',
                    'formatted_price' => 'Rp ' . number_format($total, 0, ',', '.'),
                    'reason' => "No. Tagihan '{$invNum}' sudah pernah tercatat di sistem.",
                ];
                continue;
            }

            $supplier = $supMap[strtolower($supName)] ?? null;

            $validCount++;
            $totalBalanceDue += $balanceDue;
            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => 'valid',
                'status_label' => 'Siap Impor',
                'supplier_id' => $supplier?->id,
                'supplier_name' => $supName,
                'invoice_number' => $invNum,
                'invoice_date' => !empty($invDate) ? date('Y-m-d', strtotime($invDate)) : date('Y-m-d'),
                'due_date' => !empty($dueDate) ? date('Y-m-d', strtotime($dueDate)) : date('Y-m-d', strtotime('+30 days')),
                'total_amount' => $total,
                'paid_amount' => $paid,
                'balance_due' => $balanceDue,
                'name' => $supName,
                'code' => $invNum,
                'category' => $supplier ? 'Pemasok Terdaftar' : 'Pemasok Baru (Auto-Create)',
                'formatted_price' => 'Sisa: Rp ' . number_format($balanceDue, 0, ',', '.'),
                'notes' => $notes,
                'reason' => 'Tagihan hutang valid.',
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'total_balance_due' => $totalBalanceDue,
            'can_proceed' => $validCount > 0 && $errorCount === 0,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Payables import.
     */
    public function executePayablesImport(Business $business, array $rows): array
    {
        $validRows = array_filter($rows, fn ($r) => ($r['status'] ?? '') === 'valid');
        $imported = 0;

        DB::transaction(function () use ($business, $validRows, &$imported): void {
            foreach ($validRows as $r) {
                $supId = $r['supplier_id'] ?? null;
                if (! $supId) {
                    $s = Supplier::firstOrCreate([
                        'business_id' => $business->id,
                        'name' => $r['supplier_name'],
                    ], [
                        'phone' => '08000000' . rand(1000, 9999),
                    ]);
                    $supId = $s->id;
                }

                $balanceDue = (float) $r['balance_due'];
                $paidAmount = (float) $r['paid_amount'];
                if ($paidAmount <= 0 && $balanceDue < (float) $r['total_amount']) {
                    $paidAmount = max(0.0, (float) $r['total_amount'] - $balanceDue);
                }
                $status = ($balanceDue <= 0) ? SupplierInvoice::STATUS_PAID : (($paidAmount > 0) ? SupplierInvoice::STATUS_PARTIAL : SupplierInvoice::STATUS_UNPAID);

                SupplierInvoice::create([
                    'business_id' => $business->id,
                    'supplier_id' => $supId,
                    'invoice_number' => $r['invoice_number'],
                    'invoice_date' => $r['invoice_date'],
                    'due_date' => $r['due_date'],
                    'total_amount' => (float) $r['total_amount'],
                    'paid_amount' => $paidAmount,
                    'balance_due' => $balanceDue,
                    'status' => $status,
                ]);
                $imported++;
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => SupplierInvoice::class,
                'auditable_id' => $business->id,
                'action' => 'import_payables',
                'notes' => "Import {$imported} tagihan saldo awal hutang pemasok.",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        });

        return ['imported' => $imported];
    }

    // =========================================================================
    // SPRINT 3: HUMAN RESOURCE MANAGEMENT (MASTER KARYAWAN)
    // =========================================================================

    /**
     * Generate Smart Employee Template with Role & PTKP Dropdowns.
     */
    public function generateEmployeeTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // SHEET 1: INPUT_KARYAWAN
        // -------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_KARYAWAN');

        $headers = [
            'A1' => 'Nama Lengkap *',
            'B1' => 'Email Login *',
            'C1' => 'Nomor WhatsApp / HP',
            'D1' => 'Jabatan / Posisi *',
            'E1' => 'Tipe Kerja * (permanent/contract/daily_worker)',
            'F1' => 'Peran Hak Akses *',
            'G1' => 'Status PTKP Pajak *',
            'H1' => 'Tanggal Masuk (YYYY-MM-DD)',
            'I1' => 'Gaji Pokok (Rp)',
            'J1' => 'Upah Harian (Rp)',
            'K1' => 'Tunjangan Tetap (Rp)',
            'L1' => 'Tunjangan Variabel (Rp)',
            'M1' => 'NIK KTP',
            'N1' => 'NPWP',
            'O1' => 'BPJS Ketenagakerjaan (Y/N)',
            'P1' => 'No. BPJS TK',
            'Q1' => 'BPJS Kesehatan (Y/N)',
            'R1' => 'No. BPJS Kes',
            'S1' => 'Nama Bank',
            'T1' => 'Nomor Rekening',
            'U1' => 'Nama Pemilik Rekening',
            'V1' => 'Lokasi Penempatan Utama',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $bgColor = in_array($cell, ['A1', 'B1', 'D1', 'E1', 'F1', 'G1'])
                ? self::COLOR_PRIMARY
                : (in_array($cell, ['I1', 'J1', 'K1', 'L1']) ? self::COLOR_WARNING : self::COLOR_MUTED);
            $this->applyHeaderStyle($sheet, $cell, $bgColor);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Baris 2: Contoh data panduan
        $sampleRow = [
            'A2' => 'Budi Santoso',
            'B2' => 'budi.santoso@cooca.test',
            'C2' => '08123456789',
            'D2' => 'Barista Senior',
            'E2' => 'permanent',
            'F2' => 'Kasir',
            'G2' => 'TK/0',
            'H2' => '2026-01-01',
            'I2' => 4500000,
            'J2' => 0,
            'K2' => 500000,
            'L2' => 250000,
            'M2' => '3171012345670001',
            'N2' => '12.345.678.9-012.000',
            'O2' => 'Y',
            'P2' => '00012345678',
            'Q2' => 'Y',
            'R2' => '00087654321',
            'S2' => 'BCA',
            'T2' => '1234567890',
            'U2' => 'Budi Santoso',
            'V2' => 'Gudang Utama',
        ];

        foreach ($sampleRow as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $this->applyGuideRowStyle($sheet, $cell);
        }
        $sheet->getRowDimension(2)->setRowHeight(24);

        // Number formats
        $sheet->getStyle('I2:L2')->getNumberFormat()->setFormatCode('#,##0');

        // -------------------------------------------------------------
        // SHEET 2: DATA_REFERENSI_TERKAIT
        // -------------------------------------------------------------
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('DATA_REFERENSI_TERKAIT');

        $refSheet->setCellValue('A1', 'Daftar Peran (Role)');
        $this->applyHeaderStyle($refSheet, 'A1', self::COLOR_LOOKUP);

        $roles = Role::where(function ($q) use ($business): void {
            $q->where('business_id', $business->id)->orWhereNull('business_id');
        })->orderBy('name')->pluck('name')->toArray();

        if (empty($roles)) {
            $roles = ['Owner', 'Manager', 'Kasir', 'Staf Gudang', 'Staff'];
        }

        $r = 2;
        foreach ($roles as $role) {
            $refSheet->setCellValue("A{$r}", $role);
            $this->applyDataRowStyle($refSheet, "A{$r}", $r % 2 === 0);
            $r++;
        }
        $spreadsheet->addNamedRange(new NamedRange('RefRoleKaryawan', $refSheet, '$A$2:$A$' . max(2, $r - 1)));

        // Tipe Kerja
        $refSheet->setCellValue('B1', 'Tipe Kerja');
        $this->applyHeaderStyle($refSheet, 'B1', self::COLOR_LOOKUP);
        $empTypes = ['permanent', 'contract', 'daily_worker'];
        foreach ($empTypes as $i => $type) {
            $rowNum = $i + 2;
            $refSheet->setCellValue("B{$rowNum}", $type);
            $this->applyDataRowStyle($refSheet, "B{$rowNum}", $rowNum % 2 === 0);
        }
        $spreadsheet->addNamedRange(new NamedRange('RefTipeKerja', $refSheet, '$B$2:$B$4'));

        // PTKP
        $refSheet->setCellValue('C1', 'Status PTKP');
        $this->applyHeaderStyle($refSheet, 'C1', self::COLOR_LOOKUP);
        $ptkpList = ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'];
        foreach ($ptkpList as $i => $ptkp) {
            $rowNum = $i + 2;
            $refSheet->setCellValue("C{$rowNum}", $ptkp);
            $this->applyDataRowStyle($refSheet, "C{$rowNum}", $rowNum % 2 === 0);
        }
        $spreadsheet->addNamedRange(new NamedRange('RefPtkp', $refSheet, '$C$2:$C$9'));

        // Lokasi
        $refSheet->setCellValue('D1', 'Lokasi Outlet/Gudang');
        $this->applyHeaderStyle($refSheet, 'D1', self::COLOR_LOOKUP);
        $locations = Location::where('business_id', $business->id)->pluck('name')->toArray();
        if (empty($locations)) {
            $locations = ['Outlet Utama'];
        }
        $r = 2;
        foreach ($locations as $loc) {
            $refSheet->setCellValue("D{$r}", $loc);
            $this->applyDataRowStyle($refSheet, "D{$r}", $r % 2 === 0);
            $r++;
        }
        $spreadsheet->addNamedRange(new NamedRange('RefLokasiKaryawan', $refSheet, '$D$2:$D$' . max(2, $r - 1)));

        // Y/N
        $refSheet->setCellValue('E1', 'Opsi Ya/Tidak');
        $this->applyHeaderStyle($refSheet, 'E1', self::COLOR_LOOKUP);
        $refSheet->setCellValue('E2', 'Y');
        $refSheet->setCellValue('E3', 'N');
        $spreadsheet->addNamedRange(new NamedRange('RefYaTidak', $refSheet, '$E$2:$E$3'));

        // Apply Data Validation dropdowns to Sheet 1
        $this->applyDropdownValidation($sheet, 'E3:E100', '=RefTipeKerja', 'Pilih Tipe Kerja');
        $this->applyDropdownValidation($sheet, 'F3:F100', '=RefRoleKaryawan', 'Pilih Peran Hak Akses');
        $this->applyDropdownValidation($sheet, 'G3:G100', '=RefPtkp', 'Pilih Status PTKP');
        $this->applyDropdownValidation($sheet, 'O3:O100', '=RefYaTidak', 'BPJS TK Aktif (Y/N)');
        $this->applyDropdownValidation($sheet, 'Q3:Q100', '=RefYaTidak', 'BPJS Kes Aktif (Y/N)');
        $this->applyDropdownValidation($sheet, 'V3:V100', '=RefLokasiKaryawan', 'Pilih Lokasi Utama');

        // Auto-fit column widths
        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setWidth(22);
        }
        $sheet->getColumnDimension('A')->setWidth(26);
        $sheet->getColumnDimension('B')->setWidth(30);

        // -------------------------------------------------------------
        // SHEET 3: PANDUAN_PENGISIAN
        // -------------------------------------------------------------
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_PENGISIAN', [
            ['Kolom Hijau (Wajib)', 'Nama Lengkap, Email Login, Jabatan, Tipe Kerja, Peran, dan Status PTKP wajib diisi.'],
            ['Kolom Kuning (Finansial)', 'Gaji Pokok, Upah Harian, Tunjangan diisi angka murni tanpa titik atau tanda koma.'],
            ['Tipe Kerja', 'Pilihan: permanent (Karyawan Tetap), contract (Kontrak), daily_worker (Pekerja Harian Lepas).'],
            ['Status PTKP', 'Wajib dipilih dari dropdown: TK/0 s.d TK/3 untuk Lajang, K/0 s.d K/3 untuk Kawin.'],
            ['Password Akun', 'Password awal default diset: password123. Karyawan dapat mengubahnya saat login pertama.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Import_Karyawan_{$business->id}.xlsx");
    }

    /**
     * Export all employees / staff master to Excel.
     */
    public function exportEmployees(Business $business): StreamedResponse
    {
        $memberships = BusinessMembership::where('business_id', $business->id)
            ->with(['user', 'customRole', 'location'])
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DAFTAR_KARYAWAN');

        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Lengkap',
            'C1' => 'Email Login',
            'D1' => 'No. WhatsApp',
            'E1' => 'Jabatan / Posisi',
            'F1' => 'Tipe Kerja',
            'G1' => 'Peran Hak Akses',
            'H1' => 'Status PTKP',
            'I1' => 'Tgl Masuk',
            'J1' => 'Gaji Pokok (Rp)',
            'K1' => 'Upah Harian (Rp)',
            'L1' => 'Tunjangan Tetap (Rp)',
            'M1' => 'Tunjangan Variabel (Rp)',
            'N1' => 'NIK KTP',
            'O1' => 'NPWP',
            'P1' => 'BPJS TK',
            'Q1' => 'No. BPJS TK',
            'R1' => 'BPJS Kes',
            'S1' => 'No. BPJS Kes',
            'T1' => 'Nama Bank',
            'U1' => 'Nomor Rekening',
            'V1' => 'Pemilik Rekening',
            'W1' => 'Lokasi Penempatan',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        foreach ($memberships as $idx => $m) {
            $user = $m->user;
            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", CsvSanitizer::sanitize((string) ($user?->name ?: '-')));
            $sheet->setCellValue("C{$rowNum}", CsvSanitizer::sanitize((string) ($user?->email ?: '-')));
            $sheet->setCellValue("D{$rowNum}", CsvSanitizer::sanitize((string) ($m->whatsapp_number ?: ($user?->phone ?: '-'))));
            $sheet->setCellValue("E{$rowNum}", CsvSanitizer::sanitize((string) ($m->job_title ?: '-')));
            $sheet->setCellValue("F{$rowNum}", strtoupper($m->employment_type ?: 'permanent'));
            $sheet->setCellValue("G{$rowNum}", CsvSanitizer::sanitize((string) ($m->customRole?->name ?: ($m->role ?: 'Staff'))));
            $sheet->setCellValue("H{$rowNum}", $m->tax_ptkp_status ?: 'TK/0');
            $sheet->setCellValue("I{$rowNum}", $m->join_date ? Carbon::parse($m->join_date)->format('Y-m-d') : '-');
            $sheet->setCellValue("J{$rowNum}", (float) $m->base_salary);
            $sheet->setCellValue("K{$rowNum}", (float) $m->daily_rate);
            $sheet->setCellValue("L{$rowNum}", (float) $m->fixed_allowances);
            $sheet->setCellValue("M{$rowNum}", (float) $m->variable_allowances);
            $sheet->setCellValue("N{$rowNum}", CsvSanitizer::sanitize((string) ($m->nik_ktp ?: '-')));
            $sheet->setCellValue("O{$rowNum}", CsvSanitizer::sanitize((string) ($m->npwp ?: '-')));
            $sheet->setCellValue("P{$rowNum}", $m->bpjs_tk_enabled ? 'Aktif' : 'Non-Aktif');
            $sheet->setCellValue("Q{$rowNum}", CsvSanitizer::sanitize((string) ($m->bpjs_tk_number ?: '-')));
            $sheet->setCellValue("R{$rowNum}", $m->bpjs_kes_enabled ? 'Aktif' : 'Non-Aktif');
            $sheet->setCellValue("S{$rowNum}", CsvSanitizer::sanitize((string) ($m->bpjs_kes_number ?: '-')));
            $sheet->setCellValue("T{$rowNum}", CsvSanitizer::sanitize((string) ($m->bank_name ?: '-')));
            $sheet->setCellValue("U{$rowNum}", CsvSanitizer::sanitize((string) ($m->bank_account_number ?: '-')));
            $sheet->setCellValue("V{$rowNum}", CsvSanitizer::sanitize((string) ($m->bank_account_holder ?: '-')));
            $sheet->setCellValue("W{$rowNum}", CsvSanitizer::sanitize((string) ($m->location?->name ?: '-')));

            $this->applyDataRowStyle($sheet, "A{$rowNum}:W{$rowNum}", $rowNum % 2 === 0);
            $sheet->getStyle("J{$rowNum}:M{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $rowNum++;
        }

        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(30);

        return $this->streamSpreadsheet($spreadsheet, "Data_Karyawan_{$business->id}.xlsx");
    }

    /**
     * Parse and preview employee import file.
     */
    public function parseAndPreviewEmployees(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        $existingMemberships = BusinessMembership::where('business_id', $business->id)->with('user')->get();
        $existingEmails = [];
        foreach ($existingMemberships as $m) {
            if ($m->user?->email) {
                $existingEmails[strtolower(trim($m->user->email))] = $m;
            }
        }

        $roles = Role::where(function ($q) use ($business): void {
            $q->where('business_id', $business->id)->orWhereNull('business_id');
        })->get();
        $roleMap = [];
        foreach ($roles as $r) {
            $roleMap[strtolower(trim($r->name))] = $r;
            $roleMap[strtolower(trim($r->slug))] = $r;
        }

        $locations = Location::where('business_id', $business->id)->get();
        $locMap = [];
        foreach ($locations as $l) {
            $locMap[strtolower(trim($l->name))] = $l;
        }

        $parsedRows = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;
            $name = trim((string) ($row['nama_lengkap'] ?? $row['nama'] ?? $row['name'] ?? ''));
            $email = strtolower(trim((string) ($row['email_login'] ?? $row['email'] ?? '')));
            $phone = trim((string) ($row['nomor_whatsapp_hp'] ?? $row['nomor_whatsapp'] ?? $row['no_wa'] ?? $row['telepon'] ?? $row['phone'] ?? ''));
            $jobTitle = trim((string) ($row['jabatan_posisi'] ?? $row['jabatan'] ?? $row['posisi'] ?? ''));
            $empTypeRaw = strtolower(trim((string) ($row['tipe_kerja_permanent_contract_daily_worker'] ?? $row['tipe_kerja'] ?? 'permanent')));
            $roleName = trim((string) ($row['peran_hak_akses'] ?? $row['peran'] ?? $row['role'] ?? 'Staff'));
            $ptkp = strtoupper(trim((string) ($row['status_ptkp_pajak'] ?? $row['status_ptkp'] ?? $row['ptkp'] ?? 'TK/0')));
            $joinDate = trim((string) ($row['tanggal_masuk_yyyy_mm_dd'] ?? $row['tanggal_masuk'] ?? $row['tgl_masuk'] ?? ''));
            $salary = (float) ($row['gaji_pokok_rp'] ?? $row['gaji_pokok'] ?? 0);
            $dailyRate = (float) ($row['upah_harian_rp'] ?? $row['upah_harian'] ?? 0);
            $fixedAllow = (float) ($row['tunjangan_tetap_rp'] ?? $row['tunjangan_tetap'] ?? 0);
            $varAllow = (float) ($row['tunjangan_variabel_rp'] ?? $row['tunjangan_variabel'] ?? 0);
            $nik = trim((string) ($row['nik_ktp'] ?? $row['nik'] ?? ''));
            $npwp = trim((string) ($row['npwp'] ?? ''));
            $bpjsTk = in_array(strtolower(trim((string) ($row['bpjs_ketenagakerjaan_y_n'] ?? $row['bpjs_tk'] ?? ''))), ['y', 'ya', 'yes', '1', 'true']);
            $noBpjsTk = trim((string) ($row['no_bpjs_tk'] ?? ''));
            $bpjsKes = in_array(strtolower(trim((string) ($row['bpjs_kesehatan_y_n'] ?? $row['bpjs_kes'] ?? ''))), ['y', 'ya', 'yes', '1', 'true']);
            $noBpjsKes = trim((string) ($row['no_bpjs_kes'] ?? ''));
            $bankName = trim((string) ($row['nama_bank'] ?? $row['bank'] ?? ''));
            $bankAcc = trim((string) ($row['nomor_rekening'] ?? $row['no_rekening'] ?? ''));
            $bankHolder = trim((string) ($row['nama_pemilik_rekening'] ?? $row['pemilik_rekening'] ?? ''));
            $locName = trim((string) ($row['lokasi_penempatan_utama'] ?? $row['lokasi'] ?? ''));

            // Normalization
            $empType = match ($empTypeRaw) {
                'kontrak', 'contract' => 'contract',
                'harian', 'daily', 'daily_worker', 'lepas' => 'daily_worker',
                default => 'permanent',
            };

            $matchedRole = $roleMap[strtolower($roleName)] ?? null;
            $matchedLoc = $locMap[strtolower($locName)] ?? null;

            // Validations
            if (empty($name) || empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Format Salah',
                    'name' => $name ?: 'Tanpa Nama',
                    'code' => $email ?: '-',
                    'category' => $jobTitle ?: '-',
                    'formatted_price' => 'Rp ' . number_format($salary, 0, ',', '.'),
                    'reason' => 'Nama lengkap dan format email login yang valid wajib diisi.',
                ];
                continue;
            }

            $isDuplicate = isset($existingEmails[$email]);

            if ($isDuplicate) {
                $duplicateCount++;
                $status = 'duplicate';
                $statusLabel = 'Sudah Ada';
                $reason = "Email '{$email}' sudah terdaftar sebagai anggota staf di bisnis ini. Pilih opsi 'Perbarui Data' jika ingin memperbarui gaji/jabatan.";
            } else {
                $validCount++;
                $status = 'valid';
                $statusLabel = 'Siap Impor';
                $reason = 'Data staf lengkap dan valid.';
            }

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => $status,
                'status_label' => $statusLabel,
                'name' => $name,
                'code' => $email,
                'category' => $jobTitle ?: 'Staf',
                'formatted_price' => 'Rp ' . number_format($salary, 0, ',', '.'),
                'email' => $email,
                'phone' => $phone,
                'job_title' => $jobTitle,
                'employment_type' => $empType,
                'role_id' => $matchedRole?->id,
                'role_name' => $matchedRole?->name ?: ($roleName ?: 'Staff'),
                'tax_ptkp_status' => in_array($ptkp, ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3']) ? $ptkp : 'TK/0',
                'join_date' => !empty($joinDate) ? date('Y-m-d', strtotime($joinDate)) : date('Y-m-d'),
                'base_salary' => $salary,
                'daily_rate' => $dailyRate,
                'fixed_allowances' => $fixedAllow,
                'variable_allowances' => $varAllow,
                'nik_ktp' => $nik,
                'npwp' => $npwp,
                'bpjs_tk_enabled' => $bpjsTk,
                'bpjs_tk_number' => $noBpjsTk,
                'bpjs_kes_enabled' => $bpjsKes,
                'bpjs_kes_number' => $noBpjsKes,
                'bank_name' => $bankName,
                'bank_account_number' => $bankAcc,
                'bank_account_holder' => $bankHolder ?: $name,
                'primary_location_id' => $matchedLoc?->id,
                'reason' => $reason,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'can_proceed' => ($validCount + $duplicateCount) > 0,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Employee Import.
     */
    public function executeEmployeeImport(Business $business, array $rows, string $duplicateStrategy = 'skip'): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        $fallbackRole = Role::where(function ($q) use ($business): void {
            $q->where('business_id', $business->id)->orWhereNull('business_id');
        })->whereIn('slug', ['staff', 'kasir', 'employee'])->first();

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, $fallbackRole, &$imported, &$updated, &$skipped): void {
            foreach ($rows as $r) {
                if (($r['status'] ?? '') === 'error' || empty($r['email'])) {
                    $skipped++;
                    continue;
                }

                $email = strtolower(trim($r['email']));
                $user = User::where('email', $email)->first();

                if (! $user) {
                    $user = User::create([
                        'name' => $r['name'],
                        'email' => $email,
                        'phone' => $r['phone'] ?? null,
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                    ]);
                }

                $membership = BusinessMembership::where('business_id', $business->id)
                    ->where('user_id', $user->id)
                    ->first();

                $roleId = $r['role_id'] ?? $fallbackRole?->id;
                $roleModel = $roleId ? Role::find($roleId) : $fallbackRole;
                $roleSlug = $roleModel?->slug ?: 'staff';

                $locId = $r['primary_location_id'] ?? $r['location_id'] ?? null;
                if (! $locId && !empty($r['location_name'])) {
                    $locName = trim((string) $r['location_name']);
                    $foundLoc = Location::where('business_id', $business->id)
                        ->where(function ($q) use ($locName) {
                            $q->where('name', 'like', "%{$locName}%")->orWhere('code', $locName);
                        })->first();
                    $locId = $foundLoc?->id;
                }

                $profileData = [
                    'job_title' => $r['job_title'] ?? null,
                    'employment_type' => $r['employment_type'] ?? 'permanent',
                    'role' => $roleSlug,
                    'role_id' => $roleId,
                    'join_date' => $r['join_date'] ?? date('Y-m-d'),
                    'base_salary' => (float) ($r['base_salary'] ?? 0),
                    'daily_rate' => (float) ($r['daily_rate'] ?? 0),
                    'fixed_allowances' => (float) ($r['fixed_allowances'] ?? 0),
                    'variable_allowances' => (float) ($r['variable_allowances'] ?? 0),
                    'tax_ptkp_status' => $r['tax_ptkp_status'] ?? 'TK/0',
                    'nik_ktp' => $r['nik_ktp'] ?? null,
                    'npwp' => $r['npwp'] ?? null,
                    'bpjs_tk_enabled' => (bool) ($r['bpjs_tk_enabled'] ?? false),
                    'bpjs_tk_number' => $r['bpjs_tk_number'] ?? null,
                    'bpjs_kes_enabled' => (bool) ($r['bpjs_kes_enabled'] ?? false),
                    'bpjs_kes_number' => $r['bpjs_kes_number'] ?? null,
                    'bank_name' => $r['bank_name'] ?? null,
                    'bank_account_number' => $r['bank_account_number'] ?? null,
                    'bank_account_holder' => $r['bank_account_holder'] ?? $r['name'],
                    'whatsapp_number' => $r['phone'] ?? null,
                    'primary_location_id' => $locId,
                ];

                if ($membership) {
                    if ($duplicateStrategy === 'update') {
                        $membership->update($profileData);
                        $user->update(['name' => $r['name'], 'phone' => $r['phone'] ?? $user->phone]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $profileData['business_id'] = $business->id;
                    $profileData['user_id'] = $user->id;
                    BusinessMembership::create($profileData);
                    $imported++;
                }
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => BusinessMembership::class,
                'auditable_id' => $business->id,
                'action' => 'import_employees',
                'notes' => "Import {$imported} staf baru, {$updated} diperbarui, {$skipped} dilewati.",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    // =========================================================================
    // SPRINT 3: LOG MESIN PRESENSI BIOMETRIK (FINGERPRINT LOG)
    // =========================================================================

    /**
     * Generate Fingerprint Machine Attendance Log Template.
     */
    public function generateFingerprintTemplate(Business $business): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: LOG_MESIN_FINGERPRINT
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('LOG_MESIN_FINGERPRINT');

        $headers = [
            'A1' => 'ID Mesin / PIN / NIK / Email *',
            'B1' => 'Nama Karyawan (Referensi)',
            'C1' => 'Tanggal * (YYYY-MM-DD)',
            'D1' => 'Jam Masuk * (HH:MM / HH:MM:SS)',
            'E1' => 'Jam Keluar (HH:MM / HH:MM:SS)',
            'F1' => 'Status Kehadiran',
            'G1' => 'Nama Shift',
            'H1' => 'Catatan Mesin / Lokasi',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $bgColor = in_array($cell, ['A1', 'C1', 'D1']) ? self::COLOR_PRIMARY : self::COLOR_MUTED;
            $this->applyHeaderStyle($sheet, $cell, $bgColor);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Baris 2: Contoh data panduan
        $sampleRow = [
            'A2' => 'budi.santoso@cooca.test',
            'B2' => 'Budi Santoso',
            'C2' => date('Y-m-d'),
            'D2' => '08:02:15',
            'E2' => '17:05:40',
            'F2' => 'Hadir',
            'G2' => 'Shift Pagi',
            'H2' => 'Mesin Fingerprint Solution X100',
        ];
        foreach ($sampleRow as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $this->applyGuideRowStyle($sheet, $cell);
        }
        $sheet->getRowDimension(2)->setRowHeight(24);

        // SHEET 2: DAFTAR_KARYAWAN_AKTIF
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('DAFTAR_KARYAWAN_AKTIF');

        $refHeaders = [
            'A1' => 'Email Karyawan',
            'B1' => 'Nama Lengkap',
            'C1' => 'NIK KTP',
            'D1' => 'Jabatan',
        ];
        foreach ($refHeaders as $cell => $title) {
            $refSheet->setCellValue($cell, $title);
            $this->applyHeaderStyle($refSheet, $cell, self::COLOR_LOOKUP);
        }
        $refSheet->getRowDimension(1)->setRowHeight(32);

        $memberships = BusinessMembership::where('business_id', $business->id)->with('user')->get();
        $r = 2;
        foreach ($memberships as $m) {
            $refSheet->setCellValue("A{$r}", $m->user?->email ?: '-');
            $refSheet->setCellValue("B{$r}", $m->user?->name ?: '-');
            $refSheet->setCellValue("C{$r}", $m->nik_ktp ?: '-');
            $refSheet->setCellValue("D{$r}", $m->job_title ?: '-');
            $this->applyDataRowStyle($refSheet, "A{$r}:D{$r}", $r % 2 === 0);
            $r++;
        }

        foreach (['A' => 28, 'B' => 26, 'C' => 16, 'D' => 18, 'E' => 18, 'F' => 18, 'G' => 18, 'H' => 28] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // SHEET 3: PANDUAN_PENGISIAN
        $this->buildGuideSheet($spreadsheet, 'PANDUAN_LOG_MESIN', [
            ['Kompatibilitas Mesin', 'Mendukung ekspor format CSV/Excel dari Solution, ZKTeco, Fingerspot, dan mesin fingerprint umum.'],
            ['Kolom Pengenal', 'Isi Kolom A dengan Email Karyawan, NIK KTP, atau PIN mesin yang cocok.'],
            ['Format Waktu', 'Gunakan format jam 24 jam (misal 08:00 atau 17:30). Detik bersifat opsional.'],
            ['Perhitungan Lembur/Terlambat', 'Sistem secara otomatis menghitung selisih menit keterlambatan dan durasi jam kerja.'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, "Template_Presensi_Mesin_{$business->id}.xlsx");
    }

    /**
     * Export Attendance Log to Excel.
     */
    public function exportAttendance(Business $business, ?string $startDate = null, ?string $endDate = null): StreamedResponse
    {
        $query = Attendance::where('business_id', $business->id)->with(['user', 'location', 'workShift']);
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $attendances = $query->orderByDesc('date')->orderByDesc('clock_in_at')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('LOG_PRESENSI');

        $headers = [
            'A1' => 'Tanggal',
            'B1' => 'Nama Karyawan',
            'C1' => 'Email',
            'D1' => 'Shift',
            'E1' => 'Jam Masuk',
            'F1' => 'Jam Keluar',
            'G1' => 'Durasi (Menit)',
            'H1' => 'Terlambat (Menit)',
            'I1' => 'Lembur (Menit)',
            'J1' => 'Status',
            'K1' => 'Lokasi / Metode',
            'L1' => 'Catatan',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        foreach ($attendances as $att) {
            $sheet->setCellValue("A{$rowNum}", $att->date ? Carbon::parse($att->date)->format('Y-m-d') : '-');
            $sheet->setCellValue("B{$rowNum}", CsvSanitizer::sanitize((string) ($att->user?->name ?: '-')));
            $sheet->setCellValue("C{$rowNum}", CsvSanitizer::sanitize((string) ($att->user?->email ?: '-')));
            $sheet->setCellValue("D{$rowNum}", CsvSanitizer::sanitize((string) ($att->shift_name ?: ($att->workShift?->name ?: 'Normal'))));
            $sheet->setCellValue("E{$rowNum}", $att->clock_in_at ? Carbon::parse($att->clock_in_at)->format('H:i:s') : '-');
            $sheet->setCellValue("F{$rowNum}", $att->clock_out_at ? Carbon::parse($att->clock_out_at)->format('H:i:s') : '-');
            $sheet->setCellValue("G{$rowNum}", (int) $att->work_duration_minutes);
            $sheet->setCellValue("H{$rowNum}", (int) $att->late_minutes);
            $sheet->setCellValue("I{$rowNum}", (int) $att->overtime_minutes);
            $sheet->setCellValue("J{$rowNum}", strtoupper($att->status));
            $sheet->setCellValue("K{$rowNum}", $att->is_geofenced ? 'Geofenced GPS' : 'Mesin / Bebas');
            $sheet->setCellValue("L{$rowNum}", CsvSanitizer::sanitize((string) ($att->clock_in_notes ?: '-')));

            $this->applyDataRowStyle($sheet, "A{$rowNum}:L{$rowNum}", $rowNum % 2 === 0);
            $rowNum++;
        }

        foreach (['A' => 16, 'B' => 24, 'C' => 28, 'D' => 16, 'E' => 16, 'F' => 16, 'G' => 16, 'H' => 16, 'I' => 16, 'J' => 16, 'K' => 20, 'L' => 28] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        return $this->streamSpreadsheet($spreadsheet, "Log_Presensi_{$business->id}.xlsx");
    }

    /**
     * Parse and preview fingerprint attendance file.
     */
    public function parseAndPreviewAttendance(Business $business, UploadedFile $file): array
    {
        $rawRows = $this->readSpreadsheetRows($file);
        if (empty($rawRows)) {
            return [
                'total_rows' => 0,
                'valid_count' => 0,
                'duplicate_count' => 0,
                'error_count' => 0,
                'can_proceed' => false,
                'rows' => [],
            ];
        }

        $memberships = BusinessMembership::where('business_id', $business->id)->with('user')->get();
        $userMap = [];
        foreach ($memberships as $m) {
            $u = $m->user;
            if (! $u) continue;
            if ($u->email) $userMap[strtolower(trim($u->email))] = $u;
            if ($u->phone) $userMap[strtolower(trim($u->phone))] = $u;
            if ($m->nik_ktp) $userMap[strtolower(trim($m->nik_ktp))] = $u;
            if ($u->name) $userMap[strtolower(trim($u->name))] = $u;
            $userMap[(string) $u->id] = $u;
        }

        $parsedRows = [];
        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 2;

            // Flexible ID / Email / Phone matching
            $idRaw = '';
            foreach ($row as $k => $v) {
                if (in_array($k, [
                    'id_mesin_pin_nik_email', 'email_no_wa_nama_karyawan', 'identitas_karyawan',
                    'id_karyawan', 'id_mesin', 'pin', 'nik', 'email', 'user_id', 'username', 'karyawan'
                ]) || str_contains($k, 'mesin') || str_contains($k, 'identitas') || str_contains($k, 'email') || str_contains($k, 'pin')) {
                    $idRaw = trim((string) $v);
                    if ($idRaw !== '') break;
                }
            }
            if ($idRaw === '' && !empty($row)) {
                $idRaw = trim((string) reset($row));
            }

            $nameRaw = trim((string) ($row['nama_karyawan_referensi'] ?? $row['nama_karyawan'] ?? $row['nama_lengkap'] ?? $row['nama'] ?? ''));

            // Flexible Date matching
            $dateRaw = '';
            foreach ($row as $k => $v) {
                if (in_array($k, ['tanggal_yyyy_mm_dd', 'tanggal_presensi_yyyy_mm_dd', 'tanggal', 'tgl', 'date']) || str_contains($k, 'tanggal') || str_contains($k, 'tgl') || str_contains($k, 'date')) {
                    $dateRaw = trim((string) $v);
                    if ($dateRaw !== '') break;
                }
            }

            // Flexible Clock In & Out matching
            $inRaw = '';
            foreach ($row as $k => $v) {
                if (in_array($k, ['jam_masuk_hh_mm_hh_mm_ss', 'jam_masuk_hh_mm', 'jam_masuk', 'clock_in', 'in', 'masuk']) || str_contains($k, 'masuk') || str_contains($k, 'clock_in')) {
                    $inRaw = trim((string) $v);
                    if ($inRaw !== '') break;
                }
            }

            $outRaw = '';
            foreach ($row as $k => $v) {
                if (in_array($k, ['jam_keluar_hh_mm_hh_mm_ss', 'jam_keluar_hh_mm', 'jam_keluar', 'clock_out', 'out', 'keluar', 'pulang']) || str_contains($k, 'keluar') || str_contains($k, 'pulang') || str_contains($k, 'clock_out')) {
                    $outRaw = trim((string) $v);
                    if ($outRaw !== '') break;
                }
            }

            $statusRaw = trim((string) ($row['status_kehadiran'] ?? $row['status'] ?? ''));
            $shiftName = trim((string) ($row['nama_shift'] ?? $row['shift'] ?? 'Normal'));
            $notes = trim((string) ($row['catatan_mesin_lokasi'] ?? $row['catatan'] ?? 'Import Mesin Fingerprint'));

            // Match user
            $user = null;
            if ($idRaw !== '') {
                $user = $userMap[strtolower($idRaw)] ?? null;
            }
            if (! $user && $nameRaw !== '') {
                $user = $userMap[strtolower($nameRaw)] ?? null;
            }

            if (! $user) {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Karyawan Tak Dikenal',
                    'name' => $nameRaw ?: ($idRaw ?: 'ID Tak Diketahui'),
                    'code' => $idRaw ?: '-',
                    'category' => $shiftName,
                    'formatted_price' => $inRaw ? "Masuk: {$inRaw}" : '-',
                    'reason' => "Karyawan dengan identitas '{$idRaw}' tidak ditemukan di database staf bisnis Anda.",
                ];
                continue;
            }

            // Validate date and clock in
            $parsedDate = !empty($dateRaw) ? date('Y-m-d', strtotime($dateRaw)) : null;
            if (! $parsedDate || $parsedDate === '1970-01-01') {
                $errorCount++;
                $parsedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => 'error',
                    'status_label' => 'Tanggal Salah',
                    'name' => $user->name,
                    'code' => $user->email,
                    'category' => $shiftName,
                    'formatted_price' => '-',
                    'reason' => "Format tanggal '{$dateRaw}' tidak valid. Gunakan format YYYY-MM-DD.",
                ];
                continue;
            }

            // Build timestamps
            $inTime = !empty($inRaw) ? date('H:i:s', strtotime($inRaw)) : '08:00:00';
            $outTime = !empty($outRaw) ? date('H:i:s', strtotime($outRaw)) : null;

            $clockInAt = Carbon::parse("{$parsedDate} {$inTime}");
            $clockOutAt = $outTime ? Carbon::parse("{$parsedDate} {$outTime}") : null;

            // Calculate duration and late minutes
            $durationMinutes = 0;
            if ($clockOutAt && $clockOutAt->gt($clockInAt)) {
                $durationMinutes = (int) $clockInAt->diffInMinutes($clockOutAt);
            }

            $lateMinutes = 0;
            $scheduledStart = Carbon::parse("{$parsedDate} 08:00:00");
            if ($clockInAt->gt($scheduledStart)) {
                $lateMinutes = (int) $scheduledStart->diffInMinutes($clockInAt);
            }

            $attStatus = Attendance::STATUS_PRESENT;
            if ($lateMinutes > 15) {
                $attStatus = Attendance::STATUS_LATE;
            }

            // Check duplicate attendance for user on same date
            $existing = Attendance::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->where('date', $parsedDate)
                ->first();

            if ($existing) {
                $duplicateCount++;
                $status = 'duplicate';
                $statusLabel = 'Sudah Ada';
                $reason = "Data presensi {$user->name} pada tanggal {$parsedDate} sudah pernah tercatat. Pilih 'Perbarui Data' jika ingin menimpa jam kerja.";
            } else {
                $validCount++;
                $status = 'valid';
                $statusLabel = 'Siap Impor';
                $reason = 'Log presensi valid.';
            }

            $parsedRows[] = [
                'row_number' => $rowNumber,
                'status' => $status,
                'status_label' => $statusLabel,
                'user_id' => $user->id,
                'name' => $user->name,
                'code' => $user->email,
                'category' => $shiftName,
                'formatted_price' => "{$inTime} s/d " . ($outTime ?: 'Belum Out'),
                'date' => $parsedDate,
                'clock_in_at' => $clockInAt->toDateTimeString(),
                'clock_out_at' => $clockOutAt?->toDateTimeString(),
                'duration_minutes' => $durationMinutes,
                'late_minutes' => $lateMinutes,
                'attendance_status' => $attStatus,
                'shift_name' => $shiftName,
                'notes' => $notes,
                'reason' => $reason,
            ];
        }

        return [
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'duplicate_count' => $duplicateCount,
            'error_count' => $errorCount,
            'can_proceed' => ($validCount + $duplicateCount) > 0,
            'rows' => $parsedRows,
        ];
    }

    /**
     * Execute Fingerprint Attendance Import.
     */
    public function executeAttendanceImport(Business $business, array $rows, string $duplicateStrategy = 'skip'): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($business, $rows, $duplicateStrategy, &$imported, &$updated, &$skipped): void {
            foreach ($rows as $r) {
                if (($r['status'] ?? '') === 'error' || empty($r['user_id']) || empty($r['date'])) {
                    $skipped++;
                    continue;
                }

                $existing = Attendance::where('business_id', $business->id)
                    ->where('user_id', $r['user_id'])
                    ->where('date', $r['date'])
                    ->first();

                $attData = [
                    'business_id' => $business->id,
                    'user_id' => $r['user_id'],
                    'date' => $r['date'],
                    'shift_name' => $r['shift_name'] ?? 'Normal',
                    'clock_in_at' => $r['clock_in_at'],
                    'clock_out_at' => $r['clock_out_at'] ?? null,
                    'work_duration_minutes' => (int) ($r['duration_minutes'] ?? 0),
                    'late_minutes' => (int) ($r['late_minutes'] ?? 0),
                    'status' => $r['attendance_status'] ?? Attendance::STATUS_PRESENT,
                    'is_geofenced' => false,
                    'clock_in_notes' => $r['notes'] ?? 'Import Mesin Fingerprint',
                ];

                if ($existing) {
                    if ($duplicateStrategy === 'update') {
                        $existing->update($attData);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    Attendance::create($attData);
                    $imported++;
                }
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => Attendance::class,
                'auditable_id' => $business->id,
                'action' => 'import_attendance',
                'notes' => "Import presensi: {$imported} baru, {$updated} diperbarui, {$skipped} dilewati.",
                'risk_level' => AuditLog::RISK_LOW,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        });

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    // =========================================================================
    // SPRINT 3: LOGISTIK OMNICHANNEL & EKSPOR PENGIRIMAN (STOREFRONT & MARKETPLACE)
    // =========================================================================

    /**
     * Export Storefront Orders Dispatch Manifest for Couriers & Shipping.
     */
    public function exportStorefrontOrdersDispatch(Business $business, array $filters = []): StreamedResponse
    {
        $query = CommerceOrder::where('business_id', $business->id)->with('items');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        $orders = $query->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('MANIFEST_PENGIRIMAN');

        $headers = [
            'A1' => 'No. Pesanan',
            'B1' => 'Tgl Pesanan',
            'C1' => 'Nama Penerima',
            'D1' => 'No. HP Penerima',
            'E1' => 'Email Penerima',
            'F1' => 'Alamat Lengkap Pengiriman',
            'G1' => 'Kode Pos',
            'H1' => 'Ekspedisi / Kurir',
            'I1' => 'Layanan Kurir',
            'J1' => 'No. Resi (Waybill)',
            'K1' => 'Status Pengiriman',
            'L1' => 'Status Pembayaran',
            'M1' => 'Subtotal (Rp)',
            'N1' => 'Ongkir (Rp)',
            'O1' => 'Total Bayar (Rp)',
            'P1' => 'Rincian Produk',
            'Q1' => 'Catatan Pengiriman',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        foreach ($orders as $order) {
            $itemsSummary = $order->items->map(function ($it) {
                return ($it->product_name ?: 'Item') . ' (' . (float) $it->quantity . 'x)';
            })->implode(', ');

            $sheet->setCellValue("A{$rowNum}", $order->order_number);
            $sheet->setCellValue("B{$rowNum}", $order->created_at ? $order->created_at->format('Y-m-d H:i') : '-');
            $sheet->setCellValue("C{$rowNum}", $order->customer_name ?: '-');
            $sheet->setCellValue("D{$rowNum}", $order->customer_phone ?: '-');
            $sheet->setCellValue("E{$rowNum}", $order->customer_email ?: '-');
            $sheet->setCellValue("F{$rowNum}", $order->shipping_address ?: '-');
            $sheet->setCellValue("G{$rowNum}", $order->destination_postal_code ?: '-');
            $sheet->setCellValue("H{$rowNum}", $order->shipping_courier_name ?: ($order->shipping_courier_code ?: 'Kurir Standar'));
            $sheet->setCellValue("I{$rowNum}", $order->shipping_courier_service ?: 'Reguler');
            $sheet->setCellValue("J{$rowNum}", $order->shipping_waybill_id ?: '-');
            $sheet->setCellValue("K{$rowNum}", strtoupper((string) ($order->shipping_status ?: $order->status)));
            $sheet->setCellValue("L{$rowNum}", strtoupper((string) $order->payment_status));
            $sheet->setCellValue("M{$rowNum}", (float) $order->subtotal);
            $sheet->setCellValue("N{$rowNum}", (float) $order->shipping_cost);
            $sheet->setCellValue("O{$rowNum}", (float) $order->total_amount);
            $sheet->setCellValue("P{$rowNum}", $itemsSummary ?: '-');
            $sheet->setCellValue("Q{$rowNum}", $order->shipping_notes ?: ($order->notes ?: '-'));

            $this->applyDataRowStyle($sheet, "A{$rowNum}:Q{$rowNum}", $rowNum % 2 === 0);
            $sheet->getStyle("M{$rowNum}:O{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $rowNum++;
        }

        foreach (['A' => 20, 'B' => 18, 'C' => 24, 'D' => 18, 'E' => 24, 'F' => 36, 'G' => 12, 'H' => 18, 'I' => 16, 'J' => 20, 'K' => 18, 'L' => 18, 'M' => 16, 'N' => 16, 'O' => 18, 'P' => 36, 'Q' => 28] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        return $this->streamSpreadsheet($spreadsheet, "Manifest_Pengiriman_Toko_{$business->id}.xlsx");
    }

    /**
     * Export Marketplace Multi-Channel Consolidated Orders.
     */
    public function exportMarketplaceOrders(Business $business, array $filters = []): StreamedResponse
    {
        $query = MarketplaceOrder::where('business_id', $business->id);

        if (!empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }
        if (!empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }
        if (!empty($filters['start_date'])) {
            $query->whereDate('placed_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('placed_at', '<=', $filters['end_date']);
        }

        $orders = $query->orderByDesc('placed_at')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PESANAN_MARKETPLACE');

        $headers = [
            'A1' => 'Saluran Marketplace',
            'B1' => 'No. Pesanan Marketplace (SN)',
            'C1' => 'Tgl Transaksi',
            'D1' => 'Nama Pembeli',
            'E1' => 'No. Telepon Pembeli',
            'F1' => 'Ekspedisi / Kurir',
            'G1' => 'No. Resi Pengiriman',
            'H1' => 'Total Belanja (Rp)',
            'I1' => 'Biaya Layanan / Admin (Rp)',
            'J1' => 'Pendapatan Bersih (Rp)',
            'K1' => 'Status Pesanan',
            'L1' => 'Ringkasan Produk',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $this->applyHeaderStyle($sheet, $cell, self::COLOR_PRIMARY);
        }
        $sheet->getRowDimension(1)->setRowHeight(32);

        $rowNum = 2;
        foreach ($orders as $order) {
            $itemsSummary = is_array($order->items_summary)
                ? collect($order->items_summary)->map(fn ($it) => ($it['name'] ?? 'Item') . ' (' . ($it['quantity'] ?? 1) . 'x)')->implode(', ')
                : (is_string($order->items_summary) ? $order->items_summary : '-');

            $netIncome = max(0.0, (float) $order->total_amount - (float) $order->channel_fee);

            $sheet->setCellValue("A{$rowNum}", $order->getChannelLabel());
            $sheet->setCellValue("B{$rowNum}", $order->external_order_sn ?: ($order->external_order_id ?: '-'));
            $sheet->setCellValue("C{$rowNum}", $order->placed_at ? $order->placed_at->format('Y-m-d H:i') : '-');
            $sheet->setCellValue("D{$rowNum}", $order->buyer_name ?: '-');
            $sheet->setCellValue("E{$rowNum}", $order->buyer_phone ?: '-');
            $sheet->setCellValue("F{$rowNum}", $order->shipping_provider ?: '-');
            $sheet->setCellValue("G{$rowNum}", $order->tracking_number ?: '-');
            $sheet->setCellValue("H{$rowNum}", (float) $order->total_amount);
            $sheet->setCellValue("I{$rowNum}", (float) $order->channel_fee);
            $sheet->setCellValue("J{$rowNum}", $netIncome);
            $sheet->setCellValue("K{$rowNum}", strtoupper((string) $order->order_status));
            $sheet->setCellValue("L{$rowNum}", $itemsSummary ?: '-');

            $this->applyDataRowStyle($sheet, "A{$rowNum}:L{$rowNum}", $rowNum % 2 === 0);
            $sheet->getStyle("H{$rowNum}:J{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $rowNum++;
        }

        foreach (['A' => 20, 'B' => 28, 'C' => 18, 'D' => 22, 'E' => 18, 'F' => 18, 'G' => 22, 'H' => 18, 'I' => 18, 'J' => 18, 'K' => 18, 'L' => 36] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        return $this->streamSpreadsheet($spreadsheet, "Konsolidasi_Pesanan_Marketplace_{$business->id}.xlsx");
    }

    // =========================================================================
    // 4. HELPER METHODS (EXCEL STYLING & STREAMING)
    // =========================================================================

    private function applyHeaderStyle($sheet, string $cell, string $bgColor): void
    {
        $sheet->getStyle($cell)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11, 'name' => 'Segoe UI'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER_COLOR]]],
        ]);
    }

    private function applyGuideRowStyle($sheet, string $cell): void
    {
        $sheet->getStyle($cell)->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '64748B'], 'size' => 10, 'name' => 'Segoe UI'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::GUIDE_ROW_COLOR]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER_COLOR]]],
        ]);
    }

    private function applyDataRowStyle($sheet, string $range, bool $isZebra): void
    {
        $style = [
            'font' => ['size' => 10, 'name' => 'Segoe UI'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER_COLOR]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
        if ($isZebra) {
            $style['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ZEBRA_COLOR]];
        }
        $sheet->getStyle($range)->applyFromArray($style);
    }

    private function applyDropdownValidation($sheet, string $cellRange, string $formula, string $promptTitle): void
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setPromptTitle($promptTitle);
        $validation->setPrompt('Silakan pilih dari daftar yang tersedia.');
        $validation->setFormula1($formula);
        $sheet->setDataValidation($cellRange, $validation);
    }

    private function buildGuideSheet(Spreadsheet $spreadsheet, string $title, array $guidelines): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($title);

        $sheet->setCellValue('A1', 'Panduan Pengisian Data & Pencegahan Kesalahan');
        $this->applyHeaderStyle($sheet, 'A1:B1', self::COLOR_PRIMARY);
        $sheet->mergeCells('A1:B1');
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getColumnDimension('A')->setWidth(26);
        $sheet->getColumnDimension('B')->setWidth(65);

        $r = 2;
        foreach ($guidelines as [$rule, $desc]) {
            $sheet->setCellValue("A{$r}", $rule);
            $sheet->setCellValue("B{$r}", $desc);
            $this->applyDataRowStyle($sheet, "A{$r}:B{$r}", $r % 2 === 0);
            $sheet->getRowDimension($r)->setRowHeight(24);
            $r++;
        }
    }

    private function streamSpreadsheet(Spreadsheet $spreadsheet, string $fileName): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function readSpreadsheetRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return [];
        }

        $headerRow = array_shift($rows);
        $normalizedHeaders = [];
        foreach ($headerRow as $col => $name) {
            $cleanName = strtolower(trim((string) $name));
            $cleanName = (string) preg_replace('/[^a-z0-9_]/', '_', $cleanName);
            $cleanName = (string) preg_replace('/_+/', '_', $cleanName);
            $cleanName = trim($cleanName, '_');
            $normalizedHeaders[$col] = $cleanName;
        }

        $result = [];
        foreach ($rows as $row) {
            // Check if row is sample/guide row (often row 2 has placeholder text)
            $rowValues = array_filter(array_values($row));
            if (empty($rowValues)) {
                continue;
            }

            $mapped = [];
            foreach ($normalizedHeaders as $col => $key) {
                if ($key !== '') {
                    $mapped[$key] = $row[$col] ?? null;
                }
            }
            $result[] = $mapped;
        }

        return $result;
    }
}
