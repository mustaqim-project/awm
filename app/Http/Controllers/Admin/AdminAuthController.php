<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Security\BruteForceProtectionService;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\Google2FAService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class AdminAuthController extends Controller
{
    /**
     * Show admin login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Process admin login with brute force protection and Google Authenticator 2FA.
     */
    public function login(
        Request $request,
        BruteForceProtectionService $bruteForce,
        Google2FAService $google2FA
    ): RedirectResponse {
        $ip = (string) $request->ip();

        if ($bruteForce->isIpBlocked($ip)) {
            $remaining = $bruteForce->getRemainingBlockMinutes($ip);
            return back()->withErrors([
                'email' => "Alamat IP Anda diblokir sementara karena terdeteksi aktivitas login mencurigakan berulang kali. Silakan coba lagi dalam {$remaining} menit.",
            ])->onlyInput('email');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'totp_code' => ['nullable', 'string', 'max:16'],
        ]);
        $email = strtolower(trim($validated['email']));

        if ($bruteForce->isAccountLocked($ip, $email)) {
            $remaining = $bruteForce->getRemainingAccountLockoutMinutes($ip, $email);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login admin yang gagal. Akses dibatasi selama {$remaining} menit demi keamanan.",
            ])->onlyInput('email');
        }

        /** @var Admin|null $admin */
        $admin = Admin::where('email', $email)->first();

        // Verify credentials & active status
        if ($admin && $admin->is_active && Hash::check($validated['password'], $admin->password)) {
            // Check Google Authenticator 2FA if enabled on this admin account
            if ($admin->hasTwoFactorEnabled()) {
                $totpCode = trim((string) ($validated['totp_code'] ?? ''));

                if ($totpCode === '') {
                    return back()->withErrors([
                        'totp_code' => 'Akun Administrator ini dilindungi Google Authenticator. Masukkan kode 6 digit dari aplikasi authenticator Anda.',
                    ])->onlyInput('email');
                }

                if (! $google2FA->verifyCode((string) $admin->two_factor_secret, $totpCode)) {
                    $status = $bruteForce->recordFailedAttempt($ip, $email, 'admin');

                    if ($status['is_blocked']) {
                        return back()->withErrors([
                            'email' => "Alamat IP Anda diblokir sementara ({$status['remaining_minutes']} menit) karena terdeteksi aktivitas login berulang kali.",
                        ])->onlyInput('email');
                    }

                    if ($status['is_account_locked']) {
                        return back()->withErrors([
                            'email' => "Terlalu banyak percobaan login admin yang gagal. Akun dikunci sementara untuk IP Anda selama {$status['remaining_minutes']} menit demi keamanan.",
                        ])->onlyInput('email');
                    }

                    return back()->withErrors([
                        'totp_code' => 'Kode verifikasi Google Authenticator tidak valid atau telah kedaluwarsa. Pastikan jam pada perangkat Anda sinkron.',
                    ])->onlyInput('email');
                }
            }

            Auth::guard('admin')->login($admin, $request->boolean('remember'));

            $bruteForce->resetAttempts($ip, $email);
            $request->session()->regenerate();

            $intended = (string) $request->session()->get('url.intended', '');
            if ($intended !== '' && str_contains($intended, '/admin/login')) {
                $request->session()->forget('url.intended');
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        $status = $bruteForce->recordFailedAttempt($ip, $email, 'admin');

        if ($status['is_blocked']) {
            return back()->withErrors([
                'email' => "Alamat IP Anda diblokir sementara ({$status['remaining_minutes']} menit) karena terdeteksi aktivitas login berulang kali.",
            ])->onlyInput('email');
        }

        if ($status['is_account_locked']) {
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login admin yang gagal. Akun dikunci sementara untuk IP Anda selama {$status['remaining_minutes']} menit demi keamanan.",
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Kredensial administrator tidak valid atau akun dinonaktifkan.',
        ])->onlyInput('email');
    }

    /**
     * Log the admin out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Anda telah berhasil logout dari Admin Panel.');
    }
}
