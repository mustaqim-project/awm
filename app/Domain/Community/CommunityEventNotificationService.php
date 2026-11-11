<?php

declare(strict_types=1);

namespace App\Domain\Community;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Mail\CommunityEventPaymentReceiptMail;
use App\Models\AuditLog;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CommunityEventNotificationService
{
    public function __construct(
        protected ?AdminWhatsAppService $adminWhatsAppService = null
    ) {
        $this->adminWhatsAppService = $adminWhatsAppService ?? app(AdminWhatsAppService::class);
    }

    /**
     * Send official payment receipt and digital e-ticket to the registered participant.
     * Dispatched via WhatsApp Platform and Email.
     *
     * @return array{success: bool, wa_sent: bool, email_sent: bool, message: string}
     */
    public function sendPaymentReceiptAndTicket(CommunityEventRegistration $registration): array
    {
        $registration->loadMissing(['event', 'user', 'business']);

        $event = $registration->event;
        $user = $registration->user;

        if (! $event || ! $user) {
            return [
                'success' => false,
                'wa_sent' => false,
                'email_sent' => false,
                'message' => 'Data event atau peserta tidak ditemukan.',
            ];
        }

        $waSent = false;
        $emailSent = false;
        $errors = [];

        // 1. Dispatch via WhatsApp
        $phone = $this->sanitizePhoneNumber($user->phone ?? '');
        if (! empty($phone)) {
            $waMessage = $this->buildReceiptWhatsAppMessage($registration);
            try {
                $waRes = $this->adminWhatsAppService->sendMessage($phone, $waMessage);
                $waSent = (bool) ($waRes['success'] ?? false);
                if (! $waSent) {
                    $errors[] = 'WA: ' . ($waRes['error'] ?? 'Gagal terkirim');
                }
            } catch (\Throwable $e) {
                Log::warning("[EventNotification] WA dispatch failed for registration #{$registration->id}: " . $e->getMessage());
                $errors[] = 'WA Exception: ' . $e->getMessage();
            }
        } else {
            $errors[] = 'Nomor WhatsApp peserta tidak tersedia.';
        }

        // 2. Dispatch via Email
        if (! empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($user->email)->send(new CommunityEventPaymentReceiptMail($registration, 'receipt'));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::warning("[EventNotification] Email dispatch failed for registration #{$registration->id}: " . $e->getMessage());
                $errors[] = 'Email Exception: ' . $e->getMessage();
            }
        }

        // 3. Record Audit Log
        try {
            AuditLog::create([
                'business_id' => $registration->business_id,
                'user_id' => $user->getAuthIdentifier(),
                'action' => 'community.event.receipt_dispatched',
                'auditable_type' => CommunityEventRegistration::class,
                'auditable_id' => $registration->id,
                'risk_level' => AuditLog::RISK_LOW,
                'notes' => "Bukti bayar & tiket dikirim (WA: " . ($waSent ? 'OK' : 'FAIL') . ", Email: " . ($emailSent ? 'OK' : 'FAIL') . ") untuk event: {$event->title}",
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System / CLI',
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ignore audit log failure
        }

        $isSuccess = $waSent || $emailSent;
        $statusMsg = $isSuccess
            ? "Bukti bayar dan tiket berhasil dikirim ke {$user->name} (WA: " . ($waSent ? 'Terkirim' : 'Gagal') . ", Email: " . ($emailSent ? 'Terkirim' : 'Gagal') . ")."
            : "Gagal mengirim bukti bayar: " . implode('; ', $errors);

        return [
            'success' => $isSuccess,
            'wa_sent' => $waSent,
            'email_sent' => $emailSent,
            'message' => $statusMsg,
        ];
    }

    /**
     * Send event schedule reminder to the participant.
     *
     * @return array{success: bool, wa_sent: bool, email_sent: bool, message: string}
     */
    public function sendEventReminder(CommunityEventRegistration $registration): array
    {
        $registration->loadMissing(['event', 'user', 'business']);

        $event = $registration->event;
        $user = $registration->user;

        if (! $event || ! $user) {
            return [
                'success' => false,
                'wa_sent' => false,
                'email_sent' => false,
                'message' => 'Data event atau peserta tidak ditemukan.',
            ];
        }

        $waSent = false;
        $emailSent = false;
        $errors = [];

        // 1. Dispatch via WhatsApp
        $phone = $this->sanitizePhoneNumber($user->phone ?? '');
        if (! empty($phone)) {
            $waMessage = $this->buildReminderWhatsAppMessage($registration);
            try {
                $waRes = $this->adminWhatsAppService->sendMessage($phone, $waMessage);
                $waSent = (bool) ($waRes['success'] ?? false);
                if (! $waSent) {
                    $errors[] = 'WA: ' . ($waRes['error'] ?? 'Gagal terkirim');
                }
            } catch (\Throwable $e) {
                Log::warning("[EventNotification] WA reminder failed for registration #{$registration->id}: " . $e->getMessage());
                $errors[] = 'WA Exception: ' . $e->getMessage();
            }
        }

        // 2. Dispatch via Email
        if (! empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($user->email)->send(new CommunityEventPaymentReceiptMail($registration, 'reminder'));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::warning("[EventNotification] Email reminder failed for registration #{$registration->id}: " . $e->getMessage());
                $errors[] = 'Email Exception: ' . $e->getMessage();
            }
        }

        return [
            'success' => $waSent || $emailSent,
            'wa_sent' => $waSent,
            'email_sent' => $emailSent,
            'message' => ($waSent || $emailSent)
                ? "Pengingat event berhasil dikirim ke {$user->name}."
                : "Gagal mengirim pengingat event: " . implode('; ', $errors),
        ];
    }

    /**
     * Broadcast event reminder to all paid/active participants of an event.
     *
     * @return array{total: int, sent_count: int, failed_count: int}
     */
    public function broadcastReminderToPaidParticipants(CommunityEvent $event): array
    {
        $registrations = $event->registrations()
            ->with(['user', 'event'])
            ->where('status', '!=', CommunityEventRegistration::STATUS_CANCELLED)
            ->where('payment_status', CommunityEventRegistration::PAYMENT_STATUS_PAID)
            ->get();

        $sentCount = 0;
        $failedCount = 0;

        foreach ($registrations as $reg) {
            $res = $this->sendEventReminder($reg);
            if ($res['success']) {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'total' => $registrations->count(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ];
    }

    /**
     * Build WhatsApp text message for Payment Receipt & E-Ticket.
     */
    protected function buildReceiptWhatsAppMessage(CommunityEventRegistration $registration): string
    {
        $event = $registration->event;
        $user = $registration->user;
        $ticketCode = $registration->ticket_code;
        $ticketUrl = route('community.events.ticket', $registration->community_event_id);
        $amountFormatted = $registration->getFormattedAmountPaid();
        $startTime = $event?->start_time instanceof Carbon
            ? $event->start_time->translatedFormat('l, d F Y, H:i') . ' WIB'
            : (string) ($event?->start_time ?? '-');
        $location = $event?->location_name ?: ($event?->is_online ? 'Sesi Online (Tautan via Tiket)' : 'Lokasi Resmi Event');

        return "🎉 *BUKTI PEMBAYARAN & TIKET EVENT RESMI COOCA*\n"
            . "──────────────────────────────\n"
            . "Halo *{$user?->name}*,\n"
            . "Pembayaran tiket event Anda telah berhasil kami terima. Berikut adalah rincian tiket digital Anda:\n\n"
            . "🎫 *Kode Tiket:* `{$ticketCode}`\n"
            . "📌 *Event:* {$event?->title}\n"
            . "📅 *Waktu:* {$startTime}\n"
            . "📍 *Lokasi:* {$location}\n"
            . "💰 *Total Biaya:* {$amountFormatted} *(LUNAS)*\n"
            . "──────────────────────────────\n\n"
            . "📱 *Akses E-Tiket Digital & QR Code Check-in:*\n"
            . "{$ticketUrl}\n\n"
            . "_Harap simpan pesan ini dan tunjukkan QR Code pada tautan di atas saat registrasi ulang / check-in di lokasi acara._\n\n"
            . "Salam hangat,\n"
            . "*Tim Komunitas & Acara COOCA*\n"
            . "https://cooca.id";
    }

    /**
     * Build WhatsApp text message for Event Schedule Reminder.
     */
    protected function buildReminderWhatsAppMessage(CommunityEventRegistration $registration): string
    {
        $event = $registration->event;
        $user = $registration->user;
        $ticketCode = $registration->ticket_code;
        $ticketUrl = route('community.events.ticket', $registration->community_event_id);
        $startTime = $event?->start_time instanceof Carbon
            ? $event->start_time->translatedFormat('l, d F Y, H:i') . ' WIB'
            : (string) ($event?->start_time ?? '-');
        $location = $event?->location_name ?: ($event?->is_online ? 'Sesi Online' : 'Lokasi Acara');

        $onlineInfo = '';
        if ($event?->is_online && $event?->online_url) {
            $onlineInfo = "🔗 *Tautan Online Meeting:* {$event->online_url}\n";
        }

        return "⏰ *PENGINGAT JADWAL EVENT COOCA*\n"
            . "──────────────────────────────\n"
            . "Halo *{$user?->name}*,\n"
            . "Jangan lupa, event yang Anda ikuti akan segera dimulai!\n\n"
            . "📌 *Event:* {$event?->title}\n"
            . "📅 *Waktu:* {$startTime}\n"
            . "📍 *Lokasi:* {$location}\n"
            . $onlineInfo
            . "🎫 *Kode Tiket Anda:* `{$ticketCode}`\n"
            . "──────────────────────────────\n\n"
            . "📱 *Buka E-Tiket & Barcode Check-in:*\n"
            . "{$ticketUrl}\n\n"
            . "Pastikan Anda hadir tepat waktu untuk mendapatkan pengalaman belajar dan networking terbaik.\n\n"
            . "Sampai jumpa di acara!\n"
            . "*Tim Komunitas & Acara COOCA*";
    }

    /**
     * Normalize Indonesian phone number to E.164 format without leading plus (e.g. 628123456789).
     */
    protected function sanitizePhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }

        return $cleaned;
    }
}

