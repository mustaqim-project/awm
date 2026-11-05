<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Ai\HelpChatbotService;
use App\Http\Controllers\Controller;
use App\Models\AiChatbotConversation;
use App\Models\AiChatbotMessage;
use App\Support\Context;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

final class HelpChatbotWebController extends Controller
{
    public function __construct(
        private readonly HelpChatbotService $chatbotService
    ) {}

    /**
     * Endpoint API untuk memproses pertanyaan bantuan pengguna in-app (Help Center / Business Assistant).
     */
    public function ask(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user() ?? auth()->user();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'in:help_center,business_assistant,auto'],
            'conversation_id' => ['nullable', 'string', 'uuid'],
            'force_queue' => ['nullable', 'boolean'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.sender' => ['required_with:history', 'string', 'in:user,assistant,bot'],
            'history.*.text' => ['required_with:history', 'string', 'max:2000'],
        ]);

        try {
            $result = $this->chatbotService->answer(
                $business,
                $user,
                $validated['message'],
                $validated['history'] ?? [],
                $validated['type'] ?? null,
                $validated['conversation_id'] ?? null,
                (bool) ($validated['force_queue'] ?? false)
            );

            return response()->json([
                'success' => true,
                'status' => $result['status'] ?? 'completed',
                'message_id' => $result['message_id'] ?? null,
                'conversation_id' => $result['conversation_id'] ?? null,
                'reply' => $result['reply'],
                'intent' => $result['intent'] ?? 'feature_guide',
                'is_data_query' => (bool) ($result['is_data_query'] ?? false),
                'permission_granted' => (bool) ($result['permission_granted'] ?? true),
                'quick_links' => $result['quick_links'] ?? [],
                'ai_model' => $result['ai_model'] ?? 'cooca-ai-engine',
                'provider' => $result['provider'] ?? 'cooca',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kendala saat memproses pertanyaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Polling endpoint untuk mengecek status pemrosesan pesan antrean (Queue Status Polling).
     */
    public function checkJobStatus(string $messageId): JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var AiChatbotMessage|null $message */
        $message = AiChatbotMessage::where('id', $messageId)
            ->whereHas('conversation', function ($q) use ($business) {
                $q->where('business_id', $business->id);
            })
            ->first();

        if (! $message) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan antrean tidak ditemukan.',
            ], 404);
        }

        $meta = is_array($message->metadata) ? $message->metadata : [];

        return response()->json([
            'success' => true,
            'status' => $message->status, // 'pending', 'processing', 'completed', 'failed'
            'reply' => $message->content,
            'is_completed' => $message->status === AiChatbotMessage::STATUS_COMPLETED,
            'is_failed' => $message->status === AiChatbotMessage::STATUS_FAILED,
            'intent' => $message->intent,
            'is_data_query' => (bool) $message->is_data_query,
            'permission_granted' => (bool) $message->permission_granted,
            'quick_links' => $meta['quick_links'] ?? [],
            'ai_model' => $meta['ai_model'] ?? null,
        ]);
    }

    /**
     * Dapatkan daftar riwayat percakapan pengguna pada bisnis aktif (JSON API & View).
     */
    public function getConversations(Request $request): JsonResponse|View
    {
        $business = Context::requireBusiness();
        $user = Context::user() ?? auth()->user();

        $type = $request->query('type');
        $search = trim((string) $request->query('search', ''));

        $query = AiChatbotConversation::where('business_id', $business->id)
            ->withCount('messages');

        if ($user && ! Context::isOwner()) {
            $query->where('user_id', $user->id);
        }

        if ($type && in_array($type, ['help_center', 'business_assistant'], true)) {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        $conversations = $query->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        // Jika request via API / AJAX (misal dari widget floating chat)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'conversations' => $conversations->items(),
                'meta' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'total' => $conversations->total(),
                ],
            ]);
        }

        // Stats ringkasan untuk Bento cards
        $statsQuery = AiChatbotConversation::where('business_id', $business->id);
        if ($user && ! Context::isOwner()) {
            $statsQuery->where('user_id', $user->id);
        }

        $totalCount = (clone $statsQuery)->count();
        $helpCenterCount = (clone $statsQuery)->where('type', 'help_center')->count();
        $businessAssistantCount = (clone $statsQuery)->where('type', 'business_assistant')->count();

        return view('app.help_chatbot.conversations', [
            'conversations' => $conversations,
            'activeType' => $type,
            'search' => $search,
            'stats' => [
                'total' => $totalCount,
                'help_center' => $helpCenterCount,
                'business_assistant' => $businessAssistantCount,
            ],
        ]);
    }

    /**
     * Hapus sesi percakapan chatbot.
     */
    public function deleteConversation(Request $request, string $conversationId): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user() ?? auth()->user();

        /** @var AiChatbotConversation|null $conversation */
        $conversation = AiChatbotConversation::where('id', $conversationId)
            ->where('business_id', $business->id)
            ->first();

        if (! $conversation) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi percakapan tidak ditemukan.',
                ], 404);
            }

            return back()->with('error', 'Sesi percakapan tidak ditemukan.');
        }

        // Isolasi keamanan: jika bukan owner, pastikan milik user yang bersangkutan
        if (! Context::isOwner() && $user && $conversation->user_id !== $user->id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses untuk menghapus percakapan ditolak.',
                ], 403);
            }

            return back()->with('error', 'Akses untuk menghapus percakapan ditolak.');
        }

        $conversation->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Percakapan berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Percakapan berhasil dihapus.');
    }

    /**
     * Dapatkan riwayat pesan dalam satu percakapan.
     */
    public function getHistory(string $conversationId): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user() ?? auth()->user();

        /** @var AiChatbotConversation|null $conversation */
        $conversation = AiChatbotConversation::where('id', $conversationId)
            ->where('business_id', $business->id)
            ->first();

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi percakapan tidak ditemukan.',
            ], 404);
        }

        // Isolasi keamanan: jika bukan owner, pastikan milik user yang bersangkutan
        if (! Context::isOwner() && $user && $conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ke riwayat percakapan ditolak.',
            ], 403);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get(['id', 'sender', 'content', 'intent', 'is_data_query', 'permission_granted', 'status', 'metadata', 'created_at']);

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'type' => $conversation->type,
                'title' => $conversation->title,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Mulai sesi percakapan baru.
     */
    public function newConversation(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user() ?? auth()->user();

        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:help_center,business_assistant'],
            'title' => ['nullable', 'string', 'max:100'],
        ]);

        $type = $validated['type'] ?? 'help_center';
        $title = $validated['title'] ?? ($type === 'business_assistant' ? 'Analisis Bisnis Baru' : 'Panduan Fitur Baru');

        $conversation = AiChatbotConversation::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'type' => $type,
            'title' => $title,
        ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'type' => $conversation->type,
            'title' => $conversation->title,
        ]);
    }
}
