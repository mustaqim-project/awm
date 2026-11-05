<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Ai\HelpChatbotService;
use App\Http\Controllers\Controller;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class HelpChatbotWebController extends Controller
{
    public function __construct(
        private readonly HelpChatbotService $chatbotService
    ) {}

    /**
     * Endpoint API untuk memproses pertanyaan bantuan pengguna in-app.
     */
    public function ask(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user() ?? auth()->user();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.sender' => ['required_with:history', 'string', 'in:user,assistant,bot'],
            'history.*.text' => ['required_with:history', 'string', 'max:2000'],
        ]);

        try {
            $result = $this->chatbotService->answer(
                $business,
                $user,
                $validated['message'],
                $validated['history'] ?? []
            );

            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
                'intent' => $result['intent'] ?? 'feature_guide',
                'is_data_query' => $result['is_data_query'] ?? false,
                'permission_granted' => $result['permission_granted'] ?? true,
                'quick_links' => $result['quick_links'] ?? [],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kendala saat memproses pertanyaan: ' . $e->getMessage(),
            ], 500);
        }
    }
}

