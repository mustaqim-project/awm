<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

final class ProfileController extends Controller
{
    /**
     * Get authenticated user profile + businesses.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $businesses = $user->businesses()
            ->with(['subscription'])
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'logo_url' => $b->logo_url,
                'currency' => $b->currency ?? 'IDR',
                'subscription_plan' => $b->subscription?->plan,
                'subscription_status' => $b->subscription?->status,
            ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'avatar_url' => $user->avatar_url ?? null,
            ],
            'businesses' => $businesses,
        ], Response::HTTP_OK);
    }

    /**
     * Update profile info.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'avatar_url' => $user->avatar_url ?? null,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check((string) $request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => 'Kata sandi lama tidak sesuai.',
                'errors' => ['current_password' => ['Kata sandi lama tidak sesuai.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update(['password' => Hash::make((string) $request->input('password'))]);
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Kata sandi berhasil diubah.',
        ], Response::HTTP_OK);
    }

    /**
     * Get business profile.
     */
    public function businessProfile(Request $request, Business $business): JsonResponse
    {
        $user = $request->user();

        // Ensure user belongs to business
        if (!$user->businesses()->where('businesses.id', $business->id)->exists()) {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'business' => $business->append(['logo_url', 'currency_symbol']),
        ], Response::HTTP_OK);
    }

    /**
     * Update business profile.
     */
    public function updateBusinessProfile(Request $request, Business $business): JsonResponse
    {
        $user = $request->user();

        if (!$user->businesses()->where('businesses.id', $business->id)->exists()) {
            return response()->json(['message' => 'Akses ditolak.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'in:IDR,USD,EUR,SGD,MYR,JPY'],
            'pos_receipt_footer_note' => ['nullable', 'string', 'max:300'],
        ]);

        $business->update($validated);

        return response()->json([
            'message' => 'Profil bisnis berhasil diperbarui.',
            'business' => $business->fresh()->append(['logo_url', 'currency_symbol']),
        ], Response::HTTP_OK);
    }

    /**
     * Logout current device token.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'message' => 'Berhasil keluar.',
        ], Response::HTTP_OK);
    }

    /**
     * Logout from all devices.
     */
    public function logoutAllDevices(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Berhasil keluar dari semua perangkat.',
        ], Response::HTTP_OK);
    }

    /**
     * Complete owner & business setup onboarding profile.
     * POST /api/v1/profile/complete
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'business_scale' => ['nullable', 'string', 'max:50'],
            'template_code' => ['nullable', 'string', 'max:50'],
        ]);

        // 1. Update user
        $userUpdates = ['phone' => $validated['phone']];
        if (! empty($validated['name'])) {
            $userUpdates['name'] = $validated['name'];
        }
        $user->update($userUpdates);

        // 2. Resolve or create active business
        $business = $user->activeBusiness ?: $user->businesses()->first();
        if (! $business) {
            $business = Business::create([
                'name' => $validated['business_name'],
                'currency' => 'IDR',
                'business_scale' => $validated['business_scale'] ?? 'micro',
            ]);

            \App\Models\BusinessMembership::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'role' => 'owner',
            ]);

            $user->update(['active_business_id' => $business->id]);
        } else {
            $business->update([
                'name' => $validated['business_name'],
                'business_scale' => $validated['business_scale'] ?? $business->business_scale,
            ]);
        }

        // Apply template if requested
        if (! empty($validated['template_code'])) {
            try {
                $templateService = app(\App\Domain\Business\BusinessTypeTemplateService::class);
                $templateService->applyTemplateByCode($business, $validated['template_code']);
            } catch (\Throwable) {}
        }

        // 3. Update or create primary location
        $location = $business->locations()->where('is_primary', true)->first()
            ?? $business->locations()->first();

        $locData = [
            'name' => $location?->name ?? 'Outlet Utama',
            'type' => 'outlet',
            'is_primary' => true,
            'is_active' => true,
            'address' => $validated['address'] ?? $location?->address,
            'city' => $validated['city'] ?? $location?->city,
            'province' => $validated['province'] ?? $location?->province,
            'district' => $validated['district'] ?? $location?->district,
            'village' => $validated['village'] ?? $location?->village,
            'postal_code' => $validated['postal_code'] ?? $location?->postal_code,
            'latitude' => $validated['latitude'] ?? $location?->latitude,
            'longitude' => $validated['longitude'] ?? $location?->longitude,
        ];

        if ($location) {
            $location->update($locData);
        } else {
            $locData['business_id'] = $business->id;
            $locData['code'] = 'OUT-01';
            $location = \App\Models\Location::create($locData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil bisnis berhasil dilengkapi.',
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
            'location' => $location,
        ], Response::HTTP_OK);
    }
}
