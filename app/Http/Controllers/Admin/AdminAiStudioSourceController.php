<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourceCredential;
use App\Models\AiStudio\InspirationSourcePolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AdminAiStudioSourceController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan middleware RBAC Superadmin.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.ai_studio.sources', only: [
                'index',
                'storeSource',
                'updateSource',
                'saveCredentials',
                'updatePolicy',
                'toggleSource',
            ]),
        ];
    }

    /**
     * Tampilkan daftar konfigurasi sumber inspirasi & kebijakan lisensi.
     */
    public function index(Request $request): View|JsonResponse
    {
        $sources = InspirationSource::query()
            ->with(['credentials', 'policy'])
            ->withCount('items')
            ->orderBy('name')
            ->get();

        // AC-009: Masking ketat kredensial agar rahasia tidak pernah bocor ke antarmuka atau klien
        $sourcesData = $sources->map(function (InspirationSource $source) {
            $hasCredentials = $source->credentials->where('status', 'active')->isNotEmpty();

            return [
                'id' => $source->id,
                'code' => $source->code,
                'name' => $source->name,
                'driver_class' => $source->driver_class,
                'is_active' => $source->is_active,
                'fetch_frequency_minutes' => $source->fetch_frequency_minutes,
                'rate_limit_per_minute' => $source->rate_limit_per_minute,
                'supports_image' => $source->supports_image,
                'supports_video' => $source->supports_video,
                'items_count' => $source->items_count,
                'last_synced_at' => $source->last_synced_at?->toIso8601String(),
                'has_credentials' => $hasCredentials,
                'policy' => $source->policy ? [
                    'allow_commercial_use' => $source->policy->allow_commercial_use,
                    'require_attribution' => $source->policy->require_attribution,
                    'retention_days' => $source->policy->retention_days,
                    'disclaimer_template' => $source->policy->disclaimer_template,
                ] : null,
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $sourcesData,
            ]);
        }

        return view('admin.ai-studio.sources', compact('sources', 'sourcesData'));
    }

    /**
     * Simpan sumber data inspirasi baru.
     */
    public function storeSource(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:inspiration_sources,code'],
            'name' => ['required', 'string', 'max:100'],
            'driver_class' => ['required', 'string', 'max:255'],
            'fetch_frequency_minutes' => ['nullable', 'integer', 'min:5', 'max:10080'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:600'],
            'supports_image' => ['nullable', 'boolean'],
            'supports_video' => ['nullable', 'boolean'],
        ]);

        $source = InspirationSource::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'driver_class' => $validated['driver_class'],
            'fetch_frequency_minutes' => $validated['fetch_frequency_minutes'] ?? 60,
            'rate_limit_per_minute' => $validated['rate_limit_per_minute'] ?? 30,
            'supports_image' => (bool) ($validated['supports_image'] ?? true),
            'supports_video' => (bool) ($validated['supports_video'] ?? false),
            'is_active' => true,
        ]);

        // Buat default policy legal (Strict Commercial Attribution Safe)
        InspirationSourcePolicy::create([
            'source_id' => $source->id,
            'allow_commercial_use' => true,
            'require_attribution' => true,
            'retention_days' => 90,
            'disclaimer_template' => 'Gambar ini disediakan oleh ' . $source->name . ' untuk referensi visual legal.',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sumber inspirasi berhasil ditambahkan.',
                'source_id' => $source->id,
            ], 201);
        }

        return redirect()->route('admin.ai-studio.sources.index')
            ->with('success', 'Sumber inspirasi berhasil ditambahkan.');
    }

    /**
     * Perbarui sumber inspirasi.
     */
    public function updateSource(Request $request, string $sourceId): RedirectResponse|JsonResponse
    {
        /** @var InspirationSource $source */
        $source = InspirationSource::findOrFail($sourceId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'driver_class' => ['required', 'string', 'max:255'],
            'fetch_frequency_minutes' => ['nullable', 'integer', 'min:5', 'max:10080'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:600'],
            'supports_image' => ['nullable', 'boolean'],
            'supports_video' => ['nullable', 'boolean'],
        ]);

        $source->update([
            'name' => $validated['name'],
            'driver_class' => $validated['driver_class'],
            'fetch_frequency_minutes' => $validated['fetch_frequency_minutes'] ?? $source->fetch_frequency_minutes,
            'rate_limit_per_minute' => $validated['rate_limit_per_minute'] ?? $source->rate_limit_per_minute,
            'supports_image' => (bool) ($validated['supports_image'] ?? $source->supports_image),
            'supports_video' => (bool) ($validated['supports_video'] ?? $source->supports_video),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sumber inspirasi berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.ai-studio.sources.index')
            ->with('success', 'Sumber inspirasi berhasil diperbarui.');
    }

    /**
     * Simpan Kredensial API Provider Secara Terenkripsi (AC-009).
     */
    public function saveCredentials(Request $request, string $sourceId): JsonResponse
    {
        /** @var InspirationSource $source */
        $source = InspirationSource::findOrFail($sourceId);

        $validated = $request->validate([
            'credential_type' => ['required', 'string', 'in:api_key,oauth2,bearer_token'],
            'api_key' => ['required', 'string', 'min:8', 'max:1000'],
        ]);

        InspirationSourceCredential::updateOrCreate(
            ['source_id' => $source->id],
            [
                'credential_type' => $validated['credential_type'],
                'credentials_encrypted' => [
                    'api_key' => $validated['api_key'],
                ],
                'status' => 'active',
                'created_by' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Kredensial API berhasil dienkripsi dan disimpan dengan aman.',
            'has_credentials' => true,
        ]);
    }

    /**
     * Perbarui Kebijakan Hak Cipta & Lisensi Komersial Sumber.
     */
    public function updatePolicy(Request $request, string $sourceId): JsonResponse
    {
        /** @var InspirationSource $source */
        $source = InspirationSource::findOrFail($sourceId);

        $validated = $request->validate([
            'allow_commercial_use' => ['required', 'boolean'],
            'require_attribution' => ['required', 'boolean'],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'disclaimer_template' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        InspirationSourcePolicy::updateOrCreate(
            ['source_id' => $source->id],
            [
                'allow_commercial_use' => $validated['allow_commercial_use'],
                'require_attribution' => $validated['require_attribution'],
                'retention_days' => $validated['retention_days'] ?? 90,
                'disclaimer_template' => $validated['disclaimer_template'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Kebijakan hak cipta dan lisensi berhasil diperbarui.',
        ]);
    }

    /**
     * Toggle Aktif / Non-aktif Sumber.
     */
    public function toggleSource(Request $request, string $sourceId): JsonResponse
    {
        /** @var InspirationSource $source */
        $source = InspirationSource::findOrFail($sourceId);

        $source->update([
            'is_active' => !$source->is_active,
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $source->is_active,
        ]);
    }
}
