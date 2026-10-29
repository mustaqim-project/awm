<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\SeoTopicClusterCommand;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoPromptNineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed pillars and merge articles
        $this->artisan('seo:merge-articles', ['--apply' => true]);
        $this->artisan('seo:create-hpp-bep-pillars', ['--apply' => true]);
        $this->artisan('seo:topic-clusters', ['--apply' => true]);
    }

    public function test_posts_have_topic_cluster_and_contextual_links(): void
    {
        $posts = Post::published()->take(20)->get();
        $this->assertNotEmpty($posts);

        foreach ($posts as $post) {
            $this->assertNotEmpty($post->topic_cluster, "Post {$post->slug} must have a topic_cluster assigned.");
            $this->assertArrayHasKey($post->topic_cluster, SeoTopicClusterCommand::CLUSTERS);

            $mapping = SeoTopicClusterCommand::CLUSTERS[$post->topic_cluster];

            // Verify content has link to target URL
            $this->assertStringContainsString(
                $mapping['url'],
                $post->content,
                "Post {$post->slug} (cluster: {$post->topic_cluster}) must contain contextual link to {$mapping['url']}"
            );
        }
    }

    public function test_article_page_renders_contextual_cta_banner(): void
    {
        $testSlugs = [
            'cara-menghitung-hpp-kopi' => 'Buka Panduan & Kalkulator HPP',
            'cara-menghitung-hpp' => 'Buka Panduan & Kalkulator HPP',
            'cara-menghitung-bep' => 'Buka Panduan & Kalkulator BEP',
            'cara-membuat-nib' => 'Baca Panduan NIB UMKM',
            'cara-stok-opname' => 'Coba Aplikasi Kasir Warung',
        ];

        foreach ($testSlugs as $slug => $expectedCtaBtn) {
            $post = Post::where('slug', $slug)->first();
            $this->assertNotNull($post, "Post {$slug} must exist in test db");
            $response = $this->get('/blog/' . $slug);
            $response->assertStatus(200);

            $html = $response->getContent();
            $this->assertStringContainsString(
                $expectedCtaBtn,
                $html,
                "Failed on {$slug} (cluster: {$post->topic_cluster}): expected CTA button '{$expectedCtaBtn}'"
            );
        }
    }
}
