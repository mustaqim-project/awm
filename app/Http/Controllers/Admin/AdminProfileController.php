<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\Google2FAService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

final class AdminProfileController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly Google2FAService $google2FAService
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.profile.manage'),
        ];
    }

    /**
     * Show admin profile, password, and Google Authenticator 2FA management page.
     */
    public function index(Request $request): View
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $has2FA = $admin->hasTwoFactorEnabled();

        // If not yet enabled, provide a secret key and QR code ready to scan
        if (! $has2FA) {
            $twoFactorSecret = (string) $request->session()->get('admin_pending_2fa_secret', '');
            if (empty($twoFactorSecret)) {
                $twoFactorSecret = $this->google2FAService->generateSecret();
                $request->session()->put('admin_pending_2fa_secret', $twoFactorSecret);
            }

            $qrCodeSvg = $this->google2FAService->getQrCodeSvg('Cooca Platform', (string) $admin->email, $twoFactorSecret);
        } else {
            $twoFactorSecret = (string) $admin->two_factor_secret;
            $qrCodeSvg = '';
        }

        return view('admin.profile.index', compact('admin', 'has2FA', 'twoFactorSecret', 'qrCodeSvg'));
    }

    /**
     * Update admin profile name and email.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email,' . $admin->id],
        ], [
            'name.required' => 'Nama lengkap administrator wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan oleh administrator lain.',
        ]);

        $admin->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return back()->with('success', 'Profil administrator berhasil diperbarui.');
    }

    /**
     * Update admin password (Ganti Kata Sandi).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed', PasswordRule::default()],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai dengan akun Anda.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.string' => 'Kata sandi baru harus berupa teks yang valid.',
            'password.min' => 'Kata sandi baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $admin->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Kata sandi Administrator berhasil diubah.');
    }

    /**
     * Confirm and enable Google Authenticator 2FA.
     */
    public function enableTwoFactor(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'secret' => ['required', 'string', 'size:16'],
            'totp_code' => ['required', 'string', 'size:6'],
        ], [
            'secret.required' => 'Kunci rahasia 2FA tidak ditemukan.',
            'totp_code.required' => 'Kode 6 digit Google Authenticator wajib diisi.',
            'totp_code.size' => 'Kode Google Authenticator harus berupa 6 digit angka.',
        ]);

        $secret = strtoupper($validated['secret']);
        $code = $validated['totp_code'];

        if (! $this->google2FAService->verifyCode($secret, $code)) {
            return back()->with('error', 'Kode verifikasi Google Authenticator tidak cocok. Pastikan jam pada perangkat Anda sinkron.');
        }

        $admin->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->forget('admin_pending_2fa_secret');

        return back()->with('success', 'Google Authenticator (2FA) berhasil diaktifkan. Akun Anda kini terlindungi.');
    }

    /**
     * Disable Google Authenticator 2FA (requires current password and TOTP code).
     */
    public function disableTwoFactor(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'password' => ['required', 'current_password:admin'],
            'totp_code' => ['required', 'string', 'size:6'],
        ], [
            'password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.current_password' => 'Kata sandi akun administrator tidak cocok.',
            'totp_code.required' => 'Kode Google Authenticator wajib diisi.',
            'totp_code.size' => 'Kode harus terdiri dari 6 digit angka.',
        ]);

        if (! $this->google2FAService->verifyCode((string) $admin->two_factor_secret, $validated['totp_code'])) {
            return back()->with('error', 'Kode Google Authenticator tidak valid. Penonaktifan 2FA dibatalkan.');
        }

        $admin->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('success', 'Google Authenticator (2FA) telah dinonaktifkan.');
    }
}
