<?php

declare(strict_types=1);

namespace App\Domain\Business\Services;

use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Models\Admin;
use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class BusinessPurgeService
{
    public function __construct(
        private readonly StorageTrackingService $storageTrackingService
    ) {}

    /**
     * Completely and permanently hard-delete a business, its relational database records,
     * child entities, and all physical storage files and assets.
     *
     * @param Business $business
     * @param Admin|null $admin The administrator who authorized and executed the purge.
     * @return array{deleted_id: string, name: string, purged_tables_count: int, purged_files: bool}
     * @throws Throwable
     */
    public function purge(Business $business, ?Admin $admin = null): array
    {
        $businessId = (string) $business->id;
        $businessName = (string) $business->name;
        $businessSlug = (string) $business->slug;
        $logoPath = (string) ($business->logo_path ?? '');

        Log::warning('Initiating full hard delete of business tenant', [
            'business_id' => $businessId,
            'business_name' => $businessName,
            'admin_id' => $admin?->id,
            'admin_email' => $admin?->email,
        ]);

        $driver = DB::getDriverName();

        // 1. Identify all database tables containing a business_id column
        if ($driver === 'sqlite') {
            $allTables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tablesWithBizId = [];
            foreach ($allTables as $t) {
                $tableName = (string) $t->name;
                $cols = DB::select("PRAGMA table_info('{$tableName}')");
                foreach ($cols as $col) {
                    if ($col->name === 'business_id') {
                        $tablesWithBizId[] = $tableName;
                        break;
                    }
                }
            }
            $tables = array_filter($tablesWithBizId, fn ($tbl) => $tbl !== 'businesses');
        } else {
            $database = config('database.connections.mysql.database');
            $tablesWithBizId = DB::select("
                SELECT TABLE_NAME 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = ? AND COLUMN_NAME = 'business_id'
            ", [$database]);

            $tables = array_filter(
                array_column($tablesWithBizId, 'TABLE_NAME'),
                fn ($tbl) => $tbl !== 'businesses'
            );
        }

        // 2. Perform transactional database purge with foreign key checks safely isolated
        DB::transaction(function () use ($businessId, $tables, $driver): void {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            try {
                // A. Explicitly purge child relation items that reference parent records
                $this->purgeChildTables($businessId);

                // B. Delete records from all tables containing business_id
                foreach ($tables as $table) {
                    try {
                        DB::table($table)->where('business_id', $businessId)->delete();
                    } catch (Throwable $e) {
                        Log::error("Failed to delete business rows from table: {$table}", [
                            'business_id' => $businessId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // C. Remove any membership bindings in pivot tables
                DB::table('business_users')->where('business_id', $businessId)->delete();

                // D. Delete the business entity itself
                DB::table('businesses')->where('id', $businessId)->delete();
            } finally {
                if ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = ON;');
                } else {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            }
        });

        // 3. Purge physical files and directories from storage disks
        $filesPurged = $this->purgeStorageAssets($businessId, $businessSlug, $logoPath, $business);

        Log::info('Completed full hard delete of business tenant', [
            'business_id' => $businessId,
            'business_name' => $businessName,
            'admin_email' => $admin?->email,
            'purged_tables_count' => count($tables),
        ]);

        return [
            'deleted_id' => $businessId,
            'name' => $businessName,
            'purged_tables_count' => count($tables),
            'purged_files' => $filesPurged,
        ];
    }

    /**
     * Purge nested child records linked to parent tables belonging to the business.
     */
    private function purgeChildTables(string $businessId): void
    {
        // POS order items and modifiers
        if (Schema::hasTable('pos_orders')) {
            $posOrderIds = DB::table('pos_orders')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($posOrderIds)) {
                if (Schema::hasTable('pos_order_items')) {
                    $posOrderItemIds = DB::table('pos_order_items')->whereIn('pos_order_id', $posOrderIds)->pluck('id')->all();
                    if (! empty($posOrderItemIds) && Schema::hasTable('pos_order_item_modifiers')) {
                        DB::table('pos_order_item_modifiers')->whereIn('pos_order_item_id', $posOrderItemIds)->delete();
                    }
                    DB::table('pos_order_items')->whereIn('pos_order_id', $posOrderIds)->delete();
                }
                if (Schema::hasTable('pos_order_payments')) {
                    DB::table('pos_order_payments')->whereIn('pos_order_id', $posOrderIds)->delete();
                }
            }
        }

        // Sales order items
        if (Schema::hasTable('sales_orders') && Schema::hasTable('sales_order_items')) {
            $salesOrderIds = DB::table('sales_orders')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($salesOrderIds)) {
                DB::table('sales_order_items')->whereIn('sales_order_id', $salesOrderIds)->delete();
            }
        }

        // Purchase order & return items
        if (Schema::hasTable('purchase_orders') && Schema::hasTable('purchase_order_items')) {
            $poIds = DB::table('purchase_orders')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($poIds)) {
                DB::table('purchase_order_items')->whereIn('purchase_order_id', $poIds)->delete();
            }
        }
        if (Schema::hasTable('purchase_returns') && Schema::hasTable('purchase_return_items')) {
            $prIds = DB::table('purchase_returns')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($prIds)) {
                DB::table('purchase_return_items')->whereIn('purchase_return_id', $prIds)->delete();
            }
        }

        // Sales returns items
        if (Schema::hasTable('sales_returns') && Schema::hasTable('sales_return_items')) {
            $srIds = DB::table('sales_returns')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($srIds)) {
                DB::table('sales_return_items')->whereIn('sales_return_id', $srIds)->delete();
            }
        }

        // Invoices items
        if (Schema::hasTable('invoices') && Schema::hasTable('invoice_items')) {
            $invIds = DB::table('invoices')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($invIds)) {
                DB::table('invoice_items')->whereIn('invoice_id', $invIds)->delete();
            }
        }

        // Journal entry lines
        if (Schema::hasTable('journal_entries') && Schema::hasTable('journal_entry_lines')) {
            $jeIds = DB::table('journal_entries')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($jeIds)) {
                DB::table('journal_entry_lines')->whereIn('journal_entry_id', $jeIds)->delete();
            }
        }

        // Goods receipts and issues items
        if (Schema::hasTable('goods_receipts') && Schema::hasTable('goods_receipt_items')) {
            $grIds = DB::table('goods_receipts')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($grIds)) {
                DB::table('goods_receipt_items')->whereIn('goods_receipt_id', $grIds)->delete();
            }
        }
        if (Schema::hasTable('goods_issues') && Schema::hasTable('goods_issue_items')) {
            $giIds = DB::table('goods_issues')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($giIds)) {
                DB::table('goods_issue_items')->whereIn('goods_issue_id', $giIds)->delete();
            }
        }

        // Stock opname and transfer items
        if (Schema::hasTable('stock_opnames') && Schema::hasTable('stock_opname_items')) {
            $soIds = DB::table('stock_opnames')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($soIds)) {
                DB::table('stock_opname_items')->whereIn('stock_opname_id', $soIds)->delete();
            }
        }
        if (Schema::hasTable('stock_transfers') && Schema::hasTable('stock_transfer_items')) {
            $stIds = DB::table('stock_transfers')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($stIds)) {
                DB::table('stock_transfer_items')->whereIn('stock_transfer_id', $stIds)->delete();
            }
        }
        if (Schema::hasTable('stock_adjustments') && Schema::hasTable('stock_adjustment_items')) {
            $saIds = DB::table('stock_adjustments')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($saIds)) {
                DB::table('stock_adjustment_items')->whereIn('stock_adjustment_id', $saIds)->delete();
            }
        }

        // Commerce orders items & batches
        if (Schema::hasTable('commerce_orders')) {
            $coIds = DB::table('commerce_orders')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($coIds)) {
                if (Schema::hasTable('commerce_order_items')) {
                    DB::table('commerce_order_items')->whereIn('commerce_order_id', $coIds)->delete();
                }
                if (Schema::hasTable('commerce_order_batches')) {
                    DB::table('commerce_order_batches')->whereIn('commerce_order_id', $coIds)->delete();
                }
            }
        }

        // Customer cart items
        if (Schema::hasTable('customer_carts') && Schema::hasTable('customer_cart_items')) {
            $cartIds = DB::table('customer_carts')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($cartIds)) {
                DB::table('customer_cart_items')->whereIn('cart_id', $cartIds)->delete();
            }
        }

        // BOM items & headers
        if (Schema::hasTable('cost_models')) {
            $costModelIds = DB::table('cost_models')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($costModelIds)) {
                if (Schema::hasTable('bom_headers')) {
                    $bomHeaderIds = DB::table('bom_headers')->whereIn('cost_model_id', $costModelIds)->pluck('id')->all();
                    if (! empty($bomHeaderIds)) {
                        if (Schema::hasTable('bom_items')) {
                            DB::table('bom_items')->whereIn('bom_header_id', $bomHeaderIds)->delete();
                        }
                        DB::table('bom_headers')->whereIn('id', $bomHeaderIds)->delete();
                    }
                }
                if (Schema::hasTable('cost_model_activities')) {
                    DB::table('cost_model_activities')->whereIn('cost_model_id', $costModelIds)->delete();
                }
                if (Schema::hasTable('cost_model_components')) {
                    DB::table('cost_model_components')->whereIn('cost_model_id', $costModelIds)->delete();
                }
                if (Schema::hasTable('cost_model_labors')) {
                    DB::table('cost_model_labors')->whereIn('cost_model_id', $costModelIds)->delete();
                }
                if (Schema::hasTable('cost_model_machines')) {
                    DB::table('cost_model_machines')->whereIn('cost_model_id', $costModelIds)->delete();
                }
            }
        }

        // Modifier groups & options
        if (Schema::hasTable('modifier_groups')) {
            $modGroupIds = DB::table('modifier_groups')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($modGroupIds)) {
                if (Schema::hasTable('modifier_options')) {
                    $optionIds = DB::table('modifier_options')->whereIn('modifier_group_id', $modGroupIds)->pluck('id')->all();
                    if (! empty($optionIds)) {
                        if (Schema::hasTable('modifier_option_materials')) {
                            DB::table('modifier_option_materials')->whereIn('modifier_option_id', $optionIds)->delete();
                        }
                        DB::table('modifier_options')->whereIn('id', $optionIds)->delete();
                    }
                }
                if (Schema::hasTable('product_modifier_groups')) {
                    DB::table('product_modifier_groups')->whereIn('modifier_group_id', $modGroupIds)->delete();
                }
            }
        }

        // Roles & role permissions
        if (Schema::hasTable('roles') && Schema::hasTable('role_permissions')) {
            $roleIds = DB::table('roles')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($roleIds)) {
                DB::table('role_permissions')->whereIn('role_id', $roleIds)->delete();
            }
        }

        // Social media post media
        if (Schema::hasTable('social_media_posts') && Schema::hasTable('social_post_media')) {
            $socialPostIds = DB::table('social_media_posts')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($socialPostIds)) {
                DB::table('social_post_media')->whereIn('social_media_post_id', $socialPostIds)->delete();
            }
        }

        // WhatsApp broadcast campaign recipients
        if (Schema::hasTable('whatsapp_broadcast_campaigns') && Schema::hasTable('whatsapp_broadcast_recipients')) {
            $campaignIds = DB::table('whatsapp_broadcast_campaigns')->where('business_id', $businessId)->pluck('id')->all();
            if (! empty($campaignIds)) {
                DB::table('whatsapp_broadcast_recipients')->whereIn('campaign_id', $campaignIds)->delete();
            }
        }
    }

    /**
     * Permanently remove all physical files and directories associated with this business.
     */
    private function purgeStorageAssets(string $businessId, string $slug, string $logoPath, Business $business): bool
    {
        try {
            // Storage tracking service handles standard tenant storage directories
            $this->storageTrackingService->handleBusinessDeleted($business);

            // Double check directories on public disk
            $publicDirs = [
                "businesses/{$businessId}",
                "bisnis/{$slug}",
            ];
            foreach ($publicDirs as $dir) {
                if (Storage::disk('public')->exists($dir)) {
                    Storage::disk('public')->deleteDirectory($dir);
                }
            }

            // Double check private disk directories
            $localDirs = [
                "private/bisnis/{$slug}",
                "businesses/{$businessId}",
            ];
            foreach ($localDirs as $dir) {
                if (Storage::disk('local')->exists($dir)) {
                    Storage::disk('local')->deleteDirectory($dir);
                }
            }

            // Delete logo if file exists
            if (! empty($logoPath) && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            return true;
        } catch (Throwable $e) {
            Log::error('Error cleaning up business storage assets', [
                'business_id' => $businessId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
