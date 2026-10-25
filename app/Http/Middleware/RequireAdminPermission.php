<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce granular role & permission authorization on Platform Admin Console controllers and routes.
 */
final class RequireAdminPermission
{
    /**
     * Handle an incoming request for Admin Console.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi admin tidak valid atau akun dinonaktifkan.',
                    'error_code' => 'UNAUTHENTICATED_ADMIN',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Sesi berakhir atau akun dinonaktifkan. Silakan login kembali.');
        }

        // Super Administrator has unrestricted access across all platform modules
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check if admin has at least one of the required permissions
        foreach ($permissions as $permission) {
            if ($admin->hasPermission($permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki hak akses untuk aksi ini di Admin Console.',
                'error_code' => 'ADMIN_PERMISSION_DENIED',
                'required_permissions' => $permissions,
            ], Response::HTTP_FORBIDDEN);
        }

        abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda tidak memiliki hak akses (permission) untuk modul atau tindakan tersebut di Admin Console.');
    }
}
