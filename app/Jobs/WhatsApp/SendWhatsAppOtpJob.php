<?php

declare(strict_types=1);

namespace App\Jobs\WhatsApp;

use App\Domain\WhatsApp\AdminWhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job: SendWhatsAppOtpJob
 *
 * Mengirim kode OTP autentikasi resmi via Meta WhatsApp Cloud API secara asynchronous.
 * Didispatch ke antrean 'whatsapp' sehingga user tidak menunggu respons API Meta.
 *
 * Retry policy: 2 kali dengan backoff eksponensial (3s, 6s).
 * Timeout: 30 detik per attempt.
 */
class SendWhatsAppOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah maksimum percobaan pengiriman.
     */
    public int $tries = 2;

    /**
     * Waktu tunggu eksponensial antar percobaan (detik).
     *
     * @var array<int, int>
     */
    public array $backoff = [3, 6];

    /**
     * Batas waktu eksekusi per attempt (detik).
     */
    public int $timeout = 30;

    public function __construct(
        public readonly string $phone,
        public readonly string $otpCode,
        public readonly ?string $templateName = null,
        public readonly ?string $email = null,
        public readonly ?string $recipientName = null
    ) {
        $this->onQueue('whatsapp');
    }

    /**
     * Eksekusi pengiriman OTP via AdminWhatsAppService.
     */
    public function handle(AdminWhatsAppService $adminWa): void
    {
        $maskedPhone = substr($this->phone, 0, 4) . '****' . substr($this->phone, -3);

        Log::channel('daily')->info("[SendWhatsAppOtpJob] Mengirim OTP ke {$maskedPhone}...");

        $result = $adminWa->sendOtp($this->phone, $this->otpCode);

        if ($result['success'] ?? false) {
            $messageId = $result['message_id'] ?? 'unknown';
            Log::channel('daily')->info("[SendWhatsAppOtpJob] OTP berhasil dikirim ke {$maskedPhone}. message_id: {$messageId}");
        } else {
            $error = $result['error'] ?? 'Unknown error';
            Log::channel('daily')->error("[SendWhatsAppOtpJob] Gagal mengirim OTP ke {$maskedPhone}: {$error}");

            // Lempar exception agar Job bisa di-retry
            throw new \RuntimeException("WhatsApp OTP gagal dikirim ke {$maskedPhone}: {$error}");
        }
    }

    /**
     * Handle kegagalan setelah semua retry habis.
     * Secara otomatis memicu mitigasi pengiriman ke email pengguna/pelanggan.
     */
    public function failed(?\Throwable $exception): void
    {
        $maskedPhone = substr($this->phone, 0, 4) . '****' . substr($this->phone, -3);

        Log::channel('daily')->critical(
            "[SendWhatsAppOtpJob] FINAL FAILURE - OTP untuk {$maskedPhone} gagal dikirim setelah {$this->tries} percobaan.",
            ['error' => $exception?->getMessage()]
        );

        $targetEmail = $this->email;
        $name = $this->recipientName;

        if (empty($targetEmail)) {
            // Coba cari email dari User (owner/staff) atau GlobalCustomer
            $user = \App\Models\User::where('phone', $this->phone)->first();
            if ($user && ! empty($user->email)) {
                $targetEmail = $user->email;
                $name = $name ?: (string) ($user->name ?: 'Pengguna');
            } else {
                $customer = \App\Models\GlobalCustomer::where('phone', $this->phone)->first();
                if ($customer && ! empty($customer->email)) {
                    $targetEmail = $customer->email;
                    $name = $name ?: (string) ($customer->name ?: 'Pelanggan');
                }
            }
        }

        if (! empty($targetEmail)) {
            try {
                \Illuminate\Support\Facades\Mail::to($targetEmail)->send(new \App\Mail\UserOtpMail(
                    otpCode: $this->otpCode,
                    recipientName: $name ?: 'Pengguna Cooca',
                    expiresInMinutes: 10,
                    action: 'Autentikasi & Keamanan Akun',
                    phone: $this->phone,
                    isFallbackFromWaba: true
                ));

                Log::channel('daily')->info("[SendWhatsAppOtpJob] Fallback OTP darurat berhasil dikirim ke email: {$targetEmail}");
            } catch (\Throwable $e) {
                Log::channel('daily')->error("[SendWhatsAppOtpJob] Gagal mengirim fallback OTP ke email {$targetEmail}: " . $e->getMessage());
            }
        }
    }
}
