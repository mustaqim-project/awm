<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\User\Services\UserPurgeService;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Services\Google2FAService;
use App\Support\Security\CsvSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminUserController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.users.view', only: ['index']),
            new Middleware('require.admin.permission:admin.users.export', only: ['export']),
            new Middleware('require.admin.permission:admin.users.delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of registered users.
     */
    public function index(Request $request): View
    {
        $query = User::with(['activeBusiness', 'businesses'])->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = \App\Support\PaginationHelper::perPage($request, 25);
        $users = $query->paginate($perPage)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Export all users to CSV format (Nama, Email, Bisnis, Tanggal Daftar, Google Login).
     */
    public function export(): StreamedResponse
    {
        $fileName = 'users_export_'.now()->format('Ymd_His').'.csv';

        $users = User::with('activeBusiness')->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($users): void {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header Row
            fputcsv($file, [
                'ID',
                'Nama Lengkap',
                'Email',
                'Bisnis Aktif',
                'Metode Login',
                'Tanggal Registrasi',
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    CsvSanitizer::sanitize($user->name),
                    CsvSanitizer::sanitize($user->email),
                    CsvSanitizer::sanitize($user->activeBusiness?->name ?? 'Belum Diatur'),
                    $user->google_id ? 'Google OAuth' : 'Email/Password',
                    $user->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Completely and permanently hard delete a user account with Google Authenticator 2FA verification.
     */
    public function destroy(
        Request $request,
        User $user,
        UserPurgeService $purgeService,
        Google2FAService $google2FAService
    ): RedirectResponse {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->hasTwoFactorEnabled()) {
            return back()->with(
                'error',
                'Penghapusan permanen ditolak: Akun administrator Anda belum mengaktifkan Google Authenticator (2FA). Silakan aktifkan 2FA di profil Anda terlebih dahulu.'
            );
        }

        $validated = $request->validate([
            'confirm_email' => ['required', 'string'],
            'totp_code' => ['required', 'string'],
        ], [
            'confirm_email.required' => 'Konfirmasi email pengguna wajib diisi.',
            'totp_code.required' => 'Kode 6 digit Google Authenticator wajib diisi.',
        ]);

        $expectedEmail = trim((string) $user->email);
        $enteredConfirm = trim((string) $validated['confirm_email']);

        if (strcasecmp($enteredConfirm, $expectedEmail) !== 0) {
            return back()->with(
                'error',
                "Email konfirmasi tidak sesuai. Ketik persis \"{$user->email}\" untuk melanjutkan."
            );
        }

        if (! $google2FAService->verifyCode((string) $admin->two_factor_secret, (string) $validated['totp_code'])) {
            return back()->with(
                'error',
                'Kode verifikasi Google Authenticator tidak valid atau telah kedaluwarsa. Operasi dibatalkan demi keamanan data platform.'
            );
        }

        try {
            $purgeResult = $purgeService->purge($user, $admin);

            return redirect()->route('admin.users.index')->with(
                'success',
                "Pengguna \"{$purgeResult['name']}\" ({$purgeResult['email']}) beserta sesi dan aset terkait telah dihapus secara permanen dari sistem."
            );
        } catch (\Throwable $e) {
            Log::error('Failed to execute hard delete on user: ' . $e->getMessage(), [
                'target_user_id' => $user->id,
                'admin_id' => $admin->id,
            ]);

            return back()->with(
                'error',
                'Terjadi kesalahan saat memproses penghapusan permanen pengguna: ' . $e->getMessage()
            );
        }
    }
}
