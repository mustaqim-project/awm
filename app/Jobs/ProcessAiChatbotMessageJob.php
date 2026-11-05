<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Ai\Chatbot\BusinessAssistantAiService;
use App\Domain\Ai\Chatbot\HelpCenterAiService;
use App\Models\AiChatbotMessage;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProcessAiChatbotMessageJob
 *
 * Worker antrean pemrosesan pesan AI Chatbot (Help Center / Business Assistant).
 * Memenuhi kriteria Bagian 4 (High Traffic & Queue Management):
 * - Idempotency / unique execution check
 * - Context binding (User, Business, Membership) untuk evaluasi RBAC di background worker
 * - Exponential backoff retry & timeout
 * - Pembaruan status pesan bertahap: pending -> processing -> completed / failed
 */
final class ProcessAiChatbotMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;
    public int $backoff = 3;

    public function __construct(
        public readonly string $assistantMessageId,
        public readonly string $conversationId,
        public readonly string $businessId,
        public readonly ?string $userId,
        public readonly string $query,
        public readonly string $type = 'help_center',
        public readonly array $history = []
    ) {}

    public function handle(): void
    {
        /** @var AiChatbotMessage|null $message */
        $message = AiChatbotMessage::find($this->assistantMessageId);
        if (! $message) {
            return;
        }

        // Idempotency check: jika sudah selesai, jangan diproses ulang
        if ($message->status === AiChatbotMessage::STATUS_COMPLETED) {
            return;
        }

        $message->update(['status' => AiChatbotMessage::STATUS_PROCESSING]);

        $business = Business::find($this->businessId);
        if (! $business) {
            $message->update([
                'status' => AiChatbotMessage::STATUS_FAILED,
                'content' => HelpCenterAiService::buildSystemBusyResponse()['reply'],
                'metadata' => ['quick_links' => HelpCenterAiService::getSupportContactLinks()],
            ]);
            return;
        }

        $user = $this->userId ? User::find($this->userId) : null;
        $membership = null;
        if ($user) {
            $membership = BusinessMembership::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->first();
            Auth::setUser($user);
        }

        $originalBiz = Context::business();
        $originalMem = Context::membership();

        try {
            Context::setBusiness($business, $membership);

            if ($this->type === 'business_assistant') {
                $service = app(BusinessAssistantAiService::class);
                $result = $service->answer($business, $user, $this->query, $this->history);
            } else {
                $service = app(HelpCenterAiService::class);
                $result = $service->answer($business, $user, $this->query, $this->history);
            }

            $message->update([
                'content' => $result['reply'],
                'intent' => $result['intent'] ?? 'general',
                'is_data_query' => (bool) ($result['is_data_query'] ?? false),
                'permission_granted' => (bool) ($result['permission_granted'] ?? true),
                'status' => AiChatbotMessage::STATUS_COMPLETED,
                'metadata' => [
                    'quick_links' => $result['quick_links'] ?? [],
                    'ai_model' => $result['ai_model'] ?? null,
                    'provider' => $result['provider'] ?? null,
                ],
            ]);
        } catch (Throwable $e) {
            Log::channel('daily')->error("[ProcessAiChatbotMessageJob] Execution failed: {$e->getMessage()}");
            throw $e;
        } finally {
            Context::setBusiness($originalBiz, $originalMem);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('daily')->critical("[ProcessAiChatbotMessageJob] Permanently failed for message {$this->assistantMessageId}: {$exception->getMessage()}");

        /** @var AiChatbotMessage|null $message */
        $message = AiChatbotMessage::find($this->assistantMessageId);
        if ($message && $message->status !== AiChatbotMessage::STATUS_COMPLETED) {
            $busyResponse = HelpCenterAiService::buildSystemBusyResponse();
            $message->update([
                'status' => AiChatbotMessage::STATUS_FAILED,
                'content' => $busyResponse['reply'],
                'metadata' => [
                    'quick_links' => $busyResponse['quick_links'],
                    'error' => 'Job execution reached max attempts',
                ],
            ]);
        }
    }
}

