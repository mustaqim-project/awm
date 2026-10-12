<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Community\ContentModerationService;
use App\Http\Controllers\Controller;
use App\Models\CommunityWordFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminWordFilterController extends Controller
{
    public function __construct(
        private readonly ContentModerationService $moderationService = new ContentModerationService
    ) {}

    /**
     * Display a listing of word filter rules with category statistics.
     */
    public function index(Request $request): View
    {
        $category = $request->query('category', 'all');
        $search = $request->query('q');

        $query = CommunityWordFilter::with('creator');

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('word', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $filters = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        $stats = [
            'total' => CommunityWordFilter::count(),
            'judol' => CommunityWordFilter::where('category', CommunityWordFilter::CATEGORY_JUDOL)->count(),
            'scam' => CommunityWordFilter::where('category', CommunityWordFilter::CATEGORY_SCAM)->count(),
            'kata_kasar' => CommunityWordFilter::where('category', CommunityWordFilter::CATEGORY_KATA_KASAR)->count(),
            'pornografi' => CommunityWordFilter::where('category', CommunityWordFilter::CATEGORY_PORNOGRAFI)->count(),
            'custom' => CommunityWordFilter::where('category', CommunityWordFilter::CATEGORY_CUSTOM)->count(),
            'active' => CommunityWordFilter::where('is_active', true)->count(),
        ];

        return view('admin.word_filters.index', compact('filters', 'stats', 'category', 'search'));
    }

    /**
     * Store a newly created word filter rule.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:128'],
            'category' => ['required', 'in:judol,scam,kata_kasar,pornografi,custom'],
            'action' => ['required', 'in:block,censor'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $cleanWord = strtolower(trim($validated['word']));

        $existing = CommunityWordFilter::where('word', $cleanWord)
            ->where('category', $validated['category'])
            ->first();

        if ($existing) {
            $existing->update([
                'action' => $validated['action'],
                'is_active' => true,
                'description' => $validated['description'] ?? $existing->description,
            ]);
            $this->moderationService->clearCache();
            return back()->with('success', "Kata terlarang '{$cleanWord}' sudah ada dan statusnya telah diperbarui.");
        }

        CommunityWordFilter::create([
            'word' => $cleanWord,
            'category' => $validated['category'],
            'action' => $validated['action'],
            'is_active' => true,
            'description' => $validated['description'] ?? null,
            'created_by_admin_id' => auth('admin')->id(),
        ]);

        $this->moderationService->clearCache();

        return back()->with('success', "Kata terlarang '{$cleanWord}' berhasil ditambahkan ke filter moderasi.");
    }

    /**
     * Bulk store multiple word filters from comma-separated or newline text.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'words' => ['required', 'string'],
            'category' => ['required', 'in:judol,scam,kata_kasar,pornografi,custom'],
            'action' => ['required', 'in:block,censor'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        // Split by newlines, carriage returns, or commas
        $rawWords = preg_split('/[\r\n,]+/', $validated['words']);
        $count = 0;
        $adminId = auth('admin')->id();

        foreach ($rawWords as $raw) {
            $clean = strtolower(trim($raw));
            if ($clean === '' || strlen($clean) < 2) {
                continue;
            }

            $filter = CommunityWordFilter::firstOrNew([
                'word' => $clean,
                'category' => $validated['category'],
            ]);

            $filter->action = $validated['action'];
            $filter->is_active = true;
            if (! empty($validated['description'])) {
                $filter->description = $validated['description'];
            }
            if (! $filter->exists) {
                $filter->created_by_admin_id = $adminId;
            }
            $filter->save();
            $count++;
        }

        $this->moderationService->clearCache();

        return back()->with('success', "Berhasil menambahkan {$count} kata ke filter moderasi.");
    }

    /**
     * Update the specified word filter rule.
     */
    public function update(Request $request, CommunityWordFilter $wordFilter): RedirectResponse
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:128'],
            'category' => ['required', 'in:judol,scam,kata_kasar,pornografi,custom'],
            'action' => ['required', 'in:block,censor'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $wordFilter->update([
            'word' => strtolower(trim($validated['word'])),
            'category' => $validated['category'],
            'action' => $validated['action'],
            'is_active' => $request->boolean('is_active', true),
            'description' => $validated['description'] ?? null,
        ]);

        $this->moderationService->clearCache();

        return back()->with('success', 'Aturan filter kata berhasil diperbarui.');
    }

    /**
     * Toggle the active status of a word filter rule.
     */
    public function toggle(CommunityWordFilter $wordFilter): JsonResponse|RedirectResponse
    {
        $wordFilter->update([
            'is_active' => ! $wordFilter->is_active,
        ]);

        $this->moderationService->clearCache();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $wordFilter->is_active,
                'message' => 'Status filter berhasil diubah.',
            ]);
        }

        return back()->with('success', "Status filter '{$wordFilter->word}' berhasil diubah.");
    }

    /**
     * Remove the specified word filter rule.
     */
    public function destroy(CommunityWordFilter $wordFilter): RedirectResponse
    {
        $word = $wordFilter->word;
        $wordFilter->delete();
        $this->moderationService->clearCache();

        return back()->with('success', "Kata '{$word}' berhasil dihapus dari filter moderasi.");
    }

    /**
     * Restore or seed default Indonesian moderation words.
     */
    public function seedDefaults(): RedirectResponse
    {
        $defaultWords = [
            ['word' => 'slot', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'slot gacor', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'gacor', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'maxwin', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'zeus slot', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'pragmatic', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'judi online', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'judol', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'togel', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'transfer dulu', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'dana kaget palsu', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'pinjol ilegal', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'investasi bodong', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'anjing', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'babi', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'bangsat', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'kontol', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'memek', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'bajingan', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'goblok', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'tolol', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'bokep', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'porn', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'bugil', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'vcs', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'open bo', 'category' => 'pornografi', 'action' => 'block'],
        ];

        $inserted = 0;
        foreach ($defaultWords as $item) {
            $filter = CommunityWordFilter::firstOrCreate(
                ['word' => $item['word'], 'category' => $item['category']],
                ['action' => $item['action'], 'is_active' => true, 'description' => 'Kata standar sistem Cooca']
            );
            if ($filter->wasRecentlyCreated) {
                $inserted++;
            }
        }

        $this->moderationService->clearCache();

        return back()->with('success', "Memuat ulang kata bawaan: {$inserted} kata baru ditambahkan.");
    }
}
