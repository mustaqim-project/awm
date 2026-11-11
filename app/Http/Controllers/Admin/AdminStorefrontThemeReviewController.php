<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Storefront\StorefrontCustomThemeService;
use App\Http\Controllers\Controller;
use App\Models\BusinessStorefrontCustomTheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AdminStorefrontThemeReviewController extends Controller
{
    public function __construct(
        private readonly StorefrontCustomThemeService $customThemeService
    ) {}

    /**
     * List all custom theme submissions under review.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'under_review');

        $query = BusinessStorefrontCustomTheme::with(['business', 'reviewer'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('updated_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->paginate(15)->withQueryString();

        $counts = [
            'under_review' => BusinessStorefrontCustomTheme::where('status', BusinessStorefrontCustomTheme::STATUS_UNDER_REVIEW)->count(),
            'published'    => BusinessStorefrontCustomTheme::where('status', BusinessStorefrontCustomTheme::STATUS_PUBLISHED)->count(),
            'rejected'     => BusinessStorefrontCustomTheme::where('status', BusinessStorefrontCustomTheme::STATUS_REJECTED)->count(),
            'all'          => BusinessStorefrontCustomTheme::where('has_custom_modifications', true)->count(),
        ];

        return view('admin.theme_reviews.index', compact('items', 'status', 'counts'));
    }

    /**
     * Show comparison (diff) between baseline and submitted draft.
     */
    public function show(BusinessStorefrontCustomTheme $theme): View
    {
        $theme->load(['business', 'reviewer']);

        $previewUrl = url('/' . ($theme->business->slug ?: $theme->business->id) . '?preview=1');

        return view('admin.theme_reviews.show', compact('theme', 'previewUrl'));
    }

    /**
     * Approve and Publish custom code to live production.
     */
    public function approve(Request $request, BusinessStorefrontCustomTheme $theme): RedirectResponse|JsonResponse
    {
        $theme->published_content = $theme->draft_content;
        $theme->status = BusinessStorefrontCustomTheme::STATUS_PUBLISHED;
        $theme->reviewed_at = now();
        $theme->reviewed_by = Auth::id();
        $theme->admin_review_notes = $request->input('notes', 'Disetujui dan diterapkan ke live production.');
        $theme->save();

        $msg = "Kode kustom [{$theme->theme_preset} / {$theme->file_key}] untuk bisnis {$theme->business->name} BERHASIL DISETUJUI dan langsung aktif di live production!";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.theme-reviews.index')->with('success', $msg);
    }

    /**
     * Reject custom code submission with notes.
     */
    public function reject(Request $request, BusinessStorefrontCustomTheme $theme): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $theme->status = BusinessStorefrontCustomTheme::STATUS_REJECTED;
        $theme->reviewed_at = now();
        $theme->reviewed_by = Auth::id();
        $theme->admin_review_notes = $validated['notes'];
        $theme->save();

        $msg = "Kode kustom [{$theme->theme_preset} / {$theme->file_key}] untuk bisnis {$theme->business->name} DITOLAK dengan catatan perbaikan.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.theme-reviews.index')->with('warning', $msg);
    }
}
