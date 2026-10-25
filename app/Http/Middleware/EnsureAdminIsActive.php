<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan akun administrator yang sedang login berstatus aktif (is_active = true).
 * Jika dinonaktifkan oleh pimpinan/organisasi, segera putus sesi (logout) seketika.
 */
final class EnsureAdminIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();

            if ($admin !== null && ! $admin->is_active) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->with('error', 'Akses ditolak: Akun administrator Anda telah dinonaktifkan.')
                    ->withErrors([
                        'email' => 'Akses ditolak: Akun administrator Anda telah dinonaktifkan.',
                    ]);
            }
        }

        return $next($request);
    }
}
