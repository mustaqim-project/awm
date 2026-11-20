<?php

declare(strict_types=1);

namespace App\Domain\Communication\Services;

use App\Domain\Communication\Models\BusinessEmailTemplate;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Quotation;

class EmailTemplateService
{
    /**
     * Get list of available template keys and their descriptions and allowed variables.
     *
     * @return array<string, array{title: string, description: string, variables: array<string, string>}>
     */
    public function getAvailableTemplates(): array
    {
        return [
            BusinessEmailTemplate::KEY_QUOTATION_SENT => [
                'title' => 'Pengiriman Surat Penawaran (Quotation)',
                'description' => 'Email yang dikirim ke klien saat mengirimkan surat penawaran harga resmi.',
                'variables' => [
                    '{{customer_name}}' => 'Nama Klien / Perusahaan',
                    '{{quotation_number}}' => 'Nomor Surat Penawaran (misal: QUO-2026-0001)',
                    '{{total_amount}}' => 'Total Nilai Penawaran (misal: Rp 15.000.000)',
                    '{{date}}' => 'Tanggal Penawaran',
                    '{{expiry_date}}' => 'Batas Waktu Berlaku Penawaran',
                    '{{business_name}}' => 'Nama Usaha Anda',
                    '{{business_phone}}' => 'Nomor Telepon Bisnis',
                    '{{business_email}}' => 'Email Bisnis',
                    '{{notes}}' => 'Catatan Tambahan',
                ],
            ],
            BusinessEmailTemplate::KEY_INVOICE_SENT => [
                'title' => 'Pengiriman Tagihan Faktur Penjualan Baru',
                'description' => 'Email tagihan pertama yang dikirimkan saat faktur penjualan diterbitkan.',
                'variables' => [
                    '{{customer_name}}' => 'Nama Klien / Perusahaan',
                    '{{invoice_number}}' => 'Nomor Faktur (misal: INV-2026-0001)',
                    '{{total_amount}}' => 'Total Tagihan',
                    '{{balance_due}}' => 'Sisa Tagihan yang Harus Dibayar',
                    '{{due_date}}' => 'Tanggal Jatuh Tempo',
                    '{{bank_accounts}}' => 'Rincian Rekening Bank untuk Pembayaran',
                    '{{business_name}}' => 'Nama Usaha Anda',
                    '{{view_link}}' => 'Tautan Melihat Faktur Online',
                ],
            ],
            BusinessEmailTemplate::KEY_INVOICE_REMINDER_UPCOMING => [
                'title' => 'Pengingat Tagihan (H-3 Sebelum Jatuh Tempo)',
                'description' => 'Email pengingat ramah yang dikirim 3 hari sebelum batas waktu pembayaran.',
                'variables' => [
                    '{{customer_name}}' => 'Nama Klien / Perusahaan',
                    '{{invoice_number}}' => 'Nomor Faktur',
                    '{{balance_due}}' => 'Sisa Saldo Tagihan',
                    '{{due_date}}' => 'Tanggal Jatuh Tempo',
                    '{{bank_accounts}}' => 'Rincian Rekening Bank',
                    '{{business_name}}' => 'Nama Usaha Anda',
                ],
            ],
            BusinessEmailTemplate::KEY_INVOICE_REMINDER_DUE => [
                'title' => 'Pengingat Tagihan (Hari H Jatuh Tempo)',
                'description' => 'Email pemberitahuan pada hari tanggal jatuh tempo pembayaran.',
                'variables' => [
                    '{{customer_name}}' => 'Nama Klien / Perusahaan',
                    '{{invoice_number}}' => 'Nomor Faktur',
                    '{{balance_due}}' => 'Sisa Saldo Tagihan',
                    '{{due_date}}' => 'Tanggal Jatuh Tempo',
                    '{{bank_accounts}}' => 'Rincian Rekening Bank',
                    '{{business_name}}' => 'Nama Usaha Anda',
                ],
            ],
            BusinessEmailTemplate::KEY_INVOICE_REMINDER_OVERDUE => [
                'title' => 'Pemberitahuan Tagihan Terlambat (Overdue)',
                'description' => 'Email penagihan untuk faktur yang telah melewati batas tanggal jatuh tempo.',
                'variables' => [
                    '{{customer_name}}' => 'Nama Klien / Perusahaan',
                    '{{invoice_number}}' => 'Nomor Faktur',
                    '{{balance_due}}' => 'Sisa Saldo Tertunggak',
                    '{{days_overdue}}' => 'Jumlah Hari Keterlambatan',
                    '{{bank_accounts}}' => 'Rincian Rekening Bank',
                    '{{business_name}}' => 'Nama Usaha Anda',
                ],
            ],
            BusinessEmailTemplate::KEY_PURCHASE_ORDER_SENT => [
                'title' => 'Pengiriman Purchase Order (PO) ke Pemasok',
                'description' => 'Email resmi pemesanan barang/material ke vendor atau supplier.',
                'variables' => [
                    '{{supplier_name}}' => 'Nama Supplier / Vendor',
                    '{{po_number}}' => 'Nomor Purchase Order (misal: PO-2026-0001)',
                    '{{total_amount}}' => 'Total Nilai Pemesanan',
                    '{{date}}' => 'Tanggal Pemesanan',
                    '{{expected_delivery_date}}' => 'Estimasi Tanggal Pengiriman',
                    '{{business_name}}' => 'Nama Usaha Pemesan',
                    '{{notes}}' => 'Catatan Pemesanan / Pengiriman',
                ],
            ],
        ];
    }

    /**
     * Get default subject and body for a template key.
     *
     * @return array{subject: string, body_html: string}
     */
    public function getDefaultTemplate(string $key): array
    {
        return match ($key) {
            BusinessEmailTemplate::KEY_QUOTATION_SENT => [
                'subject' => 'Surat Penawaran Harga #{{quotation_number}} - {{business_name}}',
                'body_html' => '<p>Yth. <strong>{{customer_name}}</strong>,</p>'
                    . '<p>Terima kasih atas minat dan kepercayaan Anda kepada {{business_name}}. Bersama email ini kami lampirkan dokumen resmi <strong>Surat Penawaran Harga #{{quotation_number}}</strong> dengan rincian total sebesar <strong>{{total_amount}}</strong>.</p>'
                    . '<p>Penawaran ini berlaku hingga tanggal <strong>{{expiry_date}}</strong>. Silakan tinjau rincian penawaran pada dokumen terlampir.</p>'
                    . '<p>Apabila terdapat hal yang ingin didiskusikan atau dinegosiasikan lebih lanjut, Anda dapat langsung membalas email ini atau menghubungi kami di {{business_phone}}.</p>'
                    . '<p>Hormat kami,<br><strong>{{business_name}}</strong></p>',
            ],
            BusinessEmailTemplate::KEY_INVOICE_SENT => [
                'subject' => 'Tagihan Faktur Penjualan #{{invoice_number}} - {{business_name}}',
                'body_html' => '<p>Yth. <strong>{{customer_name}}</strong>,</p>'
                    . '<p>Terima kasih atas kerja sama Anda dengan {{business_name}}. Bersama email ini, kami sampaikan rincian <strong>Faktur Penjualan #{{invoice_number}}</strong> dengan total tagihan sebesar <strong>{{total_amount}}</strong>.</p>'
                    . '<p>Batas waktu pembayaran (jatuh tempo) adalah tanggal <strong>{{due_date}}</strong>.</p>'
                    . '<div style="background-color: #f8fafc; border-left: 4px solid #007aff; padding: 12px 16px; margin: 16px 0;">'
                    . '<strong>Metode Pembayaran:</strong><br>{{bank_accounts}}'
                    . '</div>'
                    . '<p>Mohon kirimkan konfirmasi atau bukti transfer dengan membalas email ini setelah pembayaran berhasil dilakukan.</p>'
                    . '<p>Hormat kami,<br><strong>{{business_name}}</strong></p>',
            ],
            BusinessEmailTemplate::KEY_INVOICE_REMINDER_UPCOMING => [
                'subject' => '[Pengingat] Faktur #{{invoice_number}} Jatuh Tempo dalam 3 Hari - {{business_name}}',
                'body_html' => '<p>Halo <strong>{{customer_name}}</strong>,</p>'
                    . '<p>Sekadar mengingatkan dengan hormat bahwa <strong>Faktur #{{invoice_number}}</strong> sebesar <strong>{{balance_due}}</strong> akan jatuh tempo pada <strong>{{due_date}}</strong> (3 hari lagi).</p>'
                    . '<p>Detail pembayaran:<br>{{bank_accounts}}</p>'
                    . '<p>Abaikan email ini apabila Anda telah menyelesaikan pembayaran. Terima kasih atas kerja sama yang baik.</p>'
                    . '<p>Salam hangat,<br><strong>{{business_name}}</strong></p>',
            ],
            BusinessEmailTemplate::KEY_INVOICE_REMINDER_DUE => [
                'subject' => '[Jatuh Tempo Hari Ini] Tagihan Faktur #{{invoice_number}} - {{business_name}}',
                'body_html' => '<p>Yth. <strong>{{customer_name}}</strong>,</p>'
                    . '<p>Kami informasikan bahwa pembayaran untuk <strong>Faktur #{{invoice_number}}</strong> sebesar <strong>{{balance_due}}</strong> jatuh tempo pada <strong>hari ini ({{due_date}})</strong>.</p>'
                    . '<p>Silakan lakukan penyelesaian pembayaran ke rekening berikut:<br>{{bank_accounts}}</p>'
                    . '<p>Mohon konfirmasikan bukti transfer dengan membalas email ini. Terima kasih banyak.</p>'
                    . '<p>Hormat kami,<br><strong>{{business_name}}</strong></p>',
            ],
            BusinessEmailTemplate::KEY_INVOICE_REMINDER_OVERDUE => [
                'subject' => '[Pemberitahuan Jatuh Tempo] Tagihan Faktur #{{invoice_number}} Belum Diselesaikan - {{business_name}}',
                'body_html' => '<p>Yth. <strong>{{customer_name}}</strong>,</p>'
                    . '<p>Catatan keuangan kami menunjukkan bahwa <strong>Faktur #{{invoice_number}}</strong> sebesar <strong>{{balance_due}}</strong> telah melewati batas waktu jatuh tempo sebanyak <strong>{{days_overdue}} hari</strong>.</p>'
                    . '<p>Kami sangat menghargai kerja sama Anda untuk segera menyelesaikan pembayaran ke:<br>{{bank_accounts}}</p>'
                    . '<p>Jika ada kendala administrasi atau pertanyaan mengenai tagihan ini, silakan langsung membalas email ini.</p>'
                    . '<p>Hormat kami,<br><strong>{{business_name}}</strong></p>',
            ],
            BusinessEmailTemplate::KEY_PURCHASE_ORDER_SENT => [
                'subject' => 'Pesanan Pembelian (Purchase Order) #{{po_number}} - {{business_name}}',
                'body_html' => '<p>Yth. <strong>{{supplier_name}}</strong>,</p>'
                    . '<p>Bersama email ini, kami dari <strong>{{business_name}}</strong> menerbitkan dokumen resmi <strong>Purchase Order #{{po_number}}</strong> dengan total nilai <strong>{{total_amount}}</strong>.</p>'
                    . '<p>Rincian barang, kuantitas, spesifikasi, dan estimasi tanggal pengiriman tertera lengkap pada berkas PDF terlampir.</p>'
                    . '<p>Mohon konfirmasi kesiapan barang dan jadwal pengiriman dengan membalas email ini.</p>'
                    . '<p>Terima kasih atas kerja samanya.<br>Hormat kami,<br><strong>{{business_name}}</strong></p>',
            ],
            default => [
                'subject' => 'Pemberitahuan dari {{business_name}}',
                'body_html' => '<p>Halo,</p><p>Terlampir dokumen resmi dari {{business_name}}.</p><p>Terima kasih.</p>',
            ],
        };
    }

    /**
     * Render template for Quotation.
     *
     * @return array{subject: string, body_html: string}
     */
    public function renderQuotation(Quotation $quotation, Business $business, ?string $customNotes = null): array
    {
        $template = BusinessEmailTemplate::where('business_id', $business->id)
            ->where('template_key', BusinessEmailTemplate::KEY_QUOTATION_SENT)
            ->where('is_active', true)
            ->first();

        $default = $this->getDefaultTemplate(BusinessEmailTemplate::KEY_QUOTATION_SENT);
        $subject = $template?->subject ?: $default['subject'];
        $body = $template?->body_html ?: $default['body_html'];

        $variables = [
            '{{customer_name}}' => $quotation->customer?->name ?? 'Pelanggan',
            '{{quotation_number}}' => $quotation->quotation_number,
            '{{total_amount}}' => 'Rp ' . number_format((float) $quotation->total_amount, 0, ',', '.'),
            '{{date}}' => $quotation->date ? \Carbon\Carbon::parse($quotation->date)->translatedFormat('d F Y') : '-',
            '{{expiry_date}}' => $quotation->expiry_date ? \Carbon\Carbon::parse($quotation->expiry_date)->translatedFormat('d F Y') : '30 hari dari penerbitan',
            '{{business_name}}' => $business->name,
            '{{business_phone}}' => $business->phone ?? '-',
            '{{business_email}}' => $business->email ?? '-',
            '{{notes}}' => $customNotes ?: ($quotation->notes ?? ''),
        ];

        return [
            'subject' => str_replace(array_keys($variables), array_values($variables), $subject),
            'body_html' => str_replace(array_keys($variables), array_values($variables), $body),
        ];
    }

    /**
     * Render template for Invoice.
     *
     * @return array{subject: string, body_html: string}
     */
    public function renderInvoice(Invoice $invoice, Business $business, string $templateKey = BusinessEmailTemplate::KEY_INVOICE_SENT, ?string $customNotes = null): array
    {
        $template = BusinessEmailTemplate::where('business_id', $business->id)
            ->where('template_key', $templateKey)
            ->where('is_active', true)
            ->first();

        $default = $this->getDefaultTemplate($templateKey);
        $subject = $template?->subject ?: $default['subject'];
        $body = $template?->body_html ?: $default['body_html'];

        $dueDate = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date) : now();
        $daysOverdue = max(0, (int) round(now()->startOfDay()->diffInDays($dueDate->startOfDay(), false) * -1));

        $bankAccounts = $business->bank_accounts_formatted ?? 'BCA / Mandiri / BRI a.n ' . $business->name;

        $variables = [
            '{{customer_name}}' => $invoice->customer?->name ?? ($invoice->recipient_name ?? 'Pelanggan'),
            '{{invoice_number}}' => $invoice->invoice_number,
            '{{total_amount}}' => 'Rp ' . number_format((float) $invoice->total_amount, 0, ',', '.'),
            '{{balance_due}}' => 'Rp ' . number_format((float) $invoice->balance_due, 0, ',', '.'),
            '{{due_date}}' => $dueDate->translatedFormat('d F Y'),
            '{{days_overdue}}' => (string) $daysOverdue,
            '{{bank_accounts}}' => $bankAccounts,
            '{{business_name}}' => $business->name,
            '{{view_link}}' => route('invoices.show', $invoice->id),
            '{{notes}}' => $customNotes ?: ($invoice->notes ?? ''),
        ];

        return [
            'subject' => str_replace(array_keys($variables), array_values($variables), $subject),
            'body_html' => str_replace(array_keys($variables), array_values($variables), $body),
        ];
    }

    /**
     * Render template for Purchase Order.
     *
     * @return array{subject: string, body_html: string}
     */
    public function renderPurchaseOrder(PurchaseOrder $po, Business $business, ?string $customNotes = null): array
    {
        $template = BusinessEmailTemplate::where('business_id', $business->id)
            ->where('template_key', BusinessEmailTemplate::KEY_PURCHASE_ORDER_SENT)
            ->where('is_active', true)
            ->first();

        $default = $this->getDefaultTemplate(BusinessEmailTemplate::KEY_PURCHASE_ORDER_SENT);
        $subject = $template?->subject ?: $default['subject'];
        $body = $template?->body_html ?: $default['body_html'];

        $variables = [
            '{{supplier_name}}' => $po->supplier?->name ?? 'Pemasok / Vendor',
            '{{po_number}}' => $po->po_number,
            '{{total_amount}}' => 'Rp ' . number_format((float) $po->total_amount, 0, ',', '.'),
            '{{date}}' => $po->order_date ? \Carbon\Carbon::parse($po->order_date)->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            '{{expected_delivery_date}}' => $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->translatedFormat('d F Y') : 'Sesuai kesepakatan',
            '{{business_name}}' => $business->name,
            '{{notes}}' => $customNotes ?: ($po->notes ?? ''),
        ];

        return [
            'subject' => str_replace(array_keys($variables), array_values($variables), $subject),
            'body_html' => str_replace(array_keys($variables), array_values($variables), $body),
        ];
    }
}
