<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Crm\LoyaltyService;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PosReceiptApiController extends Controller
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService = new LoyaltyService(),
        private readonly WhatsAppGatewayService $whatsappGateway = new WhatsAppGatewayService()
    ) {}

    /**
     * Send or format digital WhatsApp receipt for a POS order.
     * POST /api/v1/pos/orders/{posOrder}/send-whatsapp-receipt
     */
    public function sendWhatsAppReceipt(Request $request, PosOrder|string $posOrder): JsonResponse
    {
        $business = Context::requireBusiness();

        if (is_string($posOrder)) {
            $posOrder = PosOrder::where('business_id', $business->id)->find($posOrder);
            if (! $posOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan di bisnis ini.',
                ], Response::HTTP_NOT_FOUND);
            }
        } elseif ($posOrder->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan di bisnis ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'force' => ['nullable', 'boolean'],
        ]);

        $customPhone = $validated['phone'] ?? null;
        $force = (bool) ($validated['force'] ?? false);

        // Resolve recipient phone number: explicit input -> customer relation -> guest phone
        $targetPhone = $customPhone
            ?? $posOrder->customer?->phone
            ?? $posOrder->customer_phone_guest
            ?? '';

        // Generate official wa.me direct URL with public receipt link & custom outlet template
        $whatsappUrl = $this->loyaltyService->generateWhatsAppReceiptUrl($posOrder, $targetPhone ?: null);

        $sentViaGateway = false;
        if (! empty($targetPhone)) {
            try {
                $sentViaGateway = (bool) $this->whatsappGateway->sendReceipt($posOrder, $targetPhone, $force);
            } catch (Throwable) {
                $sentViaGateway = false;
            }
        }

        $message = $sentViaGateway
            ? "Struk digital berhasil dikirim ke nomor WhatsApp {$targetPhone}."
            : "Tautan struk digital WhatsApp berhasil dibuat.";

        return response()->json([
            'success' => true,
            'sent_via_gateway' => $sentViaGateway,
            'whatsapp_url' => $whatsappUrl,
            'phone' => $targetPhone,
            'message' => $message,
        ], Response::HTTP_OK);
    }
}
