<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'cluster_id',
        'category_id',
        'cluster', // 'tutorial' (Cluster K) or 'edukasi' (Cluster O)
        'topic_cluster',
        'category',
        'excerpt',
        'content',
        'cover_image',
        'meta_title',
        'seo_title',
        'meta_description',
        'author_name',
        'views_count',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'cluster_id' => 'integer',
        'category_id' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'views_count' => 'integer',
    ];

    /**
     * Relationship to PostCategory.
     */
    public function postCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    /**
     * Relationship to PostCluster.
     */
    public function postCluster(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PostCluster::class, 'cluster_id');
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope for Cluster K (Tutorial cara).
     */
    public function scopeTutorial(Builder $query): Builder
    {
        return $query->where('cluster', 'tutorial');
    }

    /**
     * Scope for Cluster O (Edukasi topikal).
     */
    public function scopeEdukasi(Builder $query): Builder
    {
        return $query->where('cluster', 'edukasi');
    }

    /**
     * Resolve compliant SEO title according to Prompt 3 rules:
     * - If seo_title is filled: use it directly.
     * - If base title <= 52 characters: "{judul} | COOCA"
     * - If base title > 52 characters: "{judul}" without suffix.
     */
    public function getResolvedSeoTitleAttribute(): string
    {
        if (!empty($this->seo_title)) {
            $title = trim((string) $this->seo_title);
            if (mb_strlen($title) <= 52 && !str_ends_with($title, 'COOCA')) {
                return "{$title} | COOCA";
            }
            return $title;
        }

        $base = trim((string) ($this->meta_title ?: $this->title));

        if (mb_strlen($base) <= 52) {
            return "{$base} | COOCA";
        }

        if (mb_strlen($base) <= 60) {
            return $base;
        }

        // Cut at word boundary at max 60 chars to avoid Google truncation
        $sub = mb_substr($base, 0, 60);
        $lastSpace = mb_strrpos($sub, ' ');
        if ($lastSpace !== false && $lastSpace > 35) {
            $sub = mb_substr($sub, 0, $lastSpace);
        }
        return rtrim(trim($sub), ",;:-|–  ");
    }

    /**
     * Resolve compliant meta description according to Prompt 3 rules:
     * - If meta_description is filled and >= 120 chars: clean and end with a dot.
     * - Fallback: extract clean text from excerpt or content (120-155 chars),
     *   cut at word boundary, no ellipsis '...', end with dot.
     */
    public function getResolvedMetaDescriptionAttribute(): string
    {
        if (!empty($this->meta_description)) {
            $desc = trim((string) $this->meta_description);
            $desc = rtrim($desc, ".…");
            if (mb_strlen($desc) >= 120 && mb_strlen($desc) <= 160) {
                return $desc . '.';
            }
        }

        $rawExcerpt = strip_tags((string) $this->excerpt);
        $cleanExcerpt = trim((string) preg_replace('/\s+/', ' ', $rawExcerpt));

        $raw = (mb_strlen($cleanExcerpt) >= 120) ? $cleanExcerpt : strip_tags((string) $this->content);
        $raw = html_entity_decode($raw, ENT_QUOTES, 'UTF-8');
        $raw = (string) preg_replace('/\s+/', ' ', $raw);
        $raw = trim($raw);

        if (mb_strlen($raw) >= 120 && mb_strlen($raw) <= 155) {
            $clean = rtrim($raw, ".…");
            return $clean . '.';
        }

        // Target between 120 and 155 characters at word boundary
        $targetLen = min(155, mb_strlen($raw));
        $sub = mb_substr($raw, 0, $targetLen);
        $lastSpace = mb_strrpos($sub, ' ');
        if ($lastSpace !== false && $lastSpace >= 120) {
            $sub = mb_substr($sub, 0, $lastSpace);
        }
        $clean = rtrim(trim($sub), ",;:-.…");

        return $clean !== '' ? $clean . '.' : '';
    }

    /**
     * Approximate read time in minutes.
     */
    public function getReadTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Generate an optimized SEO slug according to Prompt 4 rules:
     * - Maximum 6 words or 60 characters
     * - Cut at word boundary
     * - Strip leading listicle numbers (e.g. "7 Tanda...", "5 Cara...")
     * - Strip common stop words (ini, yang, untuk, dan, di, ke, dari, bikin, begini, dll.)
     */
    public static function generateCleanSlug(string $title): string
    {
        // 1. Remove leading listicle numbers (e.g. "7 Tanda ", "5 Cara ")
        $clean = preg_replace('/^\d+[\.\-\s]+/u', '', trim($title));

        // 2. Transliterate and normalize to lowercase
        $slug = Str::slug($clean);
        $words = explode('-', $slug);

        // 3. Stop words to filter out per Prompt 4 rules
        $stopWords = [
            'ini', 'yang', 'untuk', 'dan', 'di', 'ke', 'dari', 'bikin', 'begini',
            'tapi', 'atau', 'pada', 'adalah', 'agar', 'bisa', 'kamu', 'anda',
        ];

        $filtered = array_values(array_filter($words, function ($w) use ($stopWords) {
            return !in_array($w, $stopWords, true) && strlen($w) > 1;
        }));

        if (count($filtered) < 2) {
            $filtered = array_slice($words, 0, 6);
        } else {
            $filtered = array_slice($filtered, 0, 6);
        }

        $result = implode('-', $filtered);

        // 4. Ensure max 60 characters and cut at word boundary
        if (strlen($result) > 60) {
            $sub = substr($result, 0, 60);
            $lastDash = strrpos($sub, '-');
            if ($lastDash !== false && $lastDash > 20) {
                $result = substr($sub, 0, $lastDash);
            } else {
                $result = $sub;
            }
        }

        return trim($result, '-');
    }

    /**
     * Ensure slug is generated if empty.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = static::generateCleanSlug($post->title);
            }
        });
    }
}
