<?php

declare(strict_types=1);

namespace App\Domain\Operations\Services;

use App\Domain\Operations\Mail\EventReminderMail;
use App\Domain\Operations\Models\EventReminder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventReminderService
{
    /**
     * Dispatch due reminders.
     */
    public function dispatchDueReminders(): int
    {
        $reminders = EventReminder::with(['event', 'recipientUser'])
            ->due()
            ->limit(50)
            ->get();

        $dispatched = 0;

        foreach ($reminders as $reminder) {
            $event = $reminder->event;
            if (! $event || $event->status === 'cancelled') {
                $reminder->status = 'failed';
                $reminder->error_message = 'Kegiatan dibatalkan atau tidak ditemukan.';
                $reminder->save();
                continue;
            }

            try {
                $success = false;

                if ($reminder->channel === 'email') {
                    $email = $reminder->recipient_email ?? $reminder->recipientUser?->email;
                    if ($email) {
                        Mail::to($email)->send(new EventReminderMail($event));
                        $success = true;
                    } else {
                        $reminder->error_message = 'Email penerima tidak tersedia.';
                    }
                } elseif ($reminder->channel === 'whatsapp') {
                    $phone = $reminder->recipient_phone ?? $reminder->recipientUser?->phone;
                    if ($phone) {
                        $success = $this->sendWhatsAppReminder($phone, $event);
                    } else {
                        $reminder->error_message = 'Nomor WhatsApp penerima tidak tersedia.';
                    }
                } else {
                    // in_app
                    $success = true; // In-app is served via real-time query / dashboard alert
                }

                if ($success) {
                    $reminder->status = 'sent';
                    $reminder->sent_at = now();
                    $reminder->error_message = null;
                    $dispatched++;
                } else {
                    $reminder->status = 'failed';
                }

                $reminder->save();
            } catch (\Throwable $e) {
                Log::error('EventReminder dispatch error', [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage(),
                ]);
                $reminder->status = 'failed';
                $reminder->error_message = $e->getMessage();
                $reminder->save();
            }
        }

        return $dispatched;
    }

    /**
     * Send WhatsApp reminder via Meta WhatsApp Cloud API or worker.
     */
    private function sendWhatsAppReminder(string $phone, $event): bool
    {
        $token = (string) config('services.meta_whatsapp.token', '');
        $phoneId = (string) config('services.meta_whatsapp.phone_number_id', '');

        if (empty($token) || empty($phoneId)) {
            Log::info('WhatsApp reminder skipped: Meta WhatsApp credentials not configured.', [
                'phone' => $phone,
                'event_title' => $event->title,
            ]);
            return true; // Mark as processed in dev/testing environments
        }

        $formattedPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($formattedPhone, '0')) {
            $formattedPhone = '62' . substr($formattedPhone, 1);
        }

        $message = "📅 *PENGINGAT KEGIATAN COOCA*\n\n"
            . "*{$event->title}*\n"
            . "🕒 Waktu: " . $event->start_time->format('d/m/Y H:i') . " WIB\n"
            . ($event->location ? "📍 Lokasi: {$event->location}\n" : '')
            . ($event->meeting_link ? "🔗 Link: {$event->meeting_link}\n" : '')
            . "\nHarap hadir tepat waktu. Terima kasih!";

        $url = "https://graph.facebook.com/v21.0/{$phoneId}/messages";

        $response = Http::withToken($token)->post($url, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $formattedPhone,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ]);

        return $response->successful();
    }
}
