<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Batch12BlogDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch12_sample_articles_exist_and_render_without_dashes(): void
    {
        $this->artisan('blog:import-articles')->assertSuccessful();

        // 1. Test Article #639: CoreTax DJP billing deadline
        $post639 = Post::where('slug', 'kendala-kode-billing-coretax-djp-batas-akhir-15-november-2026')->first();
        $this->assertNotNull($post639, 'Post with slug kendala-kode-billing-coretax-djp-batas-akhir-15-november-2026 must exist');
        $this->assertTrue($post639->is_published);

        // Verify zero em dash and zero en dash in DB
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), $post639->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), $post639->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post639->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post639->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post639->meta_description);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post639->meta_description);

        $response639 = $this->get('/blog/kendala-kode-billing-coretax-djp-batas-akhir-15-november-2026');
        $response639->assertOk();
        $response639->assertSee('Kendala Kode Billing Coretax DJP', false);
        $response639->assertSee('https://schema.org', false);
        $response639->assertSee('FAQPage', false);

        // 2. Test Article #658: Cloud ERP modern retail
        $post658 = Post::where('slug', 'transisi-toko-tradisional-menjadi-toko-modern-cloud-erp')->first();
        $this->assertNotNull($post658, 'Post with slug transisi-toko-tradisional-menjadi-toko-modern-cloud-erp must exist');
        $this->assertTrue($post658->is_published);

        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), $post658->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), $post658->title);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x94), (string) $post658->content);
        $this->assertStringNotContainsString(chr(0xE2) . chr(0x80) . chr(0x93), (string) $post658->content);

        $response658 = $this->get('/blog/transisi-toko-tradisional-menjadi-toko-modern-cloud-erp');
        $response658->assertOk();
        $response658->assertSee('Transisi Toko Tradisional Menjadi Toko Modern Cloud ERP', false);
        $response658->assertSee('https://schema.org', false);
        $response658->assertSee('FAQPage', false);
    }
}
