<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class UserOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otpCode,
        public readonly string $recipientName,
        public readonly int $expiresInMinutes = 10,
        public readonly string $action = 'Verifikasi Keamanan Akun',
        public readonly ?string $phone = null,
        public readonly bool $isFallbackFromWaba = true
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[PENTING] {$this->otpCode} adalah Kode Verifikasi OTP Akun COOCA Anda",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_otp',
        );
    }
}
