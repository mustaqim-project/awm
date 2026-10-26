<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, HasUuid, Notifiable, SoftDeletes;

    protected $table = 'admins';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'super_admin',
        'is_active' => true,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine if admin is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'superadmin'], true) || empty($this->role);
    }

    /**
     * Check if admin has specific role(s).
     *
     * @param string|array<int, string> $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }

        return $this->role === $roles;
    }

    /**
     * Get associated global role model.
     */
    public function roleModel(): ?Role
    {
        return Role::whereNull('business_id')->where('slug', $this->role)->first();
    }

    /**
     * Check if admin user has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        // Cache role permissions for high-performance requests (reset upon seeder/cache clear)
        $rolePermissions = \Illuminate\Support\Facades\Cache::remember(
            "admin_role_perms_{$this->role}",
            now()->addHours(6),
            function () {
                $role = Role::whereNull('business_id')
                    ->where('slug', $this->role)
                    ->with('permissions')
                    ->first();

                return $role ? $role->permissions->pluck('slug')->all() : [];
            }
        );

        return in_array($permission, $rolePermissions, true);
    }

    /**
     * Check if admin has any of the given permissions.
     *
     * @param array<int, string> $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
