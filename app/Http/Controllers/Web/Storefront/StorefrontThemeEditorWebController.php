<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Storefront;

use App\Domain\Storefront\StorefrontCustomThemeService;
use App\Domain\Storefront\StorefrontThemeService;
use App\Http\Controllers\Controller;
use App\Models\BusinessLandingPage;
use App\Models\BusinessStorefrontCustomTheme;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StorefrontThemeEditorWebController extends Controller
{
    public function __construct(
        private readonly StorefrontCustomThemeService $customThemeService,
        private readonly StorefrontThemeService $themeService
    ) {}

    /**
     * Display the Theme Code Editor for Merchant.
     */
    public function edit(Request $request): View
    {
        $business = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(['business_id' => $business->id]);
        $currentPreset = $landingPage->getThemePreset();

        $themeDetails = $this->themeService->getTheme($currentPreset);
        $files = $this->customThemeService->getOrInitializeCustomThemeFiles($business, $currentPreset);

        $activeFileKey = (string) $request->query('file', BusinessStorefrontCustomTheme::FILE_HERO);
        if (! isset(BusinessStorefrontCustomTheme::SUPPORTED_FILES[$activeFileKey])) {
            $activeFileKey = BusinessStorefrontCustomTheme::FILE_HERO;
        }

        $activeFileRecord = $files[$activeFileKey] ?? null;

        $previewUrl = url('/' . ($business->slug ?: $business->id) . '?preview=1');

        return view('app.storefront.theme_editor', compact(
            'business',
            'landingPage',
            'currentPreset',
            'themeDetails',
            'files',
            'activeFileKey',
            'activeFileRecord',
            'previewUrl'
        ));
    }

    /**
     * Save draft code for a theme file.
     */
    public function saveDraft(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(['business_id' => $business->id]);
        $currentPreset = $landingPage->getThemePreset();

        $validated = $request->validate([
            'file_key' => 'required|string|in:' . implode(',', array_keys(BusinessStorefrontCustomTheme::SUPPORTED_FILES)),
            'content'  => 'required|string',
        ]);

        $record = $this->customThemeService->saveDraft(
            $business,
            $currentPreset,
            $validated['file_key'],
            $validated['content']
        );

        return response()->json([
            'success' => true,
            'message' => 'Draft codingan berhasil disimpan. Anda dapat langsung menguji di Live Preview Sandbox.',
            'status' => $record->status,
            'has_modifications' => $record->has_custom_modifications,
            'updated_at' => $record->updated_at->format('H:i:s d M Y'),
        ]);
    }

    /**
     * Submit customized theme code for Admin Approval.
     */
    public function submitReview(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(['business_id' => $business->id]);
        $currentPreset = $landingPage->getThemePreset();

        $submittedCount = $this->customThemeService->submitForAdminReview($business, $currentPreset);

        if ($submittedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file kustomisasi baru yang perlu diajukan review.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengajukan {$submittedCount} berkas kode kustom tema untuk di-review oleh Admin Superadmin. Kode akan aktif di live production setelah di-ACC.",
            'submitted_count' => $submittedCount,
        ]);
    }

    /**
     * Reset a file back to system default.
     */
    public function resetDefault(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $landingPage = BusinessLandingPage::firstOrCreate(['business_id' => $business->id]);
        $currentPreset = $landingPage->getThemePreset();

        $validated = $request->validate([
            'file_key' => 'required|string|in:' . implode(',', array_keys(BusinessStorefrontCustomTheme::SUPPORTED_FILES)),
        ]);

        $record = $this->customThemeService->resetToDefault(
            $business,
            $currentPreset,
            $validated['file_key']
        );

        return response()->json([
            'success' => true,
            'message' => "Berkas {$validated['file_key']} berhasil di-reset ke kode bawaan resmi.",
            'baseline_content' => $record->baseline_content,
        ]);
    }
}

