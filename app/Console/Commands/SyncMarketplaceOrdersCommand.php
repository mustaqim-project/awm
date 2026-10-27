<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Marketplace\MarketplaceOrderService;
use App\Models\MarketplaceAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMarketplaceOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:sync-orders {--channel= : Specific channel to pull (shopee, tiktok_shop, tokopedia)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically pull and synchronize latest orders from connected marketplaces';

    public function handle(MarketplaceOrderService $orderService): int
    {
        $channel = $this->option('channel');

        $query = MarketplaceAccount::where('status', MarketplaceAccount::STATUS_CONNECTED);
        if ($channel) {
            $query->where('channel', $channel);
        }

        $accounts = $query->get();
        if ($accounts->isEmpty()) {
            $this->info('No connected marketplace accounts found to sync.');
            return self::SUCCESS;
        }

        $this->info("Found {$accounts->count()} connected account(s) to synchronize.");

        $totalSynced = 0;
        foreach ($accounts as $account) {
            try {
                $this->line("Pulling orders for [{$account->channel}] shop: {$account->shop_name} ({$account->shop_id})...");
                $orders = $orderService->pullAndSyncOrders($account);
                $count = count($orders);
                $totalSynced += $count;
                $this->info("✓ Synced {$count} orders for [{$account->channel}] {$account->shop_name}.");
            } catch (\Throwable $e) {
                $this->error("✗ Failed to sync orders for [{$account->channel}] {$account->shop_name}: " . $e->getMessage());
                Log::error("SyncMarketplaceOrdersCommand error: " . $e->getMessage(), [
                    'account_id' => $account->id,
                    'channel'    => $account->channel,
                ]);
            }
        }

        $this->info("Total orders synchronized: {$totalSynced}");
        return self::SUCCESS;
    }
}
