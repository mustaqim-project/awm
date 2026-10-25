<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Business;
use App\Models\Invoice;
use App\Support\Security\CsvSanitizer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoiceReportExport
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

    /**
     * Download comprehensive 2-Sheet Invoice Workbook.
     *
     * @param Collection<int, Invoice> $invoices
     */
    public function download(Business $business, Collection $invoices): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($business->name . ' - Cooca Invoice Suite')
            ->setLastModifiedBy($business->name)
            ->setTitle('Laporan Faktur Penjualan & Piutang')
            ->setSubject('Invoice & Accounts Receivable Ledger')
            ->setDescription('Master Laporan Faktur Penjualan: Ringkasan Eksekutif & Buku Faktur Detail.');

        // Sheet 1: Executive Summary & KPIs
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan Eksekutif');
        $this->buildExecutiveSummarySheet($sheet1, $business, $invoices);

        // Sheet 2: Invoice Detail Ledger
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Buku Faktur Penjualan');
        $this->buildInvoiceLedgerSheet($sheet2, $business, $invoices);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Laporan_Faktur_Penjualan_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $business->name) . '_' . date('Ymd_His') . '.xlsx';

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

    /**
     * Sheet 1: Executive KPI Cards and Aging Overview.
     *
     * @param Collection<int, Invoice> $invoices
     */
    private function buildExecutiveSummarySheet(Worksheet $sheet, Business $business, Collection $invoices): void
    {
        $sheet->setShowGridLines(true);

        // Header Title
        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'RINGKASAN EKSEKUTIF FAKTUR PENJUALAN & PIUTANG USAHA');
        $sheet->setCellValue('B4', 'Mata Uang: ' . $business->currency_code . ' (' . $business->currency_symbol . ')  |  Total Data: ' . $invoices->count() . ' Faktur');
        $sheet->setCellValue('B5', 'Waktu Export: ' . Carbon::now()->translatedFormat('d F Y, H:i:s') . ' WIB  |  Cooca Commercial Suite');

        $sheet->getStyle('B2')->getFont()->setSize(15)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('B3')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('B4')->getFont()->setSize(9)->getColor()->setRGB('64748B');
        $sheet->getStyle('B5')->getFont()->setSize(8)->setItalic(true)->getColor()->setRGB('94A3B8');

        $totalInvoiced = (float) $invoices->sum('total_amount');
        $totalPaid = (float) $invoices->sum('paid_amount');
        $totalBalanceDue = (float) $invoices->sum('balance_due');
        $totalGrossProfit = (float) $invoices->sum('total_gross_profit');
        $count = $invoices->count();
        $avgValue = $count > 0 ? $totalInvoiced / $count : 0.0;

        // Bento Cards
        $this->renderBentoCard($sheet, 'B7:C10', 'TOTAL TAGIHAN FAKTUR', $totalInvoiced, 'Nilai Bruto Seluruh Faktur', self::COLOR_BLUE_ACCENT ? 'EFF6FF' : 'EFF6FF', self::COLOR_BLUE_ACCENT, self::COLOR_BLUE_ACCENT);
        $this->renderBentoCard($sheet, 'D7:E10', 'TOTAL SUDAH DILUNASI', $totalPaid, 'Penerimaan Kas & Bank', self::COLOR_EMERALD_BG, self::COLOR_EMERALD_BORDER, self::COLOR_EMERALD_TEXT);
        $this->renderBentoCard($sheet, 'F7:G10', 'SISA PIUTANG BEREDAR (AR)', $totalBalanceDue, 'Tagihan Belum Lunas', self::COLOR_ROSE_BG, self::COLOR_ROSE_BORDER, self::COLOR_ROSE_TEXT);
        $this->renderBentoCard($sheet, 'H7:I10', 'ESTIMASI LABA KOTOR', $totalGrossProfit, 'Total Margin Faktur Riil', self::COLOR_PURPLE_BG, self::COLOR_PURPLE_BORDER, self::COLOR_PURPLE_BORDER);

        // Status Breakdown Table
        $sheet->setCellValue('B13', 'STATUS PENAGIHAN FAKTUR');
        $sheet->mergeCells('B13:E13');
        $this->styleSectionHeader($sheet, 'B13:E13');

        $byStatus = $invoices->groupBy(fn ($inv) => strtolower((string) $inv->status));
        $statuses = ['paid' => 'Lunas (Paid)', 'partial' => 'Sebagian (Partial)', 'sent' => 'Terkirim (Sent)', 'draft' => 'Konsep (Draft)', 'void' => 'Batal (Void)', 'cancelled' => 'Dibatalkan'];

        $r = 14;
        $sheet->setCellValue("B{$r}", 'Status');
        $sheet->setCellValue("C{$r}", 'Jumlah Faktur');
        $sheet->setCellValue("D{$r}", 'Total Tagihan (Rp)');
        $sheet->setCellValue("E{$r}", 'Sisa Piutang (Rp)');
        $this->styleTableHeader($sheet, "B{$r}:E{$r}");
        $r++;

        foreach ($statuses as $stKey => $stLabel) {
            $group = $byStatus->get($stKey, collect());
            if ($group->isEmpty()) {
                continue;
            }

            $sheet->setCellValue("B{$r}", $stLabel);
            $sheet->setCellValue("C{$r}", $group->count());
            $sheet->setCellValue("D{$r}", (float) $group->sum('total_amount'));
            $sheet->setCellValue("E{$r}", (float) $group->sum('balance_due'));

            $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$r}:E{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $this->applyBorderThin($sheet, "B{$r}:E{$r}");
            $r++;
        }

        // Auto-fit widths
        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

    /**
     * Sheet 2: Detailed Invoice Table.
     *
     * @param Collection<int, Invoice> $invoices
     */
    private function buildInvoiceLedgerSheet(Worksheet $sheet, Business $business, Collection $invoices): void
    {
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('B2', strtoupper($business->name));
        $sheet->setCellValue('B3', 'BUKU BESAR FAKTUR PENJUALAN LENGKAP');
        $sheet->setCellValue('B4', 'Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB');

        $sheet->getStyle('B2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle('B3')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB(self::COLOR_BLUE_ACCENT);
        $sheet->getStyle('B4')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $r = 6;
        $headers = [
            'B' => 'NO',
            'C' => 'NO. FAKTUR',
            'D' => 'TGL FAKTUR',
            'E' => 'JATUH TEMPO',
            'F' => 'NAMA PELANGGAN',
            'G' => 'PERUSAHAAN',
            'H' => 'NO. PO REFERENSI',
            'I' => 'STATUS',
            'J' => 'SUBTOTAL (RP)',
            'K' => 'DISKON (RP)',
            'L' => 'PPN / PAJAK (RP)',
            'M' => 'ONGKIR (RP)',
            'N' => 'TOTAL TAGIHAN (RP)',
            'O' => 'SUDAH DIBAYAR (RP)',
            'P' => 'SISA PIUTANG (RP)',
            'Q' => 'TOTAL HPP (RP)',
            'R' => 'LABA KOTOR (RP)',
            'S' => 'TERMIN BAYAR',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}{$r}", $title);
        }
        $this->styleTableHeader($sheet, "B{$r}:S{$r}");
        $r++;

        $sheet->freezePane("B{$r}");

        $dataStartRow = $r;
        foreach ($invoices as $idx => $inv) {
            $sheet->setCellValue("B{$r}", $idx + 1);
            $sheet->setCellValue("C{$r}", CsvSanitizer::sanitize((string) $inv->invoice_number));
            $sheet->setCellValue("D{$r}", $inv->invoice_date?->format('Y-m-d') ?? '-');
            $sheet->setCellValue("E{$r}", $inv->due_date?->format('Y-m-d') ?? '-');
            $sheet->setCellValue("F{$r}", CsvSanitizer::sanitize((string) ($inv->customer?->name ?? ($inv->recipient_name ?: '-'))));
            $sheet->setCellValue("G{$r}", CsvSanitizer::sanitize((string) ($inv->customer?->company_name ?? '-')));
            $sheet->setCellValue("H{$r}", CsvSanitizer::sanitize((string) ($inv->purchaseOrder?->po_number ?? '-')));
            $sheet->setCellValue("I{$r}", strtoupper((string) $inv->status));
            $sheet->setCellValue("J{$r}", (float) $inv->subtotal);
            $sheet->setCellValue("K{$r}", (float) $inv->discount_amount);
            $sheet->setCellValue("L{$r}", (float) $inv->tax_amount);
            $sheet->setCellValue("M{$r}", (float) $inv->shipping_cost);
            $sheet->setCellValue("N{$r}", (float) $inv->total_amount);
            $sheet->setCellValue("O{$r}", (float) $inv->paid_amount);
            $sheet->setCellValue("P{$r}", (float) $inv->balance_due);
            $sheet->setCellValue("Q{$r}", (float) $inv->total_hpp_cost);
            $sheet->setCellValue("R{$r}", (float) $inv->total_gross_profit);
            $sheet->setCellValue("S{$r}", CsvSanitizer::sanitize((string) ($inv->payment_terms ?? '-')));

            $sheet->getStyle("J{$r}:R{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet->getStyle("B{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$r}:I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("S{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($idx % 2 === 1) {
                $sheet->getStyle("B{$r}:S{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ZEBRA_BG);
            }
            $this->applyBorderThin($sheet, "B{$r}:S{$r}");
            $r++;
        }
        $dataEndRow = max($dataStartRow, $r - 1);

        // TOTAL ROW
        $sheet->setCellValue("B{$r}", '');
        $sheet->setCellValue("C{$r}", 'TOTAL KESELURUHAN');
        $sheet->setCellValue("D{$r}", count($invoices) . ' Faktur');
        $sheet->setCellValue("E{$r}", '');
        $sheet->setCellValue("F{$r}", '');
        $sheet->setCellValue("G{$r}", '');
        $sheet->setCellValue("H{$r}", '');
        $sheet->setCellValue("I{$r}", '');
        $sheet->setCellValue("J{$r}", "=SUM(J{$dataStartRow}:J{$dataEndRow})");
        $sheet->setCellValue("K{$r}", "=SUM(K{$dataStartRow}:K{$dataEndRow})");
        $sheet->setCellValue("L{$r}", "=SUM(L{$dataStartRow}:L{$dataEndRow})");
        $sheet->setCellValue("M{$r}", "=SUM(M{$dataStartRow}:M{$dataEndRow})");
        $sheet->setCellValue("N{$r}", "=SUM(N{$dataStartRow}:N{$dataEndRow})");
        $sheet->setCellValue("O{$r}", "=SUM(O{$dataStartRow}:O{$dataEndRow})");
        $sheet->setCellValue("P{$r}", "=SUM(P{$dataStartRow}:P{$dataEndRow})");
        $sheet->setCellValue("Q{$r}", "=SUM(Q{$dataStartRow}:Q{$dataEndRow})");
        $sheet->setCellValue("R{$r}", "=SUM(R{$dataStartRow}:R{$dataEndRow})");
        $sheet->setCellValue("S{$r}", '');

        $sheet->getStyle("B{$r}:S{$r}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB(self::COLOR_DARK_HEADER);
        $sheet->getStyle("B{$r}:S{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_SUBHEADER_BG);
        $sheet->getStyle("J{$r}:R{$r}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $this->applyDoubleBottomBorder($sheet, "B{$r}:S{$r}");

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(3);
    }

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
