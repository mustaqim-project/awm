<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Domain\WhatsApp\WhatsAppTrustedDeviceService;
use App\Http\Controllers\Controller;
use App\Mail\UserOtpMail;
use App\Models\AccountRecoveryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class AuthOtpController extends Controller
{
    public function show(Request $request, AdminWhatsAppService $adminWa, WhatsAppTrustedDeviceService $trustedDevice): View|RedirectResponse
    {
        if (! auth('web')->check()) {
            return redirect()->route('login');
        }

        $user = auth('web')->user();

        // Bypass for Shopee / Meta Reviewer or Testing accounts
        if (in_array($user->email, ['reviewer@cooca.id', 'shopee.reviewer@cooca.id', 'testing@cooca.id', 'demo@cooca.id'], true)) {
            if (! $user->isPhoneVerified()) {
                $user->update([
                    'phone'             => $user->phone ?: '628123456789',
                    'phone_verified_at' => now(),
                ]);
            }
            $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
            $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

            return redirect()->route('dashboard');
        }

        // 1. Karyawan / user tambahan pada bisnis tidak perlu OTP
        if (! $user->isBusinessOwner()) {
            return redirect()->route('dashboard');
        }

        $userPhone = $this->normalizePhone((string) ($user->phone ?: $user->activeBusiness?->phone));

        // 2. Jika nomor HP owner sudah terverifikasi seumur hidup, langsung ke dashboard
        if ($user->isPhoneVerified() && $userPhone) {
            $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
            $request->session()->put('auth_wa_otp_verified_at', $user->phone_verified_at->timestamp);

            return redirect()->route('dashboard');
        }

        // Jika user sudah terverifikasi OTP di sesi ini, langsung alihkan ke dashboard
        if ($request->session()->get('auth_wa_otp_verified_user_id') === $user->id) {
            return redirect()->route('dashboard');
        }

        // Jika browser/perangkat ini adalah Trusted Device yang masih valid, tandai sesi dan langsung alihkan ke dashboard
        if ($userPhone && $trustedDevice->isTrusted($request, $user, $userPhone)) {
            if (! $user->isPhoneVerified()) {
                $user->update(['phone_verified_at' => now()]);
            }
            $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
            $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

            return redirect()->route('dashboard');
        }
        $challenge = $request->session()->get('auth_wa_otp_challenge');

        // Jika nomor HP pada sesi challenge berbeda dengan nomor aktif user di database (misal pemulihan akun disetujui),
        // atau challenge belum ada / sudah expired, perbarui sesi dan kirimkan OTP ke nomor baru.
        if (
            ! is_array($challenge)
            || empty($challenge)
            || ($challenge['user_id'] ?? null) !== $user->id
            || ($userPhone && ($challenge['phone'] ?? null) !== $userPhone)
            || (int) ($challenge['expires_at'] ?? 0) < now()->timestamp
        ) {
            if ($userPhone) {
                $otp = (string) random_int(100000, 999999);
                $result = $adminWa->sendOtp($userPhone, $otp);
                $sent = (bool) ($result['success'] ?? false);
                $sentViaEmail = false;

                // Fallback otomatis: jika pengiriman WABA gagal dan user punya email, kirimkan ke email pengguna
                if (! $sent && ! empty($user->email)) {
                    try {
                        Mail::to($user->email)->send(new UserOtpMail(
                            otpCode: $otp,
                            recipientName: (string) ($user->name ?: 'Pemilik Bisnis'),
                            expiresInMinutes: 10,
                            action: 'Verifikasi Masuk Akun',
                            phone: $userPhone,
                            isFallbackFromWaba: true
                        ));
                        $sentViaEmail = true;
                        Log::info("[OTP-Fallback] WABA gagal, OTP berhasil dikirimkan ke email {$user->email} untuk user {$user->id}");
                    } catch (\Throwable $e) {
                        Log::error("[OTP-Fallback] Gagal mengirim OTP ke email {$user->email}: " . $e->getMessage());
                    }
                }

                // In local dev, allow testing OTP even if WhatsApp server is offline
                if (! $sent && ! $sentViaEmail && app()->isLocal()) {
                    Log::info("[DEV-OTP] WhatsApp server offline. Local OTP for {$userPhone} ({$user->email}): {$otp} (or master testing OTP: 123456)");
                }

                $channel = $sent ? 'whatsapp' : ($sentViaEmail ? 'email' : 'failed');
                $isOtpValid = $sent || $sentViaEmail || app()->isLocal();

                $challenge = [
                    'user_id' => $user->id,
                    'phone' => $userPhone,
                    'email' => $user->email,
                    'channel' => $channel,
                    'otp_hash' => $isOtpValid ? Hash::make($otp) : null,
                    'expires_at' => now()->addMinutes(10)->timestamp,
                    'attempts' => 0,
                    'last_sent_at' => now()->timestamp,
                    'delivery_error' => $isOtpValid ? null : 'OTP gagal dikirim ke WhatsApp dan Email. Silakan klik kirim ulang.',
                ];
                $request->session()->put('auth_wa_otp_challenge', $challenge);
            } else {
                $challenge = is_array($challenge) ? $challenge : [];
            }
        }

        $deliveryError = $challenge['delivery_error'] ?? null;
        if ($deliveryError) {
            $deliveryError = 'OTP gagal dikirim. Coba lagi.';
        }

        $activeRecovery = AccountRecoveryRequest::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        $channel = (string) ($challenge['channel'] ?? 'whatsapp');
        $maskedEmail = ! empty($user->email) ? $this->maskEmail((string) $user->email) : '';

        return view('auth.otp', [
            'phone' => $this->maskPhone((string) ($challenge['phone'] ?? $userPhone ?? '')),
            'channel' => $channel,
            'email' => $user->email,
            'maskedEmail' => $maskedEmail,
            'expiresAt' => (int) ($challenge['expires_at'] ?? 0),
            'resendIn' => max(0, ((int) ($challenge['last_sent_at'] ?? 0)) + 60 - now()->timestamp),
            'deliveryError' => $deliveryError,
            'activeRecovery' => $activeRecovery,
        ]);
    }

    public function verify(Request $request, WhatsAppTrustedDeviceService $trustedDevice): RedirectResponse
    {
        $validated = $request->validate(['otp' => ['required', 'digits:6']]);
        $challenge = $request->session()->get('auth_wa_otp_challenge');
        $user = auth('web')->user();

        if (! is_array($challenge) || ($challenge['user_id'] ?? null) !== $user->id) {
            return redirect()->route('auth.otp')->withErrors(['otp' => 'Sesi OTP tidak ditemukan. Minta OTP baru.']);
        }
        if ((int) ($challenge['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('auth_wa_otp_challenge');
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa.']);
        }

        // Defense-in-depth: batas percobaan global per akun (tidak dapat di-reset via session baru).
        $cacheKey = 'otp_attempts:auth:' . $user->id;
        $cachedAttempts = ((int) Cache::get($cacheKey, 0)) + 1;
        if ($cachedAttempts > 5) {
            Cache::forget($cacheKey);
            $request->session()->forget('auth_wa_otp_challenge');

            return back()->withErrors(['otp' => 'Terlalu banyak percobaan OTP. Minta OTP baru.']);
        }

        $attempts = (int) ($challenge['attempts'] ?? 0) + 1;
        if ($attempts > 5) {
            $request->session()->forget('auth_wa_otp_challenge');
            return back()->withErrors(['otp' => 'Batas percobaan OTP terlampaui. Minta OTP baru.']);
        }
        $isMasterBypassOtp = (app()->environment('local', 'testing') && in_array($validated['otp'], ['123456', '000000', '999999'], true))
            || in_array($user->email, ['reviewer@cooca.id', 'shopee.reviewer@cooca.id', 'testing@cooca.id', 'demo@cooca.id'], true)
            || app()->isLocal();

        if (! $isMasterBypassOtp && (empty($challenge['otp_hash']) || ! Hash::check($validated['otp'], $challenge['otp_hash']))) {
            Cache::put($cacheKey, $cachedAttempts, now()->addMinutes(10));
            $challenge['attempts'] = $attempts;
            $request->session()->put('auth_wa_otp_challenge', $challenge);
            return back()->withErrors(['otp' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $attempts) . '.']);
        }

        Cache::forget($cacheKey);
        $request->session()->forget('auth_wa_otp_challenge');
        $request->session()->regenerate();
        $request->session()->put('auth_wa_otp_verified_user_id', $user->id);
        $request->session()->put('auth_wa_otp_verified_at', now()->timestamp);

        // Verifikasi seumur hidup di database hingga ganti nomor HP
        $user->update(['phone_verified_at' => now()]);

        $targetPhone = $this->normalizePhone((string) ($user->phone ?: $user->activeBusiness?->phone)) ?: (string) ($challenge['phone'] ?? '');
        if ($targetPhone !== '') {
            $trustedDevice->trustDevice($user, $targetPhone);
        }

        $intended = (string) $request->session()->get('url.intended', '');
        if ($intended !== '' && (str_contains($intended, '/auth/otp') || str_contains($intended, '/login'))) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Verifikasi keamanan berhasil.');
    }

    public function resend(Request $request, AdminWhatsAppService $adminWa): RedirectResponse
    {
        $challenge = $request->session()->get('auth_wa_otp_challenge');
        $user = auth('web')->user();

        if (! $user->isBusinessOwner() || $user->isPhoneVerified()) {
            return redirect()->route('dashboard');
        }

        if (! is_array($challenge) || ($challenge['user_id'] ?? null) !== $user->id) {
            return redirect()->route('auth.otp')->withErrors(['otp' => 'Sesi OTP tidak ditemukan.']);
        }
        if ((int) ($challenge['last_sent_at'] ?? 0) > now()->subSeconds(60)->timestamp) {
            return back()->withErrors(['otp' => 'Tunggu 60 detik sebelum meminta OTP baru.']);
        }

        $userPhone = $this->normalizePhone((string) ($user->phone ?: $user->activeBusiness?->phone));
        $targetPhone = $userPhone ?: (string) ($challenge['phone'] ?? '');

        $otp = (string) random_int(100000, 999999);
        $result = $adminWa->sendOtp($targetPhone, $otp);
        $sent = (bool) ($result['success'] ?? false);
        $sentViaEmail = false;

        // Fallback otomatis: jika pengiriman WABA gagal dan user punya email, kirimkan ke email pengguna
        if (! $sent && ! empty($user->email)) {
            try {
                Mail::to($user->email)->send(new UserOtpMail(
                    otpCode: $otp,
                    recipientName: (string) ($user->name ?: 'Pemilik Bisnis'),
                    expiresInMinutes: 10,
                    action: 'Verifikasi Masuk Akun',
                    phone: $targetPhone,
                    isFallbackFromWaba: true
                ));
                $sentViaEmail = true;
                Log::info("[OTP-Fallback] WABA gagal saat resend, OTP berhasil dialihkan ke email {$user->email} untuk user {$user->id}");
            } catch (\Throwable $e) {
                Log::error("[OTP-Fallback] Gagal mengirim OTP resend ke email {$user->email}: " . $e->getMessage());
            }
        }

        if (! $sent && ! $sentViaEmail && ! app()->isLocal()) {
            $challenge['phone'] = $targetPhone;
            $challenge['delivery_error'] = 'OTP gagal dikirim ke WhatsApp dan Email. Coba lagi.';
            $request->session()->put('auth_wa_otp_challenge', $challenge);

            return back()->withErrors(['otp' => 'OTP gagal dikirim ke WhatsApp dan Email. Coba lagi.']);
        }

        if (! $sent && ! $sentViaEmail && app()->isLocal()) {
            Log::info("[DEV-OTP] Resend WhatsApp server offline. Local OTP for {$targetPhone} ({$user->email}): {$otp} (or master testing OTP: 123456)");
        }

        $channel = $sent ? 'whatsapp' : ($sentViaEmail ? 'email' : 'failed');
        $challenge['phone'] = $targetPhone;
        $challenge['email'] = $user->email;
        $challenge['channel'] = $channel;
        $challenge['otp_hash'] = Hash::make($otp);
        $challenge['expires_at'] = now()->addMinutes(10)->timestamp;
        $challenge['attempts'] = 0;
        $challenge['last_sent_at'] = now()->timestamp;
        $challenge['delivery_error'] = null;
        Cache::forget('otp_attempts:auth:' . $user->id);
        $request->session()->put('auth_wa_otp_challenge', $challenge);

        $statusMsg = $sent 
            ? 'OTP baru telah dikirim ke WhatsApp Anda.' 
            : 'WhatsApp sedang terkendala. Kode OTP baru telah dialihkan dan dikirimkan ke email Anda (' . $this->maskEmail((string) $user->email) . ').';

        return back()->with('status', $statusMsg);
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        } elseif (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return preg_match('/^62[1-9][0-9]{7,13}$/', $phone) ? $phone : null;
    }

    private function maskPhone(string $phone): string
    {
        return strlen($phone) > 6
            ? substr($phone, 0, 4) . str_repeat('*', max(2, strlen($phone) - 7)) . substr($phone, -3)
            : ($phone !== '' ? $phone : 'nomor WhatsApp owner');
    }

    private function maskEmail(string $email): string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);
        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(3, $len - 2));
        }

        return $maskedName . '@' . $domain;
    }
}
