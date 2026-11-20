<?php

declare(strict_types=1);

namespace App\Domain\Communication\Mail;

use App\Models\Business;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CustomerInvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
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
                'documentNumber' => $this->invoice->invoice_number,
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
            $this->invoice->loadMissing(['customer', 'items.product', 'payments']);

            $pdf = Pdf::loadView('pdf.invoice_pdf', [
                'business' => $this->business,
                'invoice' => $this->invoice,
            ])->setPaper('a4', 'portrait');

            $filename = 'Faktur-' . Str::slug($this->invoice->invoice_number) . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not attach PDF to Invoice email {$this->invoice->invoice_number}: " . $e->getMessage());
            return [];
        }
    }
}
