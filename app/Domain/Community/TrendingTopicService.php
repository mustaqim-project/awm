<?php

declare(strict_types=1);

namespace App\Domain\Community;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class TrendingTopicService
{
    /**
     * Stopwords umum bahasa Indonesia yang diabaikan dalam ekstraksi kata tren.
     */
    private const STOPWORDS = [
        'yang', 'untuk', 'pada', 'dengan', 'dari', 'adalah', 'bisa', 'akan', 'atau', 'juga',
        'saya', 'kami', 'anda', 'kita', 'oleh', 'karena', 'dalam', 'agar', 'lebih', 'sudah',
        'belum', 'saat', 'hari', 'ini', 'itu', 'bagi', 'tentang', 'sama', 'banyak', 'buat',
        'bila', 'jadi', 'harus', 'kalau', 'mana', 'pula', 'saja', 'telah', 'mau', 'ke', 'di',
        'ada', 'dan', 'ia', 'dia', 'mereka', 'serta', 'seperti', 'antara', 'tanpa', 'hingga',
        'lalu', 'namun', 'tetapi', 'walau', 'meski', 'hanya', 'sangat', 'lagi', 'semua',
        'para', 'setiap', 'mana', 'siapa', 'apa', 'kapan', 'bagaimana', 'mengapa', 'kenapa',
        'dong', 'sih', 'kok', 'kan', 'deh', 'yuk', 'nih', 'tuh', 'nya', 'pun', 'tersebut',
        'postingan', 'thread', 'komunitas', 'cooca',
    ];

    /**
     * Topik bawaan/kurasi UMKM Indonesia sebagai fallback saat aktivitas 24 jam masih kosong/baru.
     */
    private const FALLBACK_TOPICS = [
        ['topic' => '#TipsBisnis', 'count' => 14, 'category' => 'Strategi Usaha'],
        ['topic' => '#ManajemenPOS', 'count' => 11, 'category' => 'Sistem Kasir'],
        ['topic' => '#ArusKasSehat', 'count' => 9, 'category' => 'Finansial & HPP'],
        ['topic' => '#StrategiMargin', 'count' => 7, 'category' => 'Keuangan Toko'],
        ['topic' => '#RetensiPelanggan', 'count' => 6, 'category' => 'Marketing & Loyalitas'],
    ];

    /**
     * Mengambil 5 kata / topik teratas yang sering diposting dan dikomentari dalam 24 jam terakhir.
     *
     * @return array<int, array{topic: string, count: int, category: string}>
     */
    public function getTopTrendingTopics(int $limit = 5): array
    {
        $since24h = Carbon::now()->subHours(24);

        // 1. Ambil postingan 24 jam terakhir beserta jumlah komentar yang masuk dalam 24 jam
        $recentPosts = CommunityPost::withCount(['comments' => function ($q) use ($since24h): void {
            $q->where('created_at', '>=', $since24h);
        }])
        ->where(function ($q) use ($since24h): void {
            $q->where('created_at', '>=', $since24h)
              ->orWhereHas('comments', function ($cq) use ($since24h): void {
                  $cq->where('created_at', '>=', $since24h);
              });
        })
        ->get();

        // 2. Ambil komentar independen dalam 24 jam terakhir
        $recentComments = CommunityComment::where('created_at', '>=', $since24h)->pluck('content');

        // Jika aktivitas dalam 24 jam masih sangat sedikit (< 3), perluas rentang ke 7 hari
        if (($recentPosts->count() + $recentComments->count()) < 3) {
            $since7d = Carbon::now()->subDays(7);
            $recentPosts = CommunityPost::withCount(['comments' => function ($q) use ($since7d): void {
                $q->where('created_at', '>=', $since7d);
            }])
            ->where(function ($q) use ($since7d): void {
                $q->where('created_at', '>=', $since7d)
                  ->orWhereHas('comments', function ($cq) use ($since7d): void {
                      $cq->where('created_at', '>=', $since7d);
                  });
            })
            ->get();

            $recentComments = CommunityComment::where('created_at', '>=', $since7d)->pluck('content');
        }

        // Jika basis data masih baru dan belum ada postingan/komentar sama sekali, gunakan fallback kurasi
        if ($recentPosts->isEmpty() && $recentComments->isEmpty()) {
            return array_slice(self::FALLBACK_TOPICS, 0, $limit);
        }

        $frequencies = [];

        // A. Analisis Konten Postingan (memperhitungkan postingan + lonjakan komentar)
        foreach ($recentPosts as $post) {
            $engagementWeight = 1 + (int) ($post->comments_count ?? 0);
            $content = $post->content;

            // Ekstraksi Hashtag dari postingan
            if (preg_match_all('/#([a-zA-Z0-9_]+)/u', $content, $hashtags)) {
                foreach ($hashtags[0] as $tag) {
                    $normTag = '#' . ucfirst(ltrim($tag, '#'));
                    $frequencies[$normTag] = ($frequencies[$normTag] ?? 0) + ($engagementWeight * 2);
                }
            }

            // Ekstraksi Kata Kunci Bermakna dari postingan
            $cleanText = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($content));
            $words = preg_split('/\s+/u', (string) $cleanText, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            $seenInPost = [];
            foreach ($words as $word) {
                if (mb_strlen($word) < 4 || in_array($word, self::STOPWORDS, true)) {
                    continue;
                }

                $formattedWord = ucfirst($word);
                if (! isset($seenInPost[$formattedWord])) {
                    $frequencies[$formattedWord] = ($frequencies[$formattedWord] ?? 0) + $engagementWeight;
                    $seenInPost[$formattedWord] = true;
                }
            }
        }

        // B. Analisis Konten Komentar (ekstraksi kata & hashtag yang ditulis di dalam komentar)
        foreach ($recentComments as $commentContent) {
            if (preg_match_all('/#([a-zA-Z0-9_]+)/u', $commentContent, $commentHashtags)) {
                foreach ($commentHashtags[0] as $tag) {
                    $normTag = '#' . ucfirst(ltrim($tag, '#'));
                    $frequencies[$normTag] = ($frequencies[$normTag] ?? 0) + 2;
                }
            }

            $cleanText = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($commentContent));
            $words = preg_split('/\s+/u', (string) $cleanText, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            $seenInComment = [];
            foreach ($words as $word) {
                if (mb_strlen($word) < 4 || in_array($word, self::STOPWORDS, true)) {
                    continue;
                }

                $formattedWord = ucfirst($word);
                if (! isset($seenInComment[$formattedWord])) {
                    $frequencies[$formattedWord] = ($frequencies[$formattedWord] ?? 0) + 1;
                    $seenInComment[$formattedWord] = true;
                }
            }
        }

        if (empty($frequencies)) {
            return array_slice(self::FALLBACK_TOPICS, 0, $limit);
        }

        // Urutkan frekuensi terbanyak
        arsort($frequencies);

        $results = [];
        $rank = 0;
        foreach ($frequencies as $topic => $count) {
            $results[] = [
                'topic' => (string) $topic,
                'count' => (int) $count,
                'category' => str_starts_with((string) $topic, '#') ? 'Tren Populer' : 'Kata Kunci Terhangat',
            ];
            $rank++;
            if ($rank >= $limit) {
                break;
            }
        }

        // Jika hasil ekstraksi kurang dari $limit, lengkapi dengan fallback topics unik
        if (count($results) < $limit) {
            $existingTopics = array_column($results, 'topic');
            foreach (self::FALLBACK_TOPICS as $fb) {
                if (! in_array($fb['topic'], $existingTopics, true)) {
                    $results[] = $fb;
                    if (count($results) >= $limit) {
                        break;
                    }
                }
            }
        }

        return array_slice($results, 0, $limit);
    }
}
