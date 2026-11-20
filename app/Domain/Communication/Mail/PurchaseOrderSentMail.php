<?php

declare(strict_types=1);

namespace App\Domain\Communication\Mail;

use App\Models\Business;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PurchaseOrderSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PurchaseOrder $purchaseOrder,
        public readonly Business $business,
        public readonly string $emailSubject,
        public readonly string $bodyHtml
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-document-mail',
            with: [
                'businessName' => $this->business->name,
                'documentNumber' => $this->purchaseOrder->po_number,
                'subjectTitle' => $this->emailSubject,
                'bodyHtml' => $this->bodyHtml,
            ]
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        try {
            $this->purchaseOrder->loadMissing(['supplier', 'items.product', 'items.material']);

            $pdf = Pdf::loadView('documents.purchase_order_pdf', [
                'business' => $this->business,
                'po' => $this->purchaseOrder,
                'supplier' => $this->purchaseOrder->supplier,
                'items' => $this->purchaseOrder->items,
            ])->setPaper('a4', 'portrait');

            $filename = 'PurchaseOrder-' . Str::slug($this->purchaseOrder->po_number) . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not attach PDF to PurchaseOrder email {$this->purchaseOrder->po_number}: " . $e->getMessage());
            return [];
        }
    }
}
