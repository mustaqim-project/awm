<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Events\LiveChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PublicLiveChatController extends Controller
{
    /**
     * Start a new live chat session from the public website widget.
     * Requires: Nama, Nomor HP, Email, and initial Pertanyaan.
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'min:2', 'max:100'],
            'phone'   => ['required', 'string', 'min:8', 'max:25'],
            'email'   => ['required', 'string', 'email:rfc', 'max:150'],
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ], [
            'name.required'    => 'Nama wajib diisi.',
            'phone.required'   => 'Nomor HP / WhatsApp wajib diisi.',
            'email.required'   => 'Alamat email wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
            'message.required' => 'Pertanyaan atau pesan awal wajib diisi.',
        ]);

        $name = strip_tags(trim($validated['name']));
        $phone = preg_replace('/[^0-9+]/', '', trim($validated['phone']));
        $email = strtolower(trim($validated['email']));
        $message = strip_tags(trim($validated['message']));

        // Generate persistent session token
        $sessionToken = 'live_' . Str::random(32);
        $commentId = (string) Str::uuid();

        $comment = SocialMediaComment::create([
            'business_id'         => null,
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . $commentId,
            'platform_post_id'    => 'live_chat_website',
            'parent_comment_id'   => null,
            'from_id'             => $sessionToken,
            'from_name'           => $name,
            'from_email'          => $email,
            'from_phone'          => $phone,
            'session_token'       => $sessionToken,
            'message'             => $message,
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        // Broadcast to Reverb WebSockets (Visitor Channel + Admin Channel)
        try {
            event(new LiveChatMessageSent($comment));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[LiveChat] Broadcast event error: ' . $e->getMessage());
        }

        // Trigger AI Auto-Reply if eligible (respecting human takeover lifecycle)
        try {
            app(\App\Domain\SocialMedia\AdminInboxAiService::class)->processAutoReplyIfEligible($comment);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[LiveChat] AI Auto-Reply error: ' . $e->getMessage());
        }

        $created = $comment->created_time ?? now();

        return response()->json([
            'success'       => true,
            'session_token' => $sessionToken,
            'visitor'       => [
                'name'  => $name,
                'email' => $email,
                'phone' => $phone,
            ],
            'message'       => [
                'id'            => (string) $comment->id,
                'message'       => $comment->message,
                'from_name'     => $comment->from_name,
                'is_cooca'      => false,
                'is_from_page'  => false,
                'time'          => $created->format('H:i'),
                'time_relative' => 'Baru saja',
                'date'          => $created->format('d M Y'),
                'created_at'    => $created->toIso8601String(),
            ],
        ]);
    }

    /**
     * Send subsequent messages from website visitor in an active session.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => ['required', 'string', 'max:100'],
            'message'       => ['required', 'string', 'min:1', 'max:2000'],
        ], [
            'session_token.required' => 'Sesi chat tidak valid.',
            'message.required'       => 'Pesan tidak boleh kosong.',
        ]);

        $sessionToken = trim($validated['session_token']);
        $messageText = strip_tags(trim($validated['message']));

        // Find existing session info to inherit visitor details
        $firstSessionRecord = SocialMediaComment::where('is_platform', true)
            ->where('platform', 'live_chat')
            ->where(function ($q) use ($sessionToken) {
                $q->where('session_token', $sessionToken)
                  ->orWhere('from_id', $sessionToken);
            })
            ->where('is_from_page', false)
            ->oldest('created_time')
            ->first();

        if (! $firstSessionRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi chat telah kedaluwarsa atau tidak ditemukan. Silakan mulai chat baru.',
            ], 404);
        }

        $commentId = (string) Str::uuid();

        $comment = SocialMediaComment::create([
            'business_id'         => null,
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . $commentId,
            'platform_post_id'    => 'live_chat_website',
            'parent_comment_id'   => $firstSessionRecord->platform_comment_id,
            'from_id'             => $sessionToken,
            'from_name'           => $firstSessionRecord->from_name,
            'from_email'          => $firstSessionRecord->from_email,
            'from_phone'          => $firstSessionRecord->from_phone,
            'session_token'       => $sessionToken,
            'message'             => $messageText,
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        // Broadcast to Reverb WebSockets
        try {
            event(new LiveChatMessageSent($comment));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[LiveChat] Broadcast send error: ' . $e->getMessage());
        }

        // Trigger AI Auto-Reply if eligible (respecting human takeover lifecycle)
        try {
            app(\App\Domain\SocialMedia\AdminInboxAiService::class)->processAutoReplyIfEligible($comment);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[LiveChat] AI Auto-Reply error: ' . $e->getMessage());
        }

        $created = $comment->created_time ?? now();

        return response()->json([
            'success' => true,
            'message' => [
                'id'            => (string) $comment->id,
                'message'       => $comment->message,
                'from_name'     => $comment->from_name,
                'is_cooca'      => false,
                'is_from_page'  => false,
                'time'          => $created->format('H:i'),
                'time_relative' => 'Baru saja',
                'date'          => $created->format('d M Y'),
                'created_at'    => $created->toIso8601String(),
            ],
        ]);
    }

    /**
     * Retrieve conversation history for a given session.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $sessionToken = (string) $request->query('session_token');

        if (empty($sessionToken)) {
            return response()->json(['success' => false, 'messages' => []], 400);
        }

        $messages = SocialMediaComment::where('is_platform', true)
            ->where('platform', 'live_chat')
            ->where(function ($q) use ($sessionToken) {
                $q->where('session_token', $sessionToken)
                  ->orWhere('from_id', $sessionToken);
            })
            ->orderBy('created_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $firstVisitorMsg = $messages->firstWhere('is_from_page', false);

        $formatted = $messages->map(function ($m) {
            $isFromPage = (bool) $m->is_from_page;
            $created = $m->created_time ?? $m->created_at ?? now();

            return [
                'id'            => (string) $m->id,
                'message'       => $m->message,
                'media_url'     => $m->media_url,
                'media_type'    => $m->media_type,
                'from_name'     => $isFromPage ? 'Cooca Support' : ($m->from_name ?: 'Pengunjung'),
                'is_cooca'      => $isFromPage,
                'is_from_page'  => $isFromPage,
                'status'        => $m->status,
                'time'          => $created->format('H:i'),
                'time_relative' => $created->diffForHumans(null, true),
                'date'          => $created->format('d M Y'),
                'created_at'    => $created->toIso8601String(),
            ];
        });

        return response()->json([
            'success'  => true,
            'visitor'  => [
                'name'  => $firstVisitorMsg?->from_name ?? '',
                'email' => $firstVisitorMsg?->from_email ?? '',
                'phone' => $firstVisitorMsg?->from_phone ?? '',
            ],
            'messages' => $formatted,
            'count'    => $formatted->count(),
        ]);
    }

    /**
     * Resilient polling fallback for visitors when WebSocket connection is unavailable or reconnecting.
     */
    public function poll(Request $request): JsonResponse
    {
        $sessionToken = (string) $request->query('session_token');
        $afterId = (string) $request->query('after_id');

        if (empty($sessionToken)) {
            return response()->json(['success' => false, 'messages' => []], 400);
        }

        $query = SocialMediaComment::where('is_platform', true)
            ->where('platform', 'live_chat')
            ->where(function ($q) use ($sessionToken) {
                $q->where('session_token', $sessionToken)
                  ->orWhere('from_id', $sessionToken);
            });

        if (! empty($afterId)) {
            $lastComment = SocialMediaComment::find($afterId);
            if ($lastComment) {
                $query->where('created_time', '>', $lastComment->created_time);
            }
        }

        $messages = $query->orderBy('created_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $formatted = $messages->map(function ($m) {
            $isFromPage = (bool) $m->is_from_page;
            $created = $m->created_time ?? $m->created_at ?? now();

            return [
                'id'            => (string) $m->id,
                'message'       => $m->message,
                'media_url'     => $m->media_url,
                'media_type'    => $m->media_type,
                'from_name'     => $isFromPage ? 'Cooca Support' : ($m->from_name ?: 'Pengunjung'),
                'is_cooca'      => $isFromPage,
                'is_from_page'  => $isFromPage,
                'status'        => $m->status,
                'time'          => $created->format('H:i'),
                'time_relative' => $created->diffForHumans(null, true),
                'date'          => $created->format('d M Y'),
                'created_at'    => $created->toIso8601String(),
            ];
        });

        return response()->json([
            'success'  => true,
            'messages' => $formatted,
            'count'    => $formatted->count(),
        ]);
    }
}
