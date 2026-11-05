<?php

declare(strict_types=1);

namespace App\Domain\Ai;

use App\Domain\Ai\Chatbot\BusinessAssistantAiService;
use App\Domain\Ai\Chatbot\HelpCenterAiService;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Jobs\ProcessAiChatbotMessageJob;
use App\Models\AiChatbotConversation;
use App\Models\AiChatbotMessage;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class HelpChatbotService
 *
 * Master Orchestrator untuk AI Chatbot COOCA:
 * 1. Menjembatani Help Center AI (Panduan Pengguna / How-To) dan Business Assistant AI (Analisis Data Bisnis).
 * 2. Mengelola sesi percakapan (AiChatbotConversation) & riwayat pesan (AiChatbotMessage).
 * 3. Menangani Manajemen Antrean & High Traffic (Queue Management):
 *    - Membatasi concurrent AI requests per bisnis.
 *    - Jika antrean penuh / traffic tinggi, pesan ditampung dalam database queue:
 *      "Saat ini COOCA sedang menerima banyak permintaan. Pesan Anda sedang menunggu antrean. Silakan tunggu, kami akan segera memprosesnya."
 *    - Polling endpoint siap untuk memantau status pending -> processing -> completed.
 * 4. Menegakkan Zero Operational Data Leakage pada Help Center dan Strict RBAC pada Business Assistant.
 */
final class HelpChatbotService
{
    public const MAX_CONCURRENT_PER_BUSINESS = 2;

    public function __construct(
        private readonly AiProviderManager $providerManager = new AiProviderManager(),
        private readonly ?HelpCenterAiService $helpCenterService = null,
        private readonly ?BusinessAssistantAiService $businessAssistantService = null
    ) {}

    /**
     * Memproses pertanyaan chatbot, mengelola percakapan, memeriksa traffic & antrean,
     * serta mendelegasikan ke Help Center AI atau Business Assistant AI.
     *
     * @param array<int, array{sender: string, text: string}> $history
     * @return array<string, mixed>
     */
    public function answer(
        Business $business,
        ?User $user,
        string $query,
        array $history = [],
        ?string $type = null,
        ?string $conversationId = null,
        bool $forceQueue = false
    ): array {
        $cleanQuery = trim($query);
        $lowerQuery = mb_strtolower($cleanQuery);

        // 1. Tentukan Tipe / Mode Chatbot (Help Center vs Business Assistant)
        $effectiveType = $this->resolveChatbotType($lowerQuery, $type);

        // 2. Resolve atau Buat Sesi Percakapan (AiChatbotConversation)
        $conversation = $this->resolveConversation($business, $user, $effectiveType, $conversationId, $cleanQuery);

        // 3. Evaluasi Kapasitas Antrean / High Traffic Management
        $activeProcessingCount = $this->countActiveProcessingMessages($business->id);
        $maxConcurrent = (int) config('services.cooca.ai_max_concurrent', self::MAX_CONCURRENT_PER_BUSINESS);

        if ($forceQueue || $activeProcessingCount >= $maxConcurrent) {
            return $this->enqueueMessage(
                $conversation,
                $business,
                $user,
                $cleanQuery,
                $effectiveType,
                $history
            );
        }

        // 4. Eksekusi Sinkron Langsung (Kapasitas Tersedia)
        return $this->processSynchronously(
            $conversation,
            $business,
            $user,
            $cleanQuery,
            $effectiveType,
            $history
        );
    }

    /**
     * Tentukan tipe chatbot: jika eksplisit gunakan tipe tersebut; jika auto/null deteksi intent.
     */
    private function resolveChatbotType(string $lowerQuery, ?string $type): string
    {
        if ($type !== null && in_array($type, ['business_assistant', 'help_center'], true)) {
            return $type;
        }

        $helpCenter = $this->helpCenterService ?? new HelpCenterAiService($this->providerManager);
        if ($helpCenter->isOperationalBusinessDataQuery($lowerQuery)) {
            return 'business_assistant';
        }

        return 'help_center';
    }

    /**
     * Resolve percakapan aktif atau buat percakapan baru.
     */
    private function resolveConversation(
        Business $business,
        ?User $user,
        string $type,
        ?string $conversationId,
        string $query
    ): AiChatbotConversation {
        if ($conversationId !== null) {
            /** @var AiChatbotConversation|null $found */
            $found = AiChatbotConversation::where('id', $conversationId)
                ->where('business_id', $business->id)
                ->first();

            if ($found) {
                return $found;
            }
        }

        $title = Str::limit($query, 45, '...');

        return AiChatbotConversation::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'type' => $type,
            'title' => $title !== '' ? $title : 'Percakapan Baru',
        ]);
    }

    /**
     * Hitung pesan yang sedang menunggu antrean atau sedang diproses.
     */
    private function countActiveProcessingMessages(string $businessId): int
    {
        return AiChatbotMessage::whereHas('conversation', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })
            ->whereIn('status', [AiChatbotMessage::STATUS_PENDING, AiChatbotMessage::STATUS_PROCESSING])
            ->count();
    }

    /**
     * Masukkan pesan ke antrean background worker saat traffic tinggi (Bagian 4).
     *
     * @param array<int, array{sender: string, text: string}> $history
     * @return array<string, mixed>
     */
    private function enqueueMessage(
        AiChatbotConversation $conversation,
        Business $business,
        ?User $user,
        string $query,
        string $type,
        array $history
    ): array {
        // Catat pesan dari user
        AiChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => AiChatbotMessage::SENDER_USER,
            'content' => $query,
            'status' => AiChatbotMessage::STATUS_COMPLETED,
        ]);

        $queueNotice = "Saat ini COOCA sedang menerima banyak permintaan. Pesan Anda sedang menunggu antrean. Silakan tunggu, kami akan segera memprosesnya.";

        // Catat pesan asisten dengan status PENDING
        /** @var AiChatbotMessage $assistantMsg */
        $assistantMsg = AiChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => AiChatbotMessage::SENDER_ASSISTANT,
            'content' => $queueNotice,
            'intent' => 'high_traffic_queue',
            'is_data_query' => ($type === 'business_assistant'),
            'permission_granted' => true,
            'status' => AiChatbotMessage::STATUS_PENDING,
            'metadata' => [
                'queued_at' => now()->toIso8601String(),
            ],
        ]);

        // Dispatch background queue job
        ProcessAiChatbotMessageJob::dispatch(
            $assistantMsg->id,
            $conversation->id,
            $business->id,
            $user?->id,
            $query,
            $type,
            $history
        );

        Log::channel('daily')->info("[HelpChatbot] Message {$assistantMsg->id} queued for conversation {$conversation->id} under high traffic");

        return [
            'status' => 'queued',
            'message_id' => $assistantMsg->id,
            'conversation_id' => $conversation->id,
            'reply' => $queueNotice,
            'intent' => 'high_traffic_queue',
            'is_data_query' => ($type === 'business_assistant'),
            'permission_granted' => true,
            'quick_links' => [],
        ];
    }

    /**
     * Eksekusi sinkron langsung saat kapasitas sistem tersedia.
     *
     * @param array<int, array{sender: string, text: string}> $history
     * @return array<string, mixed>
     */
    private function processSynchronously(
        AiChatbotConversation $conversation,
        Business $business,
        ?User $user,
        string $query,
        string $type,
        array $history
    ): array {
        // 1. Simpan pesan user
        AiChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => AiChatbotMessage::SENDER_USER,
            'content' => $query,
            'status' => AiChatbotMessage::STATUS_COMPLETED,
        ]);

        // 2. Eksekusi layanan AI spesialis
        try {
            if ($type === 'business_assistant') {
                $service = $this->businessAssistantService ?? new BusinessAssistantAiService($this->providerManager);
                $result = $service->answer($business, $user, $query, $history);
            } else {
                $service = $this->helpCenterService ?? new HelpCenterAiService($this->providerManager);
                $result = $service->answer($business, $user, $query, $history);
            }
        } catch (Throwable $e) {
            Log::channel('daily')->error("[HelpChatbot] Synchronous execution error: {$e->getMessage()}");
            $result = HelpCenterAiService::buildSystemBusyResponse();
        }

        $status = ($result['status'] ?? 'completed') === 'busy'
            ? AiChatbotMessage::STATUS_FAILED
            : AiChatbotMessage::STATUS_COMPLETED;

        // 3. Simpan jawaban asisten
        /** @var AiChatbotMessage $assistantMsg */
        $assistantMsg = AiChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => AiChatbotMessage::SENDER_ASSISTANT,
            'content' => $result['reply'],
            'intent' => $result['intent'] ?? 'general',
            'is_data_query' => (bool) ($result['is_data_query'] ?? false),
            'permission_granted' => (bool) ($result['permission_granted'] ?? true),
            'status' => $status,
            'metadata' => [
                'quick_links' => $result['quick_links'] ?? [],
                'ai_model' => $result['ai_model'] ?? null,
                'provider' => $result['provider'] ?? null,
            ],
        ]);

        return array_merge($result, [
            'status' => $result['status'] ?? 'completed',
            'message_id' => $assistantMsg->id,
            'conversation_id' => $conversation->id,
        ]);
    }
}
