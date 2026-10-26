<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AccountRecoveryRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class AccountRecoveryApiController extends Controller
{
    /**
     * Submit account access recovery request.
     * POST /api/v1/account-recovery
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_name' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'old_email' => ['nullable', 'string', 'max:255'],
            'old_phone' => ['nullable', 'string', 'max:50'],
            'new_email' => ['nullable', 'string', 'max:255'],
            'new_phone' => ['nullable', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:3000'],
            'reason_description' => ['nullable', 'string', 'max:3000'],
            'issue_type' => ['nullable', 'string', 'in:phone_lost,email_inaccessible,both'],
            'id_card_photo' => ['nullable', 'string'],
        ]);

        $applicantName = $validated['applicant_name'] ?? $validated['owner_name'] ?? 'Pemohon';
        $businessName = $validated['business_name'] ?? 'Bisnis Pemohon';
        $oldEmail = $validated['old_email'] ?? (string) $request->input('identifier', '');
        $newEmail = $validated['new_email'] ?? (string) $request->input('contact_email', $oldEmail);
        $newPhone = $validated['new_phone'] ?? (string) $request->input('contact_phone', '-');

        $user = auth('sanctum')->user();
        if (! $user && ! empty($oldEmail)) {
            $user = User::where('email', $oldEmail)->first();
        }

        // Generate unique ticket number: ARC-YYYYMMDD-XXXX
        $todayStr = Carbon::now()->format('Ymd');
        $randomSuffix = strtoupper(Str::random(4));
        $ticketNumber = "ARC-{$todayStr}-{$randomSuffix}";

        // Handle optional file or base64 uploads
        $idCardPath = null;
        if ($request->hasFile('identity_card')) {
            $idCardPath = $request->file('identity_card')->store("recoveries/{$ticketNumber}", 'local');
        } elseif ($request->hasFile('id_card_file')) {
            $idCardPath = $request->file('id_card_file')->store("recoveries/{$ticketNumber}", 'local');
        } elseif (! empty($validated['id_card_photo']) && str_contains($validated['id_card_photo'], 'base64,')) {
            try {
                $base64Data = explode('base64,', $validated['id_card_photo'])[1] ?? '';
                $decoded = base64_decode($base64Data);
                if ($decoded !== false) {
                    $fileName = "recoveries/{$ticketNumber}/id_card.jpg";
                    Storage::disk('local')->put($fileName, $decoded);
                    $idCardPath = $fileName;
                }
            } catch (\Throwable) {}
        }
        $idCardPath = $idCardPath ?: "recoveries/{$ticketNumber}/pending_id_card.jpg";

        $bizProofPath = null;
        if ($request->hasFile('business_proof')) {
            $bizProofPath = $request->file('business_proof')->store("recoveries/{$ticketNumber}", 'local');
        } elseif ($request->input('business_proof') && str_contains((string) $request->input('business_proof'), 'base64,')) {
            try {
                $base64Data = explode('base64,', (string) $request->input('business_proof'))[1] ?? '';
                $decoded = base64_decode($base64Data);
                if ($decoded !== false) {
                    $fileName = "recoveries/{$ticketNumber}/business_proof.jpg";
                    Storage::disk('local')->put($fileName, $decoded);
                    $bizProofPath = $fileName;
                }
            } catch (\Throwable) {}
        }
        $bizProofPath = $bizProofPath ?: "recoveries/{$ticketNumber}/pending_business_proof.jpg";

        $selfiePath = null;
        if ($request->hasFile('selfie_proof')) {
            $selfiePath = $request->file('selfie_proof')->store("recoveries/{$ticketNumber}", 'local');
        }

        $recoveryRequest = AccountRecoveryRequest::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $user?->id,
            'business_name' => $businessName,
            'applicant_name' => $applicantName,
            'old_email' => $oldEmail ?: 'unspecified@cooca.id',
            'old_phone' => $validated['old_phone'] ?? null,
            'new_email' => $newEmail ?: 'unspecified@cooca.id',
            'new_phone' => $newPhone,
            'issue_type' => $validated['issue_type'] ?? AccountRecoveryRequest::ISSUE_BOTH,
            'reason_description' => $validated['reason'] ?? $validated['reason_description'] ?? 'Permohonan pemulihan akses via mobile app.',
            'identity_card_path' => $idCardPath,
            'business_proof_path' => $bizProofPath,
            'selfie_proof_path' => $selfiePath,
            'status' => AccountRecoveryRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan pemulihan akun berhasil diajukan.',
            'ticket_number' => $ticketNumber,
            'data' => [
                'ticket_number' => $ticketNumber,
                'status' => AccountRecoveryRequest::STATUS_PENDING,
                'applicant_name' => $recoveryRequest->applicant_name,
                'created_at' => $recoveryRequest->created_at?->toIso8601String(),
                'step' => 2,
                'note' => 'Permohonan Anda sedang dalam peninjauan oleh tim kepatuhan COOCA.',
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Check status of account recovery ticket.
     * GET /api/v1/account-recovery/status/{ticket}
     */
    public function status(string $ticket): JsonResponse
    {
        $cleanTicket = trim($ticket);

        $recoveryRequest = AccountRecoveryRequest::where('ticket_number', $cleanTicket)
            ->latest()
            ->first();

        if (! $recoveryRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor tiket permohonan tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        $step = 2;
        $note = 'Berkas permohonan sedang dalam proses verifikasi tim kepatuhan COOCA.';
        if ($recoveryRequest->status === AccountRecoveryRequest::STATUS_APPROVED) {
            $step = 3;
            $note = 'Permohonan pemulihan disetujui. Tautan login baru telah dikirimkan ke email baru Anda.';
        } elseif ($recoveryRequest->status === AccountRecoveryRequest::STATUS_REJECTED) {
            $step = 3;
            $note = $recoveryRequest->admin_notes ?: 'Permohonan belum dapat disetujui karena ketidaksesuaian data identitas.';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ticket_number' => $recoveryRequest->ticket_number,
                'status' => $recoveryRequest->status,
                'applicant_name' => $recoveryRequest->applicant_name,
                'business_name' => $recoveryRequest->business_name,
                'created_at' => $recoveryRequest->created_at?->toIso8601String(),
                'step' => $step,
                'note' => $note,
            ],
        ], Response::HTTP_OK);
    }
}
