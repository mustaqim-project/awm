<?php

declare(strict_types=1);

namespace App\Domain\Communication\Mail;

use App\Models\Business;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class QuotationSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Quotation $quotation,
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
                'documentNumber' => $this->quotation->quotation_number,
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
            $this->quotation->loadMissing(['customer', 'items.product', 'salesOrder']);

            $pdf = Pdf::loadView('pdf.quotation_pdf', [
                'business' => $this->business,
                'quotation' => $this->quotation,
            ])->setPaper('a4', 'portrait');

            $filename = 'Penawaran-' . Str::slug($this->quotation->quotation_number) . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not attach PDF to Quotation email {$this->quotation->quotation_number}: " . $e->getMessage());
            return [];
        }
    }
}
