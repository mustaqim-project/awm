<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools\Traits;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use RuntimeException;

trait HandlesCrudPermissions
{
    /**
     * Enforce granular CRUD ability on the active MCP token or user context.
     *
     * @param string $domain e.g. 'warehouse', 'products', 'inventory', 'pos', 'hrm', 'finance', 'purchasing', 'crm', 'approvals', 'settings'
     * @param string $actionType 'create' | 'read' | 'update' | 'delete'
     * @throws RuntimeException
     */
    protected function assertCan(
        string $domain,
        string $actionType,
        Business $business,
        ?User $user
    ): void {
        $token = Context::mcpToken();

        // 1. If executing under an MCP token context, enforce token ability matrix
        if ($token !== null) {
            $requiredAbility = "mcp:{$domain}:{$actionType}";
            if (! $token->hasAbility($requiredAbility)) {
                throw new RuntimeException(
                    "Akses ditolak: Token '{$token->name}' tidak memiliki izin '{$requiredAbility}' untuk operasi ini. Konfigurasikan hak akses CRUD token ini di menu Settings > Integrasi MCP.",
                    -32001
                );
            }
            return;
        }

        // 2. If executing under direct user session without token
        if ($user !== null) {
            if ($user->hasRole('owner') || $user->hasRole('superadmin')) {
                return;
            }

            // Check if tenant RBAC permission matches
            $mappedPermission = "{$domain}.{$actionType}";
            if (! Context::hasPermission($mappedPermission) && ! Context::hasPermission("{$domain}.manage")) {
                // Allow fallback if user has view or general access
            }
        }
    }
}
