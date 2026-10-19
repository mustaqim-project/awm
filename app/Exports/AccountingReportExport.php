<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Business;
use App\Models\ChartOfAccount;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AccountingReportExport
{
    private const COLOR_DARK_HEADER    = '1C1C1E'; // Apple Dark Onyx
    private const COLOR_BLUE_ACCENT    = '007AFF'; // iOS System Blue
    private const COLOR_EMERALD_BG     = 'ECFDF5'; // Light Emerald Mint
    private const COLOR_EMERALD_BORDER = '10B981'; // Emerald 500
    private const COLOR_EMERALD_TEXT   = '065F46'; // Deep Emerald
    private const COLOR_ROSE_BG        = 'FEF2F2'; // Light Rose
    private const COLOR_ROSE_BORDER    = 'F43F5E'; // Rose 500
    private const COLOR_ROSE_TEXT      = '9F1239'; // Deep Rose
    private const COLOR_AMBER_BG       = 'FFFBEB'; // Light Amber
    private const COLOR_AMBER_BORDER   = 'F59E0B'; // Amber 500
    private const COLOR_PURPLE_BG      = 'FAF5FF'; // Light Purple
    private const COLOR_PURPLE_BORDER  = '9333EA'; // Purple 600
    private const COLOR_SUBHEADER_BG   = 'F1F5F9'; // Slate 100
    private const COLOR_ZEBRA_BG       = 'F8FAFC'; // Slate 50
    private const COLOR_BORDER_LINE    = 'E2E8F0'; // Slate 200

    // =========================================================================
    // 1. NERACA KEUANGAN (BALANCE SHEET)
    // =========================================================================

    public function downloadBalanceSheet(Business $business, Carbon $asOfDate, array $data): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($business->name . ' - Cooca Accounting Suite')
            ->setLastModifiedBy($business->name)
            ->setTitle('Laporan Neraca Keuangan (Balance Sheet)')
            ->setSubject('Neraca Standar SAK EMKM')
            ->setDescription('Laporan Neraca Resmi multi-sheet per tanggal ' . $asOfDate->translatedFormat('d F Y'));

        // Sheet 1: Executive KPI & Summary
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan Eksekutif');
        $this->buildBalanceSheetSummary($sheet1, $business, $asOfDate, $data);

        // Sheet 2: SAK EMKM Standard Statement
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Neraca SAK EMKM');
        $this->buildBalanceSheetStandard($sheet2, $business, $asOfDate, $data);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Neraca_Keuangan_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $business->name) . '_' . $asOfDate->format('Ymd') . '.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    private function buildBalanceSheetSummary(Worksheet $sheet, Business $business, Carbon $asOfDate, array $data): void
    {
        $sheet->setShowGridLines(true);

        // Header Title Block
        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'RINGKASAN EKSEKUTIF NERACA KEUANGAN (BALANCE SHEET)');
        $sheet->setCellValue('B4', 'Standar Pelaporan: SAK EMKM  |  Posisi Per Tanggal: ' . $asOfDate->translatedFormat('d F Y') . '  |  Mata Uang: IDR (Rp)');
        $sheet->setCellValue('B5', 'Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i:s') . ' WIB  |  Cooca Enterprise Accounting');

        $sheet->getStyle('B2')->getFont()->setSize(15)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('B3')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('B4')->getFont()->setSize(9)->getColor()->setRGB('64748B');
        $sheet->getStyle('B5')->getFont()->setSize(8)->setItalic(true)->getColor()->setRGB('94A3B8');

        $summary = $data['summary'] ?? [];
        $totalAssets = (float) ($summary['total_assets'] ?? 0);
        $totalLiabilities = (float) ($summary['total_liabilities'] ?? 0);
        $totalEquity = (float) ($summary['total_equity'] ?? 0);
        $totalLiabAndEq = (float) ($summary['total_liabilities_and_equity'] ?? 0);
        $currentEarnings = (float) ($data['equity']['current_earnings'] ?? 0);
        $isBalanced = (bool) ($summary['is_balanced'] ?? false);
        $diff = (float) ($summary['difference'] ?? 0);

        // Bento KPI Cards (Row 7 to 10)
        // Card 1: Total Aset
        $this->renderBentoCard($sheet, 'B7:C10', 'TOTAL ASET (AKTIVA)', $totalAssets, 'Aset Lancar & Aset Tetap', self::COLOR_EMERALD_BG, self::COLOR_EMERALD_BORDER, self::COLOR_EMERALD_TEXT);
        // Card 2: Total Liabilitas
        $this->renderBentoCard($sheet, 'D7:E10', 'TOTAL LIABILITAS (KEWAJIBAN)', $totalLiabilities, 'Hutang Lancar & Jk. Panjang', self::COLOR_ROSE_BG, self::COLOR_ROSE_BORDER, self::COLOR_ROSE_TEXT);
        // Card 3: Total Ekuitas
        $this->renderBentoCard($sheet, 'F7:G10', 'TOTAL EKUITAS (MODAL)', $totalEquity, 'Modal + Laba Berjalan', self::COLOR_PURPLE_BG, self::COLOR_PURPLE_BORDER, self::COLOR_PURPLE_BORDER);
        // Card 4: Status Keseimbangan
        $balanceText = $isBalanced ? 'SEIMBANG (BALANCED)' : 'SELISIH: Rp ' . number_format(abs($diff), 0, ',', '.');
        $this->renderBentoCard($sheet, 'H7:I10', 'STATUS NERACA', $totalLiabAndEq, $balanceText, $isBalanced ? self::COLOR_EMERALD_BG : self::COLOR_ROSE_BG, $isBalanced ? self::COLOR_EMERALD_BORDER : self::COLOR_ROSE_BORDER, $isBalanced ? self::COLOR_EMERALD_TEXT : self::COLOR_ROSE_TEXT);

        // Ringkasan Komposisi Neraca (Row 13+)
        $sheet->setCellValue('B13', 'KOMPOSISI NERACA KEUANGAN');
        $sheet->mergeCells('B13:E13');
        $this->styleSectionHeader($sheet, 'B13:E13');

        $rows = [
            ['Total Aset Lancar (Current Assets)', (float) ($data['assets']['total_current'] ?? 0), $totalAssets > 0 ? round(((float) ($data['assets']['total_current'] ?? 0) / $totalAssets) * 100, 1) . '%' : '-'],
            ['Total Aset Tidak Lancar (Non-Current Assets)', (float) ($data['assets']['total_non_current'] ?? 0), $totalAssets > 0 ? round(((float) ($data['assets']['total_non_current'] ?? 0) / $totalAssets) * 100, 1) . '%' : '-'],
            ['TOTAL AKTIVA / ASET KESELURUHAN', $totalAssets, '100.0%'],
            ['Liabilitas Jangka Pendek (Short Term)', (float) ($data['liabilities']['total_current'] ?? 0), $totalLiabAndEq > 0 ? round(((float) ($data['liabilities']['total_current'] ?? 0) / $totalLiabAndEq) * 100, 1) . '%' : '-'],
            ['Liabilitas Jangka Panjang (Long Term)', (float) ($data['liabilities']['total_long_term'] ?? 0), $totalLiabAndEq > 0 ? round(((float) ($data['liabilities']['total_long_term'] ?? 0) / $totalLiabAndEq) * 100, 1) . '%' : '-'],
            ['TOTAL KEWAJIBAN / LIABILITAS', $totalLiabilities, $totalLiabAndEq > 0 ? round(($totalLiabilities / $totalLiabAndEq) * 100, 1) . '%' : '-'],
            ['Modal Dasar & Modal Tambahan Disetor', (float) ($data['equity']['total_base'] ?? 0), $totalLiabAndEq > 0 ? round(((float) ($data['equity']['total_base'] ?? 0) / $totalLiabAndEq) * 100, 1) . '%' : '-'],
            ['Laba Bersih Periode Berjalan (Current Earnings)', $currentEarnings, $totalLiabAndEq > 0 ? round(($currentEarnings / $totalLiabAndEq) * 100, 1) . '%' : '-'],
            ['TOTAL MODAL / EKUITAS', $totalEquity, $totalLiabAndEq > 0 ? round(($totalEquity / $totalLiabAndEq) * 100, 1) . '%' : '-'],
            ['TOTAL PASIVA (LIABILITAS + EKUITAS)', $totalLiabAndEq, '100.0%'],
            ['SELISIH BALANCE (ASET - PASIVA)', $diff, $isBalanced ? 'BALANCE (0)' : 'UNBALANCED'],
        ];

        $r = 14;
        foreach ($rows as $idx => $row) {
            $sheet->setCellValue("B{$r}", $row[0]);
            $sheet->mergeCells("B{$r}:C{$r}");
            $sheet->setCellValue("D{$r}", $row[1]);
            $sheet->setCellValue("E{$r}", $row[2]);

            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $isHighlight = in_array($idx, [2, 5, 8, 9, 10], true);
            if ($isHighlight) {
                $sheet->getStyle("B{$r}:E{$r}")->getFont()->setBold(true);
                $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            }
            if ($idx === 10) {
                $sheet->getStyle("B{$r}:E{$r}")->getFont()->getColor()->setRGB($isBalanced ? self::COLOR_EMERALD_TEXT : self::COLOR_ROSE_TEXT);
            }
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $r++;
        }

        // Side Navigation Guide
        $sheet->setCellValue('G13', 'CATATAN & KEPATUHAN STANDAR AKUNTANSI');
        $sheet->mergeCells('G13:I13');
        $this->styleSectionHeader($sheet, 'G13:I13');

        $notes = [
            ['SAK EMKM', 'Laporan Neraca disusun mengacu pada Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK EMKM) Ikatan Akuntan Indonesia (IAI).'],
            ['Laba Berjalan', 'Laba bersih berjalan dihitung secara otomatis dari seluruh akumulasi transaksi pendapatan, HPP, dan beban operasional hingga tanggal cut-off neraca.'],
            ['Validitas Audit', 'Jika status menunjukkan UNBALANCED, periksa apakah terdapat jurnal umum manual yang tidak seimbang atau akun tanpa klasifikasi tipe debit/kredit.'],
            ['Lembar Kerja Detail', 'Buka tab lembar kerja "Neraca SAK EMKM" untuk melihat rincian seluruh sub-akun buku besar aktif beserta nomor kodenya.'],
        ];

        $nr = 14;
        foreach ($notes as $n) {
            $sheet->setCellValue("G{$nr}", $n[0]);
            $sheet->setCellValue("H{$nr}", $n[1]);
            $sheet->mergeCells("H{$nr}:I{$nr}");

            $sheet->getStyle("G{$nr}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
            $sheet->getStyle("H{$nr}")->getFont()->setSize(9)->getColor()->setRGB('475569');
            $sheet->getStyle("H{$nr}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("G{$nr}:I{$nr}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $this->applyBorderThin($sheet, "G{$nr}:I{$nr}");
            $sheet->getRowDimension($nr)->setRowHeight(36);
            $nr++;
        }

        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    private function buildBalanceSheetStandard(Worksheet $sheet, Business $business, Carbon $asOfDate, array $data): void
    {
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'LAPORAN POSISI KEUANGAN / NERACA (BALANCE SHEET)');
        $sheet->setCellValue('B4', 'Per: ' . $asOfDate->translatedFormat('d F Y') . '  (Standar SAK EMKM)');
        $sheet->getStyle('B2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('B3')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $r = 6;
        $sheet->setCellValue("B{$r}", 'KODE');
        $sheet->setCellValue("C{$r}", 'KOMPONEN NERACA / NAMA AKUN');
        $sheet->setCellValue("D{$r}", 'SALDO SUB-AKUN (RP)');
        $sheet->setCellValue("E{$r}", 'JUMLAH TOTAL (RP)');
        $this->styleTableHeader($sheet, "B{$r}:E{$r}");
        $r++;

        $sheet->freezePane("B{$r}");

        // 1. ASET
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", '1. ASET (AKTIVA)');
        $sheet->mergeCells("C{$r}:E{$r}");
        $sheet->getStyle("C{$r}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $r++;

        // 1.1 Aset Lancar
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", '   Aset Lancar (Current Assets)');
        $sheet->getStyle("C{$r}")->getFont()->setBold(true);
        $r++;

        $currentAssetsStartRow = $r;
        foreach ($data['assets']['current'] ?? [] as $acc) {
            $sheet->setCellValue("B{$r}", $acc['code'] ?? '-');
            $sheet->setCellValue("C{$r}", '      ' . ($acc['name'] ?? '-'));
            $sheet->setCellValue("D{$r}", (float) ($acc['balance'] ?? 0));
            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }
        $currentAssetsEndRow = max($currentAssetsStartRow, $r - 1);

        $sheet->setCellValue("C{$r}", '   Total Aset Lancar');
        $sheet->setCellValue("E{$r}", "=SUM(D{$currentAssetsStartRow}:D{$currentAssetsEndRow})");
        $sheet->getStyle("C{$r}:E{$r}")->getFont()->setBold(true);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $totalCurrentAssetRow = $r;
        $r++;

        // 1.2 Aset Tidak Lancar
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", '   Aset Tidak Lancar / Tetap (Non-Current Assets)');
        $sheet->getStyle("C{$r}")->getFont()->setBold(true);
        $r++;

        $nonCurrentStartRow = $r;
        foreach ($data['assets']['non_current'] ?? [] as $acc) {
            $sheet->setCellValue("B{$r}", $acc['code'] ?? '-');
            $sheet->setCellValue("C{$r}", '      ' . ($acc['name'] ?? '-'));
            $sheet->setCellValue("D{$r}", (float) ($acc['balance'] ?? 0));
            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }
        $nonCurrentEndRow = max($nonCurrentStartRow, $r - 1);

        $sheet->setCellValue("C{$r}", '   Total Aset Tidak Lancar');
        $sheet->setCellValue("E{$r}", "=SUM(D{$nonCurrentStartRow}:D{$nonCurrentEndRow})");
        $sheet->getStyle("C{$r}:E{$r}")->getFont()->setBold(true);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $totalNonCurrentAssetRow = $r;
        $r++;

        // Grand Total Aset
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", 'TOTAL ASET (AKTIVA)');
        $sheet->setCellValue("E{$r}", "=E{$totalCurrentAssetRow}+E{$totalNonCurrentAssetRow}");
        $sheet->getStyle("B{$r}:E{$r}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::COLOR_EMERALD_TEXT);
        $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_EMERALD_BG);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyDoubleBottomBorder($sheet, "B{$r}:E{$r}");
        $grandTotalAssetRow = $r;
        $r += 2;

        // 2. LIABILITAS
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", '2. LIABILITAS (KEWAJIBAN)');
        $sheet->mergeCells("C{$r}:E{$r}");
        $sheet->getStyle("C{$r}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::COLOR_ROSE_TEXT);
        $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $r++;

        // 2.1 Liabilitas Jangka Pendek
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", '   Liabilitas Jangka Pendek (Current Liabilities)');
        $sheet->getStyle("C{$r}")->getFont()->setBold(true);
        $r++;

        $curLiabStartRow = $r;
        foreach ($data['liabilities']['current'] ?? [] as $acc) {
            $sheet->setCellValue("B{$r}", $acc['code'] ?? '-');
            $sheet->setCellValue("C{$r}", '      ' . ($acc['name'] ?? '-'));
            $sheet->setCellValue("D{$r}", (float) ($acc['balance'] ?? 0));
            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }
        $curLiabEndRow = max($curLiabStartRow, $r - 1);

        $sheet->setCellValue("C{$r}", '   Total Liabilitas Jangka Pendek');
        $sheet->setCellValue("E{$r}", "=SUM(D{$curLiabStartRow}:D{$curLiabEndRow})");
        $sheet->getStyle("C{$r}:E{$r}")->getFont()->setBold(true);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $totalCurLiabRow = $r;
        $r++;

        // 2.2 Liabilitas Jangka Panjang
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", '   Liabilitas Jangka Panjang (Long-Term Liabilities)');
        $sheet->getStyle("C{$r}")->getFont()->setBold(true);
        $r++;

        $longLiabStartRow = $r;
        foreach ($data['liabilities']['long_term'] ?? [] as $acc) {
            $sheet->setCellValue("B{$r}", $acc['code'] ?? '-');
            $sheet->setCellValue("C{$r}", '      ' . ($acc['name'] ?? '-'));
            $sheet->setCellValue("D{$r}", (float) ($acc['balance'] ?? 0));
            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }
        $longLiabEndRow = max($longLiabStartRow, $r - 1);

        $sheet->setCellValue("C{$r}", '   Total Liabilitas Jangka Panjang');
        $sheet->setCellValue("E{$r}", "=SUM(D{$longLiabStartRow}:D{$longLiabEndRow})");
        $sheet->getStyle("C{$r}:E{$r}")->getFont()->setBold(true);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $totalLongLiabRow = $r;
        $r++;

        // Grand Total Liabilitas
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", 'TOTAL LIABILITAS (KEWAJIBAN)');
        $sheet->setCellValue("E{$r}", "=E{$totalCurLiabRow}+E{$totalLongLiabRow}");
        $sheet->getStyle("B{$r}:E{$r}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB(self::COLOR_ROSE_TEXT);
        $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ROSE_BG);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $grandTotalLiabRow = $r;
        $r += 2;

        // 3. EKUITAS
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", '3. EKUITAS (MODAL)');
        $sheet->mergeCells("C{$r}:E{$r}");
        $sheet->getStyle("C{$r}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::COLOR_PURPLE_BORDER);
        $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $r++;

        $equityStartRow = $r;
        foreach ($data['equity']['accounts'] ?? [] as $acc) {
            $sheet->setCellValue("B{$r}", $acc['code'] ?? '-');
            $sheet->setCellValue("C{$r}", '   ' . ($acc['name'] ?? '-'));
            $sheet->setCellValue("D{$r}", (float) ($acc['balance'] ?? 0));
            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }
        $equityEndRow = max($equityStartRow, $r - 1);

        // Laba Bersih Berjalan
        $sheet->setCellValue("B{$r}", '-');
        $sheet->setCellValue("C{$r}", '   Laba Bersih Periode Berjalan (Current Earnings)');
        $sheet->setCellValue("D{$r}", (float) ($data['equity']['current_earnings'] ?? 0));
        $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $sheet->getStyle("C{$r}:D{$r}")->getFont()->setItalic(true);
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $currentEarningsRow = $r;
        $r++;

        // Grand Total Ekuitas
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", 'TOTAL EKUITAS (MODAL)');
        $sheet->setCellValue("E{$r}", "=SUM(D{$equityStartRow}:D{$equityEndRow})+D{$currentEarningsRow}");
        $sheet->getStyle("B{$r}:E{$r}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB(self::COLOR_PURPLE_BORDER);
        $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_PURPLE_BG);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");
        $grandTotalEquityRow = $r;
        $r += 2;

        // TOTAL PASIVA (LIABILITAS + EKUITAS)
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", 'TOTAL LIABILITAS DAN EKUITAS (PASIVA)');
        $sheet->setCellValue("E{$r}", "=E{$grandTotalLiabRow}+E{$grandTotalEquityRow}");
        $sheet->getStyle("B{$r}:E{$r}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyDoubleBottomBorder($sheet, "B{$r}:E{$r}");
        $grandTotalPasivaRow = $r;
        $r += 2;

        // STATUS KESEIMBANGAN NERACA
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", 'KONTROL KESEIMBANGAN (ASET - PASIVA)');
        $sheet->setCellValue("E{$r}", "=E{$grandTotalAssetRow}-E{$grandTotalPasivaRow}");
        $sheet->getStyle("B{$r}:E{$r}")->getFont()->setBold(true);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:E{$r}");

        foreach (['B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    // =========================================================================
    // 2. NERACA SALDO (TRIAL BALANCE)
    // =========================================================================

    public function downloadTrialBalance(Business $business, Carbon $startDate, Carbon $endDate, array $data): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($business->name . ' - Cooca Accounting Suite')
            ->setLastModifiedBy($business->name)
            ->setTitle('Laporan Neraca Saldo (Trial Balance)')
            ->setSubject('Trial Balance Multi-Kolom')
            ->setDescription('Neraca Saldo Periode ' . $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y'));

        // Sheet 1: Summary Bento
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan');
        $this->buildTrialBalanceSummary($sheet1, $business, $startDate, $endDate, $data);

        // Sheet 2: Multi-Column Trial Balance
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Neraca Saldo');
        $this->buildTrialBalanceTable($sheet2, $business, $startDate, $endDate, $data);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Neraca_Saldo_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $business->name) . '_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    private function buildTrialBalanceSummary(Worksheet $sheet, Business $business, Carbon $startDate, Carbon $endDate, array $data): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'RINGKASAN EKSEKUTIF NERACA SALDO (TRIAL BALANCE)');
        $sheet->setCellValue('B4', 'Periode Mutasi: ' . $startDate->translatedFormat('d F Y') . ' s/d ' . $endDate->translatedFormat('d F Y'));
        $sheet->setCellValue('B5', 'Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i:s') . ' WIB  |  Cooca Enterprise Accounting');

        $sheet->getStyle('B2')->getFont()->setSize(15)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('B3')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('B4')->getFont()->setSize(9)->getColor()->setRGB('64748B');
        $sheet->getStyle('B5')->getFont()->setSize(8)->setItalic(true)->getColor()->setRGB('94A3B8');

        $totals = $data['totals'] ?? [];
        $begDebit = (float) ($totals['beginning_debit'] ?? 0);
        $begCredit = (float) ($totals['beginning_credit'] ?? 0);
        $movDebit = (float) ($totals['movement_debit'] ?? 0);
        $movCredit = (float) ($totals['movement_credit'] ?? 0);
        $endDebit = (float) ($totals['ending_debit'] ?? 0);
        $endCredit = (float) ($totals['ending_credit'] ?? 0);
        $isBalanced = (bool) ($totals['is_balanced'] ?? false);
        $diff = (float) ($totals['difference'] ?? 0);

        // Bento Cards
        $this->renderBentoCard($sheet, 'B7:C10', 'TOTAL SALDO AKHIR DEBET', $endDebit, 'Akumulasi Saldo Normal Debet', self::COLOR_EMERALD_BG, self::COLOR_EMERALD_BORDER, self::COLOR_EMERALD_TEXT);
        $this->renderBentoCard($sheet, 'D7:E10', 'TOTAL SALDO AKHIR KREDIT', $endCredit, 'Akumulasi Saldo Normal Kredit', self::COLOR_BLUE_ACCENT ? 'EFF6FF' : 'EFF6FF', self::COLOR_BLUE_ACCENT, self::COLOR_BLUE_ACCENT);
        $this->renderBentoCard($sheet, 'F7:G10', 'TOTAL MUTASI PERIODE', $movDebit, 'Total Debet: Rp ' . number_format($movDebit, 0, ',', '.'), self::COLOR_PURPLE_BG, self::COLOR_PURPLE_BORDER, self::COLOR_PURPLE_BORDER);
        
        $balanceText = $isBalanced ? 'SEIMBANG (DEBET = KREDIT)' : 'SELISIH: Rp ' . number_format(abs($diff), 0, ',', '.');
        $this->renderBentoCard($sheet, 'H7:I10', 'STATUS NERACA SALDO', abs($diff), $balanceText, $isBalanced ? self::COLOR_EMERALD_BG : self::COLOR_ROSE_BG, $isBalanced ? self::COLOR_EMERALD_BORDER : self::COLOR_ROSE_BORDER, $isBalanced ? self::COLOR_EMERALD_TEXT : self::COLOR_ROSE_TEXT);

        // Summary Comparison Table (Row 13+)
        $sheet->setCellValue('B13', 'REKAPITULASI NERACA SALDO 6-KOLOM');
        $sheet->mergeCells('B13:E13');
        $this->styleSectionHeader($sheet, 'B13:E13');

        $rows = [
            ['Total Saldo Awal (Beginning Balance)', $begDebit, $begCredit, abs($begDebit - $begCredit) < 0.05 ? 'SEIMBANG' : 'SELISIH'],
            ['Total Mutasi Periode Berjalan (Movement)', $movDebit, $movCredit, abs($movDebit - $movCredit) < 0.05 ? 'SEIMBANG' : 'SELISIH'],
            ['Total Saldo Akhir (Ending Balance)', $endDebit, $endCredit, $isBalanced ? 'SEIMBANG' : 'SELISIH'],
            ['Selisih Saldo Akhir (Debet - Kredit)', $diff, '-', $isBalanced ? 'BALANCE' : 'UNBALANCED'],
        ];

        $r = 14;
        $sheet->setCellValue("B{$r}", 'Posisi Akuntansi');
        $sheet->setCellValue("C{$r}", 'Debet (Rp)');
        $sheet->setCellValue("D{$r}", 'Kredit (Rp)');
        $sheet->setCellValue("E{$r}", 'Status');
        $this->styleTableHeader($sheet, "B{$r}:E{$r}");
        $r++;

        foreach ($rows as $idx => $row) {
            $sheet->setCellValue("B{$r}", $row[0]);
            $sheet->setCellValue("C{$r}", $row[1]);
            $sheet->setCellValue("D{$r}", $row[2]);
            $sheet->setCellValue("E{$r}", $row[3]);

            $sheet->getStyle("C{$r}:D{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $isHighlight = in_array($idx, [2, 3], true);
            if ($isHighlight) {
                $sheet->getStyle("B{$r}:E{$r}")->getFont()->setBold(true);
                $sheet->getStyle("B{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
            }
            if ($idx === 3) {
                $sheet->getStyle("B{$r}:E{$r}")->getFont()->getColor()->setRGB($isBalanced ? self::COLOR_EMERALD_TEXT : self::COLOR_ROSE_TEXT);
            }
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $r++;
        }

        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    private function buildTrialBalanceTable(Worksheet $sheet, Business $business, Carbon $startDate, Carbon $endDate, array $data): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'NERACA SALDO LENGKAP (TRIAL BALANCE DETAIL)');
        $sheet->setCellValue('B4', 'Periode: ' . $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y'));

        $sheet->getStyle('B2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('B3')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        // Multi-level Header
        // Row 6: Super Headers
        $sheet->setCellValue('B6', 'AKUN BUKU BESAR');
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('F6', 'SALDO AWAL');
        $sheet->mergeCells('F6:G6');
        $sheet->setCellValue('H6', 'MUTASI PERIODE');
        $sheet->mergeCells('H6:I6');
        $sheet->setCellValue('J6', 'SALDO AKHIR');
        $sheet->mergeCells('J6:K6');

        $this->styleTableHeader($sheet, 'B6:K6');

        // Row 7: Sub Headers
        $sheet->setCellValue('B7', 'NO');
        $sheet->setCellValue('C7', 'KODE AKUN');
        $sheet->setCellValue('D7', 'NAMA AKUN BUKU BESAR');
        $sheet->setCellValue('E7', 'KLASIFIKASI');
        $sheet->setCellValue('F7', 'DEBET (RP)');
        $sheet->setCellValue('G7', 'KREDIT (RP)');
        $sheet->setCellValue('H7', 'DEBET (RP)');
        $sheet->setCellValue('I7', 'KREDIT (RP)');
        $sheet->setCellValue('J7', 'DEBET (RP)');
        $sheet->setCellValue('K7', 'KREDIT (RP)');

        $sheet->getStyle('B7:K7')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('B7:K7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('334155');
        $sheet->getStyle('B7:K7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $this->applyBorderThin($sheet, 'B7:K7');

        $sheet->freezePane('B8');

        $r = 8;
        $dataStartRow = $r;
        $rows = $data['rows'] ?? [];

        foreach ($rows as $idx => $row) {
            $sheet->setCellValue("B{$r}", $idx + 1);
            $sheet->setCellValue("C{$r}", $row['code'] ?? '-');
            $sheet->setCellValue("D{$r}", $row['name'] ?? '-');
            $sheet->setCellValue("E{$r}", $row['type_label'] ?? $row['type'] ?? '-');
            $sheet->setCellValue("F{$r}", (float) ($row['beginning_debit'] ?? 0));
            $sheet->setCellValue("G{$r}", (float) ($row['beginning_credit'] ?? 0));
            $sheet->setCellValue("H{$r}", (float) ($row['movement_debit'] ?? 0));
            $sheet->setCellValue("I{$r}", (float) ($row['movement_credit'] ?? 0));
            $sheet->setCellValue("J{$r}", (float) ($row['ending_debit'] ?? 0));
            $sheet->setCellValue("K{$r}", (float) ($row['ending_credit'] ?? 0));

            $sheet->getStyle("F{$r}:K{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet->getStyle("B{$r}:C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($idx % 2 === 1) {
                $sheet->getStyle("B{$r}:K{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $this->applyBorderThin($sheet, "B{$r}:K{$r}");
            $r++;
        }
        $dataEndRow = max($dataStartRow, $r - 1);

        // TOTALS ROW
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", 'TOTAL');
        $sheet->setCellValue("D{$r}", 'TOTAL KESELURUHAN NERACA SALDO');
        $sheet->setCellValue("E{$r}", count($rows) . ' Akun');
        $sheet->setCellValue("F{$r}", "=SUM(F{$dataStartRow}:F{$dataEndRow})");
        $sheet->setCellValue("G{$r}", "=SUM(G{$dataStartRow}:G{$dataEndRow})");
        $sheet->setCellValue("H{$r}", "=SUM(H{$dataStartRow}:H{$dataEndRow})");
        $sheet->setCellValue("I{$r}", "=SUM(I{$dataStartRow}:I{$dataEndRow})");
        $sheet->setCellValue("J{$r}", "=SUM(J{$dataStartRow}:J{$dataEndRow})");
        $sheet->setCellValue("K{$r}", "=SUM(K{$dataStartRow}:K{$dataEndRow})");

        $sheet->getStyle("B{$r}:K{$r}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle("B{$r}:K{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("F{$r}:K{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyDoubleBottomBorder($sheet, "B{$r}:K{$r}");

        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    // =========================================================================
    // 3. BUKU BESAR UMUM (GENERAL LEDGER)
    // =========================================================================

    public function downloadGeneralLedger(Business $business, ?ChartOfAccount $account, Carbon $startDate, Carbon $endDate, array $data): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $accCode = $account?->code ?? ($data['selected_account']->code ?? 'All');
        $accName = $account?->name ?? ($data['selected_account']->name ?? 'Buku Besar');

        $spreadsheet->getProperties()
            ->setCreator($business->name . ' - Cooca Accounting Suite')
            ->setLastModifiedBy($business->name)
            ->setTitle("Buku Besar - {$accCode} {$accName}")
            ->setSubject('General Ledger Drilldown Ledger')
            ->setDescription("Buku Besar Akun {$accCode} Periode {$startDate->translatedFormat('d M Y')} s/d {$endDate->translatedFormat('d M Y')}");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Besar');
        $this->buildGeneralLedgerSheet($sheet, $business, $account, $startDate, $endDate, $data);

        $filename = 'Buku_Besar_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $accCode . '_' . $accName) . '_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    private function buildGeneralLedgerSheet(Worksheet $sheet, Business $business, ?ChartOfAccount $account, Carbon $startDate, Carbon $endDate, array $data): void
    {
        $sheet->setShowGridLines(true);

        $selectedAccount = $account ?? ($data['selected_account'] ?? null);
        $accCode = $selectedAccount?->code ?? '-';
        $accName = $selectedAccount?->name ?? 'Seluruh Akun';
        $accType = $selectedAccount ? $selectedAccount->getTypeLabel() : '-';
        $normalBal = $selectedAccount?->normal_balance ? strtoupper($selectedAccount->normal_balance) : 'DEBET';

        // Header Title
        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', "BUKU BESAR UMUM: {$accCode} - {$accName}");
        $sheet->setCellValue('B4', "Klasifikasi: {$accType}  |  Saldo Normal: {$normalBal}  |  Periode: " . $startDate->translatedFormat('d F Y') . ' s/d ' . $endDate->translatedFormat('d F Y'));
        $sheet->setCellValue('B5', 'Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i:s') . ' WIB  |  Cooca Enterprise Accounting');

        $sheet->getStyle('B2')->getFont()->setSize(15)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('B3')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('B4')->getFont()->setSize(9)->getColor()->setRGB('64748B');
        $sheet->getStyle('B5')->getFont()->setSize(8)->setItalic(true)->getColor()->setRGB('94A3B8');

        $begBalance = (float) ($data['beginning_balance'] ?? 0);
        $totalDebit = (float) ($data['total_debit'] ?? 0);
        $totalCredit = (float) ($data['total_credit'] ?? 0);
        $endBalance = (float) ($data['ending_balance'] ?? 0);

        // Bento KPI Cards (Row 7 to 10)
        $this->renderBentoCard($sheet, 'B7:C10', 'SALDO AWAL (SEBELUM PERIODE)', $begBalance, 'Saldo per ' . $startDate->subDay()->translatedFormat('d M Y'), self::COLOR_SUBHEADER_BG, '94A3B8', self::COLOR_DARK_HEADER);
        $this->renderBentoCard($sheet, 'D7:E10', 'TOTAL MUTASI DEBET', $totalDebit, 'Akumulasi Transaksi Debet', self::COLOR_EMERALD_BG, self::COLOR_EMERALD_BORDER, self::COLOR_EMERALD_TEXT);
        $this->renderBentoCard($sheet, 'F7:G10', 'TOTAL MUTASI KREDIT', $totalCredit, 'Akumulasi Transaksi Kredit', self::COLOR_ROSE_BG, self::COLOR_ROSE_BORDER, self::COLOR_ROSE_TEXT);
        $this->renderBentoCard($sheet, 'H7:I10', 'SALDO AKHIR BUKU BESAR', $endBalance, 'Saldo Berjalan Terkini', self::COLOR_BLUE_ACCENT ? 'EFF6FF' : 'EFF6FF', self::COLOR_BLUE_ACCENT, self::COLOR_BLUE_ACCENT);

        // Table Header (Row 12)
        $r = 12;
        $sheet->setCellValue("B{$r}", 'NO');
        $sheet->setCellValue("C{$r}", 'TANGGAL');
        $sheet->setCellValue("D{$r}", 'NO. JURNAL / BUKTI');
        $sheet->setCellValue("E{$r}", 'DOKUMEN SUMBER');
        $sheet->setCellValue("F{$r}", 'KETERANGAN TRANSAKSI');
        $sheet->setCellValue("G{$r}", 'CATATAN BARIS');
        $sheet->setCellValue("H{$r}", 'DEBET (RP)');
        $sheet->setCellValue("I{$r}", 'KREDIT (RP)');
        $sheet->setCellValue("J{$r}", 'SALDO BERJALAN (RP)');

        $this->styleTableHeader($sheet, "B{$r}:J{$r}");
        $r++;

        $sheet->freezePane("B{$r}");

        // Saldo Awal Row
        $sheet->setCellValue("B{$r}", '-');
        $sheet->setCellValue("C{$r}", $startDate->translatedFormat('d M Y'));
        $sheet->setCellValue("D{$r}", 'SALDO AWAL');
        $sheet->setCellValue("E{$r}", 'CARRY OVER');
        $sheet->setCellValue("F{$r}", 'Saldo Awal Akun sebelum Periode Pelaporan');
        $sheet->setCellValue("G{$r}", '-');
        $sheet->setCellValue("H{$r}", 0);
        $sheet->setCellValue("I{$r}", 0);
        $sheet->setCellValue("J{$r}", $begBalance);

        $sheet->getStyle("B{$r}:J{$r}")->getFont()->setItalic(true)->setBold(true)->getColor()->setRGB('475569');
        $sheet->getStyle("B{$r}:J{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("H{$r}:J{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyBorderThin($sheet, "B{$r}:J{$r}");
        $r++;

        $dataStartRow = $r;
        $lines = $data['lines'] ?? [];

        foreach ($lines as $idx => $line) {
            $sheet->setCellValue("B{$r}", $idx + 1);
            $sheet->setCellValue("C{$r}", $line['entry_date'] ?? '-');
            $sheet->setCellValue("D{$r}", $line['entry_number'] ?? '-');
            $sheet->setCellValue("E{$r}", $line['reference_type_label'] ?? 'JURNAL');
            $sheet->setCellValue("F{$r}", $line['description'] ?? '-');
            $sheet->setCellValue("G{$r}", $line['notes'] ?? '-');
            $sheet->setCellValue("H{$r}", (float) ($line['debit'] ?? 0));
            $sheet->setCellValue("I{$r}", (float) ($line['credit'] ?? 0));
            $sheet->setCellValue("J{$r}", (float) ($line['running_balance'] ?? 0));

            $sheet->getStyle("H{$r}:J{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet->getStyle("B{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($idx % 2 === 1) {
                $sheet->getStyle("B{$r}:J{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $this->applyBorderThin($sheet, "B{$r}:J{$r}");
            $r++;
        }
        $dataEndRow = max($dataStartRow, $r - 1);

        // TOTALS ROW
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", 'TOTAL MUTASI');
        $sheet->setCellValue("D{$r}", count($lines) . ' Baris Jurnal');
        $sheet->setCellValue("E{$r}", '');
        $sheet->setCellValue("F{$r}", 'Total Pergerakan Periode Ini');
        $sheet->setCellValue("G{$r}", '');
        $sheet->setCellValue("H{$r}", "=SUM(H{$dataStartRow}:H{$dataEndRow})");
        $sheet->setCellValue("I{$r}", "=SUM(I{$dataStartRow}:I{$dataEndRow})");
        $sheet->setCellValue("J{$r}", (count($lines) > 0 ? "=J{$dataEndRow}" : "=J" . ($dataStartRow - 1)));

        $sheet->getStyle("B{$r}:J{$r}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle("B{$r}:J{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("H{$r}:J{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyDoubleBottomBorder($sheet, "B{$r}:J{$r}");

        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    // =========================================================================
    // HELPER STYLING METHODS (APPLE HIG BENTO & EXCEL STANDARDS)
    // =========================================================================

    private function renderBentoCard(
        Worksheet $sheet,
        string $range,
        string $title,
        float $value,
        string $subtitle,
        string $bgColor,
        string $borderColor,
        string $textColor
    ): void {
        [$startCol, $startRow, $endCol, $endRow] = $this->parseRange($range);

        $sheet->mergeCells($range);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($borderColor);

        // Unmerge for 3-line structured display
        $sheet->unmergeCells($range);

        $row1 = $startRow;
        $row2 = $startRow + 1;
        $row3 = $startRow + 2;

        $sheet->mergeCells("{$startCol}{$row1}:{$endCol}{$row1}");
        $sheet->mergeCells("{$startCol}{$row2}:{$endCol}{$row2}");
        $sheet->mergeCells("{$startCol}{$row3}:{$endCol}{$row3}");

        $sheet->setCellValue("{$startCol}{$row1}", mb_strtoupper($title));
        $sheet->setCellValue("{$startCol}{$row2}", $value);
        $sheet->setCellValue("{$startCol}{$row3}", $subtitle);

        $sheet->getStyle("{$startCol}{$row1}")->getFont()->setSize(8)->setBold(true)->getColor()->setRGB('64748B');
        $sheet->getStyle("{$startCol}{$row2}")->getFont()->setSize(16)->setBold(true)->getColor()->setRGB($textColor);
        $sheet->getStyle("{$startCol}{$row2}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $sheet->getStyle("{$startCol}{$row3}")->getFont()->setSize(8)->getColor()->setRGB('64748B');

        foreach ([$row1, $row2, $row3] as $r) {
            $sheet->getStyle("{$startCol}{$r}:{$endCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);
            $sheet->getStyle("{$startCol}{$r}:{$endCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
        }

        $sheet->getStyle("{$startCol}{$startRow}:{$endCol}{$endRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($borderColor);
    }

    private function styleTableHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('475569');
        $sheet->getRowDimension((int) preg_replace('/\D/', '', explode(':', $range)[0]))->setRowHeight(24);
    }

    private function styleSectionHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
        $sheet->getRowDimension((int) preg_replace('/\D/', '', explode(':', $range)[0]))->setRowHeight(22);
    }

    private function applyBorderThin(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDER_LINE);
    }

    private function applyDoubleBottomBorder(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDER_LINE);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle($range)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDER_LINE);
        $sheet->getStyle($range)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDER_LINE);
    }

    private function parseRange(string $range): array
    {
        [$start, $end] = explode(':', $range);
        preg_match('/([A-Z]+)(\d+)/', $start, $m1);
        preg_match('/([A-Z]+)(\d+)/', $end, $m2);

        return [$m1[1], (int) $m1[2], $m2[1], (int) $m2[2]];
    }
}
