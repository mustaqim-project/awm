<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\CommunityEventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityEventPaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly CommunityEventRegistration $registration,
        public readonly string $notificationType = 'receipt' // 'receipt' or 'reminder'
    ) {}

    public function envelope(): Envelope
    {
        $eventTitle = $this->registration->event?->title ?? 'Event Komunitas Cooca';
        $ticketCode = $this->registration->ticket_code;

        $subject = $this->notificationType === 'reminder'
            ? "[Pengingat Jadwal Event] {$eventTitle} - Tiket {$ticketCode}"
            : "[Bukti Bayar & Tiket Resmi] Pembayaran Berhasil: {$eventTitle} ({$ticketCode})";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.community_event_receipt',
            with: [
                'registration' => $this->registration,
                'event' => $this->registration->event,
                'user' => $this->registration->user,
                'notificationType' => $this->notificationType,
                'ticketUrl' => route('community.events.ticket', $this->registration->community_event_id),
            ],
        );
    }
}

