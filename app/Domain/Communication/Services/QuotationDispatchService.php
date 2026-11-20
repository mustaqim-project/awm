<?php

declare(strict_types=1);

namespace App\Domain\Communication\Services;

use App\Domain\Communication\Mail\QuotationSentMail;
use App\Domain\Communication\Models\BusinessSmtpAccount;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Quotation;

class QuotationDispatchService
{
    public function __construct(
        protected TenantMailService $mailService,
        protected EmailTemplateService $templateService
    ) {}

    /**
     * Dispatch Quotation via email with attached PDF.
     *
     * @return array{success: bool, recipient: string, used_tenant_smtp: bool, error?: string}
     */
    public function dispatch(Quotation $quotation, ?string $recipientEmail = null, ?string $customNotes = null): array
    {
        $business = $quotation->business ?? Business::findOrFail($quotation->business_id);
        $customer = $quotation->customer;

        $targetEmail = $recipientEmail ?: ($customer?->email ?? null);
        if (empty($targetEmail)) {
            return [
                'success' => false,
                'recipient' => '',
                'used_tenant_smtp' => false,
                'error' => 'Alamat email klien tidak ditemukan.',
            ];
        }

        $rendered = $this->templateService->renderQuotation($quotation, $business, $customNotes);

        $mailable = new QuotationSentMail(
            $quotation,
            $business,
            $rendered['subject'],
            $rendered['body_html']
        );

        $result = $this->mailService->send(
            $business,
            $targetEmail,
            $mailable,
            BusinessSmtpAccount::CHANNEL_QUOTATION
        );

        if ($result['success']) {
            if ($quotation->status === Quotation::STATUS_DRAFT) {
                $quotation->status = Quotation::STATUS_SENT;
                $quotation->saveQuietly();
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => Quotation::class,
                'auditable_id' => $quotation->id,
                'action' => 'quotation_email_sent',
                'notes' => "Surat Penawaran #{$quotation->quotation_number} berhasil dikirim ke email {$targetEmail}.",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);
        }

        return [
            'success' => $result['success'],
            'recipient' => $targetEmail,
            'used_tenant_smtp' => $result['used_tenant_smtp'],
            'error' => $result['error'] ?? null,
        ];
    }
}
