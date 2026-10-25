<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminRoleController extends Controller implements HasMiddleware
{
    /**
     * Map controller actions to granular platform admin permissions.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.roles.view', only: ['index']),
            new Middleware('require.admin.permission:admin.roles.manage', only: [
                'store',
                'update',
                'destroy',
                'updateAdminRole',
            ]),
        ];
    }

    /**
     * Display the Roles & Permissions Management Hub.
     */
    public function index(): View
    {
        // 1. All Platform Roles (business_id is null)
        $roles = Role::whereNull('business_id')
            ->with('permissions')
            ->orderBy('id')
            ->get();

        // 2. All Platform Admin Permissions (starts with 'admin.')
        $rawPermissions = Permission::where('slug', 'like', 'admin.%')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $permissions = $rawPermissions->groupBy('category');

        // 3. Platform Admin Members
        $admins = Admin::orderBy('name')->get();

        // Count of admins per role slug
        /** @var array<string, int> $adminCountsByRole */
        $adminCountsByRole = Admin::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->all();

        return view('admin.roles.index', compact(
            'roles',
            'permissions',
            'admins',
            'adminCountsByRole'
        ));
    }

    /**
     * Store a new custom platform admin role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,slug'],
        ]);

        $slug = Str::slug($validated['name'], '_');
        if (Role::whereNull('business_id')->where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Nama peran (role) tersebut sudah digunakan di platform.']);
        }

        $role = Role::create([
            'business_id' => null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_preset' => false,
            'is_default' => false,
        ]);

        $allowedIds = Permission::whereIn('slug', $validated['permissions'] ?? [])
            ->where('slug', 'like', 'admin.%')
            ->pluck('id');

        $role->permissions()->sync($allowedIds);

        Cache::forget("admin_role_perms_{$slug}");

        return redirect()->route('admin.roles.index')->with('success', "Peran '{$role->name}' berhasil dibuat.");
    }

    /**
     * Update an existing platform role permissions & details.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless(is_null($role->business_id), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,slug'],
        ]);

        // If custom role, allow updating name & description
        if (! $role->is_preset && $role->slug !== 'super_admin') {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);
        }

        // Sync permissions (if not super_admin, super admin always has everything)
        if ($role->slug !== 'super_admin') {
            $allowedIds = Permission::whereIn('slug', $validated['permissions'] ?? [])
                ->where('slug', 'like', 'admin.%')
                ->pluck('id');

            $role->permissions()->sync($allowedIds);
        }

        Cache::forget("admin_role_perms_{$role->slug}");

        return redirect()->route('admin.roles.index')->with('success', "Hak akses peran '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Delete a custom platform role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        abort_unless(is_null($role->business_id), 404);

        if ($role->is_preset || $role->slug === 'super_admin') {
            return back()->withErrors(['error' => 'Peran bawaan sistem (preset) tidak dapat dihapus.']);
        }

        // Check if any admin has this role
        if (Admin::where('role', $role->slug)->exists()) {
            return back()->withErrors(['error' => 'Peran ini masih digunakan oleh staf admin. Silakan ubah peran staf terlebih dahulu sebelum menghapus peran ini.']);
        }

        $name = $role->name;
        $slug = $role->slug;

        $role->permissions()->detach();
        $role->delete();

        Cache::forget("admin_role_perms_{$slug}");

        return redirect()->route('admin.roles.index')->with('success', "Peran '{$name}' berhasil dihapus.");
    }

    /**
     * Assign or update role of a platform administrator.
     */
    public function updateAdminRole(Request $request, Admin $admin): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string'],
        ]);

        $targetRole = Role::whereNull('business_id')->where('slug', $validated['role'])->first();
        if (! $targetRole) {
            return back()->withErrors(['role' => 'Peran yang dipilih tidak ditemukan dalam sistem.']);
        }

        // Prevent removing the last active super_admin
        if ($admin->isSuperAdmin() && $validated['role'] !== 'super_admin') {
            $superAdminCount = Admin::where('role', 'super_admin')->where('is_active', true)->count();
            if ($superAdminCount <= 1) {
                return back()->withErrors(['error' => 'Tidak dapat mengubah peran Super Administrator terakhir pada sistem platform.']);
            }
        }

        $oldRole = $admin->role;
        $admin->update(['role' => $validated['role']]);

        Cache::forget("admin_role_perms_{$oldRole}");
        Cache::forget("admin_role_perms_{$validated['role']}");

        return redirect()->route('admin.roles.index')->with('success', "Peran untuk {$admin->name} berhasil diperbarui menjadi {$targetRole->name}.");
    }
}
