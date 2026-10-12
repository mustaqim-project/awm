<?php

declare(strict_types=1);

namespace App\Domain\Community;

use App\Models\CommunityWordFilter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ContentModerationService
{
    public const CACHE_KEY = 'community_active_word_filters';

    /**
     * Get all active filter rules, cached indefinitely until modified.
     *
     * @return Collection<int, CommunityWordFilter>
     */
    public function getActiveFilters(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): Collection {
            return CommunityWordFilter::active()->get();
        });
    }

    /**
     * Clear moderation word cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Inspect text for prohibited words, returning violation details if any.
     *
     * @return array{
     *     is_clean: bool,
     *     has_blocked: bool,
     *     violation: ?array{word: string, category: string, category_label: string, message: string},
     *     censored_content: string
     * }
     */
    public function inspect(string $content): array
    {
        $filters = $this->getActiveFilters();
        if ($filters->isEmpty()) {
            return [
                'is_clean' => true,
                'has_blocked' => false,
                'violation' => null,
                'censored_content' => $content,
            ];
        }

        $normalized = $this->normalizeText($content);
        $censored = $content;
        $violation = null;
        $hasBlocked = false;

        foreach ($filters as $filter) {
            $word = strtolower(trim($filter->word));
            if ($word === '') {
                continue;
            }

            // Test if word exists in text
            if ($this->matchesWord($content, $normalized, $word)) {
                if ($filter->action === CommunityWordFilter::ACTION_BLOCK) {
                    $hasBlocked = true;
                    if ($violation === null) {
                        $violation = [
                            'word' => $filter->word,
                            'category' => $filter->category,
                            'category_label' => $filter->category_label,
                            'message' => "Konten Anda tidak dapat dipublikasikan karena mengandung kata/frasa yang dilarang ({$filter->category_label}): '{$filter->word}'. Harap patuhi pedoman komunitas Cooca.",
                        ];
                    }
                } elseif ($filter->action === CommunityWordFilter::ACTION_CENSOR) {
                    $censored = $this->maskWord($censored, $word);
                }
            }
        }

        return [
            'is_clean' => ! $hasBlocked && $censored === $content,
            'has_blocked' => $hasBlocked,
            'violation' => $violation,
            'censored_content' => $censored,
        ];
    }

    /**
     * Validate content against filter rules.
     * Throws ValidationException if a blocked word is detected.
     * Returns sanitized/censored content otherwise.
     *
     * @throws ValidationException
     */
    public function validateOrFail(string $content): string
    {
        $result = $this->inspect($content);

        if ($result['has_blocked'] && $result['violation'] !== null) {
            throw ValidationException::withMessages([
                'content' => [$result['violation']['message']],
            ]);
        }

        return $result['censored_content'];
    }

    /**
     * Check if a specific target word exists in original or normalized text.
     */
    protected function matchesWord(string $original, string $normalized, string $target): bool
    {
        $targetLower = strtolower($target);
        $escaped = preg_quote($targetLower, '/');

        // 1. Check exact phrase or word boundary in original lowercase
        $originalLower = strtolower($original);
        if (str_contains($targetLower, ' ')) {
            if (str_contains($originalLower, $targetLower)) {
                return true;
            }
        } else {
            if (preg_match('/(?:\b|[^a-zA-Z0-9])' . $escaped . '(?:\b|[^a-zA-Z0-9])/i', " {$originalLower} ")) {
                return true;
            }
        }

        // 2. Check in normalized leetspeak-cleaned string
        if (str_contains($targetLower, ' ')) {
            if (str_contains($normalized, $targetLower)) {
                return true;
            }
        } else {
            if (preg_match('/(?:\b|[^a-zA-Z0-9])' . $escaped . '(?:\b|[^a-zA-Z0-9])/i', " {$normalized} ")) {
                return true;
            }
        }

        // 3. Compact text without spaces/punctuation (catches s.l.o.t, g_a_c_o_r, z-e-u-s)
        $compact = preg_replace('/[^a-z0-9]/', '', $normalized);
        $compactTarget = preg_replace('/[^a-z0-9]/', '', $targetLower);
        if (strlen($compactTarget) >= 4 && str_contains($compact, $compactTarget)) {
            return true;
        }

        return false;
    }

    /**
     * Mask word occurrences with asterisks.
     */
    protected function maskWord(string $text, string $word): string
    {
        $escaped = preg_quote($word, '/');
        return preg_replace_callback('/' . $escaped . '/i', function (array $m): string {
            return str_repeat('*', max(3, strlen($m[0])));
        }, $text) ?? $text;
    }

    /**
     * Normalize leetspeak substitutions and special symbols to plain lower text.
     */
    protected function normalizeText(string $text): string
    {
        $lower = strtolower($text);

        // Leetspeak dictionary
        $replacements = [
            '@' => 'a',
            '4' => 'a',
            '0' => 'o',
            '1' => 'i',
            '!' => 'i',
            '3' => 'e',
            '5' => 's',
            '$' => 's',
            '7' => 't',
            '+' => 't',
        ];

        return strtr($lower, $replacements);
    }
}
