<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BusinessSuspendedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Business $business,
        public readonly string $reason,
        public readonly ?string $ownerName = null
    ) {}

    public function envelope(): Envelope
    {
        $businessName = $this->business->name ?: 'Bisnis';

        return new Envelope(
            subject: "[Penting] Pemberitahuan Penangguhan Akun Bisnis {$businessName} - Cooca",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.business_suspended',
        );
    }
}
