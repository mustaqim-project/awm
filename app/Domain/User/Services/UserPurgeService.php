<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class UserPurgeService
{
    /**
     * Completely and permanently hard-delete a user account, their membership associations,
     * session tokens, and physical storage assets.
     *
     * @param User $user
     * @param Admin|null $admin The administrator executing the purge.
     * @return array{deleted_id: string, name: string, email: string}
     * @throws Throwable
     */
    public function purge(User $user, ?Admin $admin = null): array
    {
        $userId = (string) $user->id;
        $userName = (string) $user->name;
        $userEmail = (string) $user->email;
        $avatar = (string) ($user->avatar ?? '');

        Log::warning('Initiating full hard delete of user account', [
            'user_id' => $userId,
            'name' => $userName,
            'email' => $userEmail,
            'admin_id' => $admin?->id,
            'admin_email' => $admin?->email,
        ]);

        $driver = DB::getDriverName();

        // 1. Transactional database purge
        DB::transaction(function () use ($userId, $driver): void {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            try {
                // Detach from all businesses
                if (Schema::hasTable('business_users')) {
                    DB::table('business_users')->where('user_id', $userId)->delete();
                }

                // Delete tokens & sessions
                if (Schema::hasTable('personal_access_tokens')) {
                    DB::table('personal_access_tokens')
                        ->where('tokenable_id', $userId)
                        ->where('tokenable_type', User::class)
                        ->delete();
                }

                // Delete notifications
                if (Schema::hasTable('notifications')) {
                    DB::table('notifications')
                        ->where('notifiable_id', $userId)
                        ->where('notifiable_type', User::class)
                        ->delete();
                }

                // Delete community likes and comments made by this user
                if (Schema::hasTable('community_likes')) {
                    DB::table('community_likes')->where('user_id', $userId)->delete();
                }
                if (Schema::hasTable('community_comments')) {
                    DB::table('community_comments')->where('user_id', $userId)->delete();
                }

                // Nullify creator/actor foreign keys in transactional tables to prevent broken cascades
                $nullableUserCols = [
                    ['table' => 'invoices', 'col' => 'created_by'],
                    ['table' => 'invoice_payments', 'col' => 'created_by'],
                    ['table' => 'cash_transactions', 'col' => 'created_by'],
                    ['table' => 'expenses', 'col' => 'recorded_by'],
                    ['table' => 'journal_entries', 'col' => 'created_by'],
                    ['table' => 'goods_receipts', 'col' => 'received_by'],
                    ['table' => 'goods_issues', 'col' => 'created_by'],
                    ['table' => 'stock_movements', 'col' => 'created_by'],
                    ['table' => 'stock_opnames', 'col' => 'conducted_by'],
                    ['table' => 'stock_opnames', 'col' => 'reconciled_by'],
                    ['table' => 'stock_transfers', 'col' => 'created_by'],
                    ['table' => 'stock_transfers', 'col' => 'received_by'],
                    ['table' => 'stock_adjustments', 'col' => 'created_by'],
                    ['table' => 'stock_adjustments', 'col' => 'approved_by'],
                    ['table' => 'audit_logs', 'col' => 'user_id'],
                    ['table' => 'ai_token_usages', 'col' => 'user_id'],
                ];

                foreach ($nullableUserCols as $ref) {
                    if (Schema::hasTable($ref['table'])) {
                        try {
                            DB::table($ref['table'])->where($ref['col'], $userId)->update([$ref['col'] => null]);
                        } catch (Throwable) {
                            // Ignore if column is not nullable or doesn't exist
                        }
                    }
                }

                // Delete user storage file records
                if (Schema::hasTable('storage_files')) {
                    DB::table('storage_files')->where('user_id', $userId)->delete();
                }

                // Delete the user record itself
                DB::table('users')->where('id', $userId)->delete();
            } finally {
                if ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = ON;');
                } else {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            }
        });

        // 2. Physical storage files cleanup
        try {
            if (! empty($avatar) && Storage::disk('public')->exists($avatar)) {
                Storage::disk('public')->delete($avatar);
            }

            if (Storage::disk('public')->exists("users/{$userId}")) {
                Storage::disk('public')->deleteDirectory("users/{$userId}");
            }

            if (Storage::disk('local')->exists("users/{$userId}")) {
                Storage::disk('local')->deleteDirectory("users/{$userId}");
            }
        } catch (Throwable $e) {
            Log::error('Error cleaning up user storage assets', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Completed full hard delete of user account', [
            'user_id' => $userId,
            'name' => $userName,
            'email' => $userEmail,
            'admin_email' => $admin?->email,
        ]);

        return [
            'deleted_id' => $userId,
            'name' => $userName,
            'email' => $userEmail,
        ];
    }
}
