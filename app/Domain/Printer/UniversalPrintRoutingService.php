<?php

declare(strict_types=1);

namespace App\Domain\Printer;

use App\Domain\Printer\Connectors\AgentPayloadConnector;
use App\Domain\Printer\Connectors\FileConnector;
use App\Domain\Printer\Connectors\NetworkConnector;
use App\Domain\Printer\Connectors\WindowsConnector;
use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosPrintJob;
use App\Models\PosPrinter;
use App\Models\PosPrinterRoutingRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class UniversalPrintRoutingService
{
    public function __construct(
        private readonly EscposFormatter $formatter = new EscposFormatter(),
        private readonly CashDrawerService $cashDrawer = new CashDrawerService()
    ) {}

    /**
     * Build routed print jobs for an order based on multi-station configuration and rules.
     *
     * @param array{
     *     single_printer_mode?: bool,
     *     open_drawer?: bool,
     *     is_reprint?: bool,
     *     actor_id?: string|null,
     *     print_receipt?: bool,
     *     print_stations?: bool,
     *     print_spk?: bool,
     *     print_packing?: bool
     * } $options
     *
     * @return array<int, array{
     *     job_id: string,
     *     document_type: string,
     *     station: string,
     *     printer: PosPrinter,
     *     printer_id: string,
     *     printer_name: string,
     *     connection_type: string,
     *     interface_address: string,
     *     port: int,
     *     paper_width: string,
     *     is_fallback: bool,
     *     fallback_from: string|null,
     *     items_count: int,
     *     raw_bytes: string,
     *     base64_payload: string
     * }>
     */
    public function planPrintJobs(PosOrder $order, ?string $locationId = null, array $options = []): array
    {
        $order->loadMissing(['items.product.category', 'items.modifiers', 'customer', 'user', 'location', 'business', 'technician']);
        $business = $order->business;
        $businessId = $business->id;
        $locationId = $locationId ?? $order->location_id;

        // Fetch all active printers for this business/location
        $printers = PosPrinter::where('business_id', $businessId)
            ->active()
            ->forLocation($locationId)
            ->get();

        // Cashier printer
        $cashierPrinter = $printers->first(fn(PosPrinter $p) => $p->isCashier())
            ?? $printers->first(fn(PosPrinter $p) => $p->is_default)
            ?? $printers->first()
            ?? $this->createVirtualDefaultPrinter($businessId);

        $isSinglePrinter = $options['single_printer_mode'] ?? ($printers->count() <= 1);
        $printReceipt = $options['print_receipt'] ?? true;
        $printStations = $options['print_stations'] ?? true;
        $isReprint = !empty($options['is_reprint']) || (int) ($order->print_count ?? 0) > 0;

        $jobs = [];

        // 1. Customer Receipt (Cashier Printer)
        if ($printReceipt) {
            $openDrawer = $options['open_drawer'] ?? $this->cashDrawer->shouldOpenForOrder($order, $isReprint);
            $receiptBytes = $this->formatter->formatReceipt($order, $cashierPrinter, [
                'open_drawer' => $openDrawer,
                'is_reprint' => $isReprint,
                'reprint_count' => (int) $order->print_count,
            ]);

            $jobs[] = [
                'job_id' => (string) Str::uuid(),
                'document_type' => PosPrinterRoutingRule::DOC_CUSTOMER_RECEIPT,
                'station' => PosPrinter::STATION_CASHIER,
                'printer' => $cashierPrinter,
                'printer_id' => $cashierPrinter->id,
                'printer_name' => $cashierPrinter->name,
                'connection_type' => $cashierPrinter->connection_type,
                'interface_address' => $cashierPrinter->interface_address,
                'port' => (int) $cashierPrinter->port,
                'paper_width' => $cashierPrinter->paper_width,
                'is_fallback' => false,
                'fallback_from' => null,
                'items_count' => $order->items->count(),
                'raw_bytes' => $receiptBytes,
                'base64_payload' => base64_encode($receiptBytes),
            ];
        }

        if (!$printStations) {
            return $jobs;
        }

        // 2. Multi-Industry Service / SPK Work Order (Automotive, Workshop, Repair, Salon, Barbershop, Laundry)
        $hasServiceOrWorkshop = !empty($order->vehicle_license_plate)
            || !empty($order->technician_id)
            || !empty($order->laundry_weight_kg)
            || $order->items->contains(fn($item) => $item->product?->is_service || str_contains(strtolower((string) $item->product?->type), 'service'));

        if ($hasServiceOrWorkshop) {
            $spkPrinter = $isSinglePrinter
                ? $cashierPrinter
                : ($printers->first(fn(PosPrinter $p) => $p->isSpk()) ?? $cashierPrinter);

            $spkBytes = $this->formatter->formatSpkServiceOrder($order, $spkPrinter, $order->items->all());
            $isFallback = $spkPrinter->id === $cashierPrinter->id && !$cashierPrinter->isSpk() && !$isSinglePrinter;

            $jobs[] = [
                'job_id' => (string) Str::uuid(),
                'document_type' => PosPrinterRoutingRule::DOC_SPK_SERVICE,
                'station' => PosPrinter::STATION_SPK,
                'printer' => $spkPrinter,
                'printer_id' => $spkPrinter->id,
                'printer_name' => $spkPrinter->name,
                'connection_type' => $spkPrinter->connection_type,
                'interface_address' => $spkPrinter->interface_address,
                'port' => (int) $spkPrinter->port,
                'paper_width' => $spkPrinter->paper_width,
                'is_fallback' => $isFallback,
                'fallback_from' => $isFallback ? 'Printer Bengkel/SPK' : null,
                'items_count' => $order->items->count(),
                'raw_bytes' => $spkBytes,
                'base64_payload' => base64_encode($spkBytes),
            ];
        }

        // 3. F&B Preparation Tickets: Partition items between Kitchen, Bar, and Pastry
        $kitchenItems = [];
        $barItems = [];
        $pastryItems = [];

        foreach ($order->items as $item) {
            $product = $item->product;
            $catName = strtolower((string) ($product?->category?->name ?? ''));
            $prodName = strtolower((string) ($item->product_name ?? ''));

            // Check if beverage/bar
            $isBeverage = str_contains($catName, 'minuman')
                || str_contains($catName, 'drink')
                || str_contains($catName, 'beverage')
                || str_contains($catName, 'kopi')
                || str_contains($catName, 'coffee')
                || str_contains($catName, 'tea')
                || str_contains($catName, 'bar')
                || str_contains($prodName, 'latte')
                || str_contains($prodName, 'kopi')
                || str_contains($prodName, 'tea');

            // Check if pastry/dessert
            $isPastry = str_contains($catName, 'pastry')
                || str_contains($catName, 'bakery')
                || str_contains($catName, 'roti')
                || str_contains($catName, 'cake')
                || str_contains($catName, 'dessert');

            if ($isBeverage) {
                $barItems[] = $item;
            } elseif ($isPastry) {
                $pastryItems[] = $item;
            } else {
                $kitchenItems[] = $item;
            }
        }

        // 3a. Kitchen Order Ticket (Makanan / Main Course)
        if (!empty($kitchenItems)) {
            $kitchenPrinter = $isSinglePrinter
                ? $cashierPrinter
                : ($printers->first(fn(PosPrinter $p) => $p->isKitchen()) ?? $cashierPrinter);

            $isFallback = $kitchenPrinter->id === $cashierPrinter->id && !$cashierPrinter->isKitchen() && !$isSinglePrinter;
            $kBytes = $this->formatter->formatKitchenOrder($order, $kitchenPrinter, $kitchenItems, 'DAPUR (KITCHEN)');

            $jobs[] = [
                'job_id' => (string) Str::uuid(),
                'document_type' => PosPrinterRoutingRule::DOC_KITCHEN_TICKET,
                'station' => PosPrinter::STATION_KITCHEN,
                'printer' => $kitchenPrinter,
                'printer_id' => $kitchenPrinter->id,
                'printer_name' => $kitchenPrinter->name,
                'connection_type' => $kitchenPrinter->connection_type,
                'interface_address' => $kitchenPrinter->interface_address,
                'port' => (int) $kitchenPrinter->port,
                'paper_width' => $kitchenPrinter->paper_width,
                'is_fallback' => $isFallback,
                'fallback_from' => $isFallback ? 'Printer Dapur' : null,
                'items_count' => count($kitchenItems),
                'raw_bytes' => $kBytes,
                'base64_payload' => base64_encode($kBytes),
            ];
        }

        // 3b. Bar Order Ticket (Minuman & Kopi)
        if (!empty($barItems)) {
            $barPrinter = $isSinglePrinter
                ? $cashierPrinter
                : ($printers->first(fn(PosPrinter $p) => $p->isBar()) ?? $printers->first(fn(PosPrinter $p) => $p->isKitchen()) ?? $cashierPrinter);

            $isFallback = $barPrinter->id === $cashierPrinter->id && !$cashierPrinter->isBar() && !$isSinglePrinter;
            $bBytes = $this->formatter->formatKitchenOrder($order, $barPrinter, $barItems, 'BARISTA / BAR');

            $jobs[] = [
                'job_id' => (string) Str::uuid(),
                'document_type' => PosPrinterRoutingRule::DOC_BAR_TICKET,
                'station' => PosPrinter::STATION_BAR,
                'printer' => $barPrinter,
                'printer_id' => $barPrinter->id,
                'printer_name' => $barPrinter->name,
                'connection_type' => $barPrinter->connection_type,
                'interface_address' => $barPrinter->interface_address,
                'port' => (int) $barPrinter->port,
                'paper_width' => $barPrinter->paper_width,
                'is_fallback' => $isFallback,
                'fallback_from' => $isFallback ? 'Printer Bar' : null,
                'items_count' => count($barItems),
                'raw_bytes' => $bBytes,
                'base64_payload' => base64_encode($bBytes),
            ];
        }

        // 3c. Pastry / Bakery Ticket
        if (!empty($pastryItems)) {
            $pastryPrinter = $isSinglePrinter
                ? $cashierPrinter
                : ($printers->first(fn(PosPrinter $p) => $p->station === PosPrinter::STATION_PASTRY || $p->supportsUsage(PosPrinter::USAGE_PASTRY_ORDER))
                   ?? $printers->first(fn(PosPrinter $p) => $p->isKitchen())
                   ?? $cashierPrinter);

            $isFallback = $pastryPrinter->id === $cashierPrinter->id && $pastryPrinter->station !== PosPrinter::STATION_PASTRY && !$isSinglePrinter;
            $pBytes = $this->formatter->formatKitchenOrder($order, $pastryPrinter, $pastryItems, 'PASTRY / BAKERY');

            $jobs[] = [
                'job_id' => (string) Str::uuid(),
                'document_type' => PosPrinterRoutingRule::DOC_PASTRY_TICKET,
                'station' => PosPrinter::STATION_PASTRY,
                'printer' => $pastryPrinter,
                'printer_id' => $pastryPrinter->id,
                'printer_name' => $pastryPrinter->name,
                'connection_type' => $pastryPrinter->connection_type,
                'interface_address' => $pastryPrinter->interface_address,
                'port' => (int) $pastryPrinter->port,
                'paper_width' => $pastryPrinter->paper_width,
                'is_fallback' => $isFallback,
                'fallback_from' => $isFallback ? 'Printer Pastry' : null,
                'items_count' => count($pastryItems),
                'raw_bytes' => $pBytes,
                'base64_payload' => base64_encode($pBytes),
            ];
        }

        // 4. Packing Slip (for Delivery & Takeaway)
        $isDeliveryOrTakeaway = in_array(strtolower((string) $order->order_type), ['delivery', 'takeaway', 'online', 'grabfood', 'gofood', 'shopeefood'], true)
            || !empty($order->delivery_address);

        if ($isDeliveryOrTakeaway && ($options['print_packing'] ?? false)) {
            $packingPrinter = $isSinglePrinter
                ? $cashierPrinter
                : ($printers->first(fn(PosPrinter $p) => $p->station === PosPrinter::STATION_PACKING || $p->supportsUsage(PosPrinter::USAGE_PACKING_SLIP)) ?? $cashierPrinter);

            $packBytes = $this->formatter->formatPackingSlip($order, $packingPrinter, $order->items->all());

            $jobs[] = [
                'job_id' => (string) Str::uuid(),
                'document_type' => PosPrinterRoutingRule::DOC_PACKING_SLIP,
                'station' => PosPrinter::STATION_PACKING,
                'printer' => $packingPrinter,
                'printer_id' => $packingPrinter->id,
                'printer_name' => $packingPrinter->name,
                'connection_type' => $packingPrinter->connection_type,
                'interface_address' => $packingPrinter->interface_address,
                'port' => (int) $packingPrinter->port,
                'paper_width' => $packingPrinter->paper_width,
                'is_fallback' => false,
                'fallback_from' => null,
                'items_count' => $order->items->count(),
                'raw_bytes' => $packBytes,
                'base64_payload' => base64_encode($packBytes),
            ];
        }

        return $jobs;
    }

    /**
     * Dispatch planned print jobs to physical printers with automatic fallback handling.
     *
     * @return array<int, array{
     *     job_id: string,
     *     document_type: string,
     *     station: string,
     *     printer_name: string,
     *     success: bool,
     *     message: string,
     *     fallback_applied: bool,
     *     base64_payload: string
     * }>
     */
    public function dispatchOrderPrintJobs(PosOrder $order, ?string $locationId = null, array $options = []): array
    {
        $plannedJobs = $this->planPrintJobs($order, $locationId, $options);
        $results = [];

        foreach ($plannedJobs as $job) {
            $printer = $job['printer'];
            $rawBytes = $job['raw_bytes'];
            $docType = $job['document_type'];
            $station = $job['station'];

            $connector = match ($printer->connection_type) {
                PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
                PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
                PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
                default => new AgentPayloadConnector(),
            };

            $fallbackApplied = false;
            $sendResult = null;

            try {
                $sendResult = $connector->send($printer, $rawBytes);
            } catch (Throwable $e) {
                Log::warning("[UniversalPrinter] Failed sending to {$printer->name}: " . $e->getMessage());
                $sendResult = ['success' => false, 'message' => $e->getMessage()];
            }

            // Fallback trigger if physical send failed and printer has a fallback
            if (!$sendResult['success'] && $printer->connection_type !== PosPrinter::TYPE_AGENT) {
                $fallbackPrinter = $printer->resolveEffectiveFallback();
                if ($fallbackPrinter && $fallbackPrinter->id !== $printer->id) {
                    try {
                        $fallbackConnector = match ($fallbackPrinter->connection_type) {
                            PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
                            PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
                            PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
                            default => new AgentPayloadConnector(),
                        };
                        $fallbackResult = $fallbackConnector->send($fallbackPrinter, $rawBytes);
                        if ($fallbackResult['success']) {
                            $sendResult = [
                                'success' => true,
                                'message' => "Dialihkan ke {$fallbackPrinter->name} karena {$printer->name} offline.",
                            ];
                            $fallbackApplied = true;
                        }
                    } catch (Throwable $fbEx) {
                        Log::error("[UniversalPrinter] Fallback print also failed: " . $fbEx->getMessage());
                    }
                }
            }

            // Persist job tracking record in database
            try {
                PosPrintJob::create([
                    'business_id' => $order->business_id,
                    'location_id' => $order->location_id,
                    'printer_id' => $printer->id,
                    'document_type' => $docType,
                    'document_id' => $order->id,
                    'payload_raw' => base64_encode($rawBytes),
                    'status' => $sendResult['success'] ? 'printed' : 'failed',
                    'error_message' => $sendResult['success'] ? null : $sendResult['message'],
                    'printed_at' => $sendResult['success'] ? now() : null,
                    'created_by' => $options['actor_id'] ?? auth()->id(),
                ]);
            } catch (Throwable $dbEx) {
                Log::warning("[UniversalPrinter] Failed creating PosPrintJob: " . $dbEx->getMessage());
            }

            $results[] = [
                'job_id' => $job['job_id'],
                'document_type' => $docType,
                'station' => $station,
                'printer_name' => $printer->name,
                'success' => $sendResult['success'],
                'message' => $sendResult['message'],
                'fallback_applied' => $fallbackApplied,
                'base64_payload' => $job['base64_payload'],
            ];
        }

        return $results;
    }

    /**
     * Create a transient default virtual printer for businesses with zero printers configured.
     */
    protected function createVirtualDefaultPrinter(string $businessId): PosPrinter
    {
        return new PosPrinter([
            'id' => (string) Str::uuid(),
            'business_id' => $businessId,
            'name' => 'Printer Default Virtual (Agent)',
            'alias' => 'Virtual Kasir',
            'station' => PosPrinter::STATION_CASHIER,
            'connection_type' => PosPrinter::TYPE_AGENT,
            'interface_address' => '127.0.0.1',
            'port' => 9100,
            'paper_width' => '80mm',
            'is_active' => true,
            'is_default' => true,
            'capabilities' => [
                PosPrinter::CAP_PRINT_TEXT,
                PosPrinter::CAP_QR_CODE,
                PosPrinter::CAP_CUT,
                PosPrinter::CAP_CASH_DRAWER,
            ],
            'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
        ]);
    }
}
