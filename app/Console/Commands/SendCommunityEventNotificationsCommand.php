<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Community\CommunityEventNotificationService;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use Illuminate\Console\Command;

class SendCommunityEventNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'community:notify-event
                            {--registration= : UUID atau Kode Tiket pendaftaran event tertentu}
                            {--event= : UUID ID Event untuk broadcast pengingat ke seluruh peserta yang sudah bayar}
                            {--type=all : Jenis notifikasi: receipt (bukti bayar), reminder (pengingat), all (keduanya)}
                            {--force-paid : Tandai pendaftaran sebagai PAID jika saat ini masih pending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirimkan bukti pembayaran resmi, e-tiket, dan/atau pengingat jadwal event ke peserta komunitas COOCA';

    /**
     * Execute the console command.
     */
    public function handle(CommunityEventNotificationService $notificationService): int
    {
        $registrationInput = $this->option('registration');
        $eventId = $this->option('event');
        $type = strtolower((string) ($this->option('type') ?? 'all'));
        $forcePaid = (bool) $this->option('force-paid');

        if (empty($registrationInput) && empty($eventId)) {
            $this->error('Harap tentukan opsi --registration=[ID_ATAU_KODE_TIKET] atau --event=[ID_EVENT].');
            return Command::FAILURE;
        }

        // Mode 1: Spesifik Registrasi Peserta Tertentu
        if (! empty($registrationInput)) {
            $registration = CommunityEventRegistration::with(['event', 'user', 'business'])
                ->where('id', $registrationInput)
                ->orWhere('ticket_code', $registrationInput)
                ->first();

            if (! $registration) {
                $this->error("Pendaftaran dengan ID atau Kode Tiket '{$registrationInput}' tidak ditemukan di database.");
                return Command::FAILURE;
            }

            $this->info("Menemukan pendaftaran: {$registration->id} ({$registration->ticket_code})");
            $this->line("Peserta : " . ($registration->user?->name ?? 'N/A') . " (" . ($registration->user?->phone ?? 'No Phone') . " / " . ($registration->user?->email ?? 'No Email') . ")");
            $this->line("Event   : " . ($registration->event?->title ?? 'N/A'));
            $this->line("Status  : {$registration->payment_status} (Status Tiket: {$registration->status})");

            if ($forcePaid && ! $registration->isPaid()) {
                $registration->markAsPaid();
                $this->info("Status pembayaran berhasil diperbarui menjadi: PAID");
            }

            // Kirim Bukti Bayar & Tiket
            if (in_array($type, ['receipt', 'all'], true)) {
                $this->comment("Mengirimkan bukti bayar dan tiket via WhatsApp & Email...");
                $res = $notificationService->sendPaymentReceiptAndTicket($registration);
                if ($res['success']) {
                    $this->info("✔ Bukti bayar & tiket berhasil dikirim!");
                    $this->line("  Detail: " . $res['message']);
                } else {
                    $this->warn("⚠ Gagal mengirim bukti bayar: " . $res['message']);
                }
            }

            // Kirim Pengingat Event
            if (in_array($type, ['reminder', 'all'], true)) {
                $this->comment("Mengirimkan pengingat jadwal event...");
                $res = $notificationService->sendEventReminder($registration);
                if ($res['success']) {
                    $this->info("✔ Pengingat event berhasil dikirim!");
                    $this->line("  Detail: " . $res['message']);
                } else {
                    $this->warn("⚠ Gagal mengirim pengingat event: " . $res['message']);
                }
            }

            return Command::SUCCESS;
        }

        // Mode 2: Broadcast Pengingat ke Semua Peserta Event yang Sudah Bayar
        $event = CommunityEvent::find($eventId);
        if (! $event) {
            $this->error("Event dengan ID '{$eventId}' tidak ditemukan.");
            return Command::FAILURE;
        }

        $this->info("Memulai broadcast pengingat untuk event: '{$event->title}'");
        $res = $notificationService->broadcastReminderToPaidParticipants($event);

        $this->info("Selesai memproses broadcast pengingat:");
        $this->line("- Total Peserta Lunas : {$res['total']}");
        $this->line("- Berhasil Dikirim    : {$res['sent_count']}");
        $this->line("- Gagal Dikirim       : {$res['failed_count']}");

        return Command::SUCCESS;
    }
}

