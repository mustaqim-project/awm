<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Security\BruteForceProtectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Process admin login with brute force protection.
     */
    public function login(Request $request, BruteForceProtectionService $bruteForce): RedirectResponse
    {
        $ip = (string) $request->ip();

        if ($bruteForce->isIpBlocked($ip)) {
            $remaining = $bruteForce->getRemainingBlockMinutes($ip);
            return back()->withErrors([
                'email' => "Alamat IP Anda diblokir sementara karena terdeteksi aktivitas login mencurigakan berulang kali. Silakan coba lagi dalam {$remaining} menit.",
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $credentials['email'] = strtolower(trim($credentials['email']));

        if ($bruteForce->isAccountLocked($ip, $credentials['email'])) {
            $remaining = $bruteForce->getRemainingAccountLockoutMinutes($ip, $credentials['email']);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login admin yang gagal. Akses dibatasi selama {$remaining} menit demi keamanan.",
            ])->onlyInput('email');
        }

        $credentials['is_active'] = true;

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $bruteForce->resetAttempts($ip, $credentials['email']);
            $request->session()->regenerate();

            $intended = (string) $request->session()->get('url.intended', '');
            if ($intended !== '' && str_contains($intended, '/admin/login')) {
                $request->session()->forget('url.intended');
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        $status = $bruteForce->recordFailedAttempt($ip, $credentials['email'], 'admin');

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
