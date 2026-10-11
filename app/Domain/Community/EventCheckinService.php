<?php

declare(strict_types=1);

namespace App\Domain\Community;

use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use App\Models\User;
use Carbon\Carbon;

final class EventCheckinService
{
    /**
     * Check-in peserta via koordinat GPS lokasi.
     *
     * @return array{success: bool, message: string, distance_meters?: int, registration?: CommunityEventRegistration}
     */
    public function checkinViaLocation(CommunityEvent $event, User $user, float $latitude, float $longitude): array
    {
        if ($event->latitude === null || $event->longitude === null) {
            return [
                'success' => false,
                'message' => 'Event ini tidak memiliki titik koordinat GPS lokasi yang dikonfigurasi.',
            ];
        }

        $registration = CommunityEventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        if ($registration === null) {
            return [
                'success' => false,
                'message' => 'Anda belum terdaftar pada event ini.',
            ];
        }

        if ($registration->status === CommunityEventRegistration::STATUS_CANCELLED) {
            return [
                'success' => false,
                'message' => 'Pendaftaran Anda pada event ini telah dibatalkan.',
            ];
        }

        if ($registration->isCheckedIn()) {
            return [
                'success' => true,
                'message' => 'Anda sudah berhasil check-in pada event ini pada ' . $registration->checked_in_at?->translatedFormat('d M Y, H:i') . ' WIB.',
                'registration' => $registration,
            ];
        }

        // Hitung jarak Haversine (meter)
        $distanceMeters = $this->calculateHaversineDistance(
            $latitude,
            $longitude,
            $event->latitude,
            $event->longitude
        );

        $allowedRadius = (int) ($event->checkin_radius_meters ?: 200);

        if ($distanceMeters > $allowedRadius) {
            return [
                'success' => false,
                'message' => "Posisi Anda berjarak {$distanceMeters} meter dari venue event (Maksimal radius {$allowedRadius} meter). Silakan mendekat ke lokasi acara atau gunakan Barcode tiket.",
                'distance_meters' => $distanceMeters,
            ];
        }

        $registration->markAsAttended(
            CommunityEventRegistration::METHOD_LOCATION,
            $latitude,
            $longitude
        );

        return [
            'success' => true,
            'message' => "Check-in berhasil via lokasi! Anda terdeteksi berjarak {$distanceMeters} meter dari venue.",
            'distance_meters' => $distanceMeters,
            'registration' => $registration,
        ];
    }

    /**
     * Check-in peserta via Barcode / QR Code.
     * Dapat dipanggil:
     * 1. Oleh Panitia/Admin yang memindai ticket_code peserta
     * 2. Oleh Peserta yang memindai event checkin_code di venue
     *
     * @return array{success: bool, message: string, registration?: CommunityEventRegistration}
     */
    public function checkinViaBarcode(CommunityEvent $event, string $code, ?User $currentUser = null): array
    {
        $code = trim($code);

        // Kasus 1: Barcode berupa kode tiket peserta (dipindai oleh admin/panitia atau sistem check-in)
        $registration = CommunityEventRegistration::where('event_id', $event->id)
            ->where(function ($q) use ($code): void {
                $q->where('ticket_code', $code)
                  ->orWhere('ticket_code', strtoupper($code));
            })
            ->first();

        // Kasus 2: Barcode berupa kode QR event venue yang dipindai oleh peserta sendiri
        if ($registration === null && $currentUser !== null) {
            if ($code === $event->checkin_code) {
                $registration = CommunityEventRegistration::where('event_id', $event->id)
                    ->where('user_id', $currentUser->getAuthIdentifier())
                    ->first();
            }
        }

        if ($registration === null) {
            return [
                'success' => false,
                'message' => 'Kode barcode / tiket tidak valid untuk event ini.',
            ];
        }

        if ($registration->status === CommunityEventRegistration::STATUS_CANCELLED) {
            return [
                'success' => false,
                'message' => 'Status pendaftaran peserta ini telah dibatalkan.',
            ];
        }

        if ($registration->isCheckedIn()) {
            return [
                'success' => true,
                'message' => 'Peserta (' . ($registration->user->name ?? 'Peserta') . ') sudah pernah check-in pada ' . $registration->checked_in_at?->translatedFormat('d M Y, H:i') . ' WIB.',
                'registration' => $registration,
            ];
        }

        $registration->markAsAttended(CommunityEventRegistration::METHOD_BARCODE);

        return [
            'success' => true,
            'message' => 'Check-in via Barcode berhasil untuk ' . ($registration->user->name ?? 'Peserta') . ' (' . ($registration->business->name ?? 'Tenant') . ')!',
            'registration' => $registration,
        ];
    }

    /**
     * Check-in manual oleh admin.
     */
    public function checkinManual(CommunityEventRegistration $registration): void
    {
        $registration->markAsAttended(CommunityEventRegistration::METHOD_MANUAL);
    }

    /**
     * Hitung jarak dua titik koordinat bumi dengan rumus Haversine dalam meter.
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }
}
