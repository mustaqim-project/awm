<?php

declare(strict_types=1);

namespace App\Domain\Storefront;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\BusinessStorefrontCustomTheme;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

final class StorefrontCustomThemeService
{
    /**
     * Get or initialize all custom theme files for a given business and active theme preset.
     *
     * @return array<string, BusinessStorefrontCustomTheme>
     */
    public function getOrInitializeCustomThemeFiles(Business $business, string $themePreset): array
    {
        $files = BusinessStorefrontCustomTheme::SUPPORTED_FILES;
        $records = [];

        foreach ($files as $fileKey => $meta) {
            $record = BusinessStorefrontCustomTheme::firstOrNew([
                'business_id' => $business->id,
                'theme_preset' => $themePreset,
                'file_key' => $fileKey,
            ]);

            // Baseline code loaded from actual filesystem view file
            $baselineCode = $this->resolveBaselineContent($themePreset, $fileKey);

            if (! $record->exists) {
                $record->file_name = $meta['name'];
                $record->baseline_content = $baselineCode;
                $record->draft_content = $baselineCode;
                $record->published_content = null;
                $record->status = BusinessStorefrontCustomTheme::STATUS_DEFAULT;
                $record->has_custom_modifications = false;
                $record->save();
            } elseif (empty($record->baseline_content)) {
                $record->baseline_content = $baselineCode;
                if (empty($record->draft_content)) {
                    $record->draft_content = $baselineCode;
                }
                $record->save();
            }

            $records[$fileKey] = $record;
        }

        return $records;
    }

    /**
     * Resolve baseline content from filesystem blade file or default CSS.
     */
    public function resolveBaselineContent(string $themePreset, string $fileKey): string
    {
        if ($fileKey === BusinessStorefrontCustomTheme::FILE_CUSTOM_CSS) {
            return "/* Custom CSS Stylesheet for {$themePreset} */\n/* Tambahkan rule CSS kustom toko Anda di sini */\n";
        }

        $path = resource_path("views/public/storefront/themes/{$themePreset}/{$fileKey}.blade.php");
        if (File::exists($path)) {
            return (string) File::get($path);
        }

        // Fallback to universal_storefront or modern_umkm
        $fallbackPath = resource_path("views/public/storefront/themes/universal_storefront/{$fileKey}.blade.php");
        if (File::exists($fallbackPath)) {
            return (string) File::get($fallbackPath);
        }

        $modernPath = resource_path("views/public/storefront/themes/modern_umkm/{$fileKey}.blade.php");
        if (File::exists($modernPath)) {
            return (string) File::get($modernPath);
        }

        return "{{-- {$fileKey} component for {$themePreset} --}}\n";
    }

    /**
     * Save draft modifications for a specific file.
     */
    public function saveDraft(
        Business $business,
        string $themePreset,
        string $fileKey,
        string $content
    ): BusinessStorefrontCustomTheme {
        $meta = BusinessStorefrontCustomTheme::SUPPORTED_FILES[$fileKey] ?? null;
        if (! $meta) {
            throw new \InvalidArgumentException("Invalid file key: {$fileKey}");
        }

        $record = BusinessStorefrontCustomTheme::firstOrNew([
            'business_id' => $business->id,
            'theme_preset' => $themePreset,
            'file_key' => $fileKey,
        ]);

        if (! $record->exists || empty($record->baseline_content)) {
            $record->baseline_content = $this->resolveBaselineContent($themePreset, $fileKey);
            $record->file_name = $meta['name'];
        }

        $isModified = trim($content) !== trim((string) $record->baseline_content);

        $record->draft_content = $content;
        $record->has_custom_modifications = $isModified;

        // If modified, set status to draft (or keep under_review if already submitted)
        if ($isModified) {
            if ($record->status === BusinessStorefrontCustomTheme::STATUS_DEFAULT) {
                $record->status = BusinessStorefrontCustomTheme::STATUS_DRAFT;
            }
        } else {
            $record->status = BusinessStorefrontCustomTheme::STATUS_DEFAULT;
        }

        $record->save();

        return $record;
    }

    /**
     * Submit all modified drafts for Admin Review.
     *
     * @return int Number of files submitted
     */
    public function submitForAdminReview(Business $business, string $themePreset): int
    {
        $customs = BusinessStorefrontCustomTheme::where('business_id', $business->id)
            ->where('theme_preset', $themePreset)
            ->where('has_custom_modifications', true)
            ->whereIn('status', [
                BusinessStorefrontCustomTheme::STATUS_DRAFT,
                BusinessStorefrontCustomTheme::STATUS_REJECTED,
            ])
            ->get();

        $count = 0;
        foreach ($customs as $item) {
            $item->status = BusinessStorefrontCustomTheme::STATUS_UNDER_REVIEW;
            $item->submitted_at = now();
            $item->save();
            $count++;
        }

        return $count;
    }

    /**
     * Reset a file back to system baseline defaults.
     */
    public function resetToDefault(Business $business, string $themePreset, string $fileKey): BusinessStorefrontCustomTheme
    {
        $baseline = $this->resolveBaselineContent($themePreset, $fileKey);

        $record = BusinessStorefrontCustomTheme::updateOrCreate(
            [
                'business_id' => $business->id,
                'theme_preset' => $themePreset,
                'file_key' => $fileKey,
            ],
            [
                'baseline_content' => $baseline,
                'draft_content' => $baseline,
                'published_content' => null,
                'status' => BusinessStorefrontCustomTheme::STATUS_DEFAULT,
                'has_custom_modifications' => false,
                'admin_review_notes' => null,
                'submitted_at' => null,
            ]
        );

        return $record;
    }

    /**
     * Render storefront section:
     * - In preview mode (by merchant): renders draft_content if customized, else standard view.
     * - In live production (public customers): renders published_content if approved by admin, else standard view.
     */
    public function renderSection(
        string $fileKey,
        string $themePreset,
        Business $business,
        array $viewData = [],
        bool $isPreview = false
    ): string {
        $custom = BusinessStorefrontCustomTheme::where('business_id', $business->id)
            ->where('theme_preset', $themePreset)
            ->where('file_key', $fileKey)
            ->first();

        $contentToRender = null;

        if ($isPreview && $custom && $custom->has_custom_modifications && ! empty($custom->draft_content)) {
            // Preview mode: Merchant can see their own custom draft immediately
            $contentToRender = $custom->draft_content;
        } elseif (! $isPreview && $custom && $custom->status === BusinessStorefrontCustomTheme::STATUS_PUBLISHED && ! empty($custom->published_content)) {
            // Production mode: Only render if approved and published by admin
            $contentToRender = $custom->published_content;
        }

        if ($contentToRender !== null) {
            try {
                return Blade::render($contentToRender, $viewData);
            } catch (\Throwable $e) {
                Log::warning("Custom theme render error on [{$themePreset}/{$fileKey}] for business {$business->id}: " . $e->getMessage());
                // Fallback gracefully to default filesystem view
            }
        }

        // Default filesystem Blade view
        $viewName = "public.storefront.themes.{$themePreset}.{$fileKey}";
        if (View::exists($viewName)) {
            return (string) View::make($viewName, $viewData)->render();
        }

        // Universal fallback
        $fallbackView = "public.storefront.themes.universal_storefront.{$fileKey}";
        if (View::exists($fallbackView)) {
            return (string) View::make($fallbackView, $viewData)->render();
        }

        return '';
    }

    /**
     * Get custom CSS if any (draft for preview, published for live).
     */
    public function getCustomCss(Business $business, string $themePreset, bool $isPreview = false): string
    {
        $custom = BusinessStorefrontCustomTheme::where('business_id', $business->id)
            ->where('theme_preset', $themePreset)
            ->where('file_key', BusinessStorefrontCustomTheme::FILE_CUSTOM_CSS)
            ->first();

        if (! $custom) {
            return '';
        }

        if ($isPreview && ! empty($custom->draft_content)) {
            return $custom->draft_content;
        }

        if (! $isPreview && $custom->status === BusinessStorefrontCustomTheme::STATUS_PUBLISHED && ! empty($custom->published_content)) {
            return $custom->published_content;
        }

        return '';
    }
}

