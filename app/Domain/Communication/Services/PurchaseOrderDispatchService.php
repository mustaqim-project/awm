<?php

declare(strict_types=1);

namespace App\Domain\Communication\Services;

use App\Domain\Communication\Mail\PurchaseOrderSentMail;
use App\Domain\Communication\Models\BusinessSmtpAccount;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\PurchaseOrder;

class PurchaseOrderDispatchService
{
    public function __construct(
        protected TenantMailService $mailService,
        protected EmailTemplateService $templateService
    ) {}

    /**
     * Dispatch Purchase Order via email with attached PDF.
     *
     * @return array{success: bool, recipient: string, used_tenant_smtp: bool, error?: string}
     */
    public function dispatch(PurchaseOrder $po, ?string $recipientEmail = null, ?string $customNotes = null): array
    {
        $business = $po->business ?? Business::findOrFail($po->business_id);
        $supplier = $po->supplier;

        $targetEmail = $recipientEmail ?: ($supplier?->email ?? null);
        if (empty($targetEmail)) {
            return [
                'success' => false,
                'recipient' => '',
                'used_tenant_smtp' => false,
                'error' => 'Alamat email supplier / vendor tidak ditemukan.',
            ];
        }

        $rendered = $this->templateService->renderPurchaseOrder($po, $business, $customNotes);

        $mailable = new PurchaseOrderSentMail(
            $po,
            $business,
            $rendered['subject'],
            $rendered['body_html']
        );

        $result = $this->mailService->send(
            $business,
            $targetEmail,
            $mailable,
            BusinessSmtpAccount::CHANNEL_PURCHASE_ORDER
        );

        if ($result['success']) {
            if ($po->status === PurchaseOrder::STATUS_DRAFT) {
                $po->status = PurchaseOrder::STATUS_CONFIRMED;
                $po->saveQuietly();
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => PurchaseOrder::class,
                'auditable_id' => $po->id,
                'action' => 'purchase_order_email_sent',
                'notes' => "Purchase Order #{$po->po_number} berhasil dikirim ke email supplier {$targetEmail}.",
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
