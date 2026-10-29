<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingCaseStudiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_studies_page_renders_illustration_scenario_and_disclaimer(): void
    {
        $response = $this->get('/resources/case-studies');

        $response->assertStatus(200);

        // Heading must state "Contoh Skenario" instead of unverified claims
        $response->assertSee('Contoh Skenario: Bagaimana UMKM Menutup Kebocoran Kasir &amp;', false);

        // Badge must show "Contoh Skenario"
        $response->assertSee('Contoh Skenario');

        // Statutory disclaimer must be present
        $response->assertSee('Angka merupakan ilustrasi berdasarkan skenario umum dan bukan jaminan hasil.');

        // Unverified claims must NOT be present
        $response->assertDontSee('Sukses Terverifikasi');
        $response->assertDontSee('Audit Operasional Terverifikasi');
    }

    public function test_case_studies_supports_verified_customer_structure(): void
    {
        config([
            'marketing.case_studies.items' => [
                [
                    'id' => 1,
                    'status' => 'verified',
                    'business_name' => 'Kedai Kopi Nyata',
                    'branches' => '2 Gerai',
                    'city' => 'Yogyakarta',
                    'industry' => 'fnb',
                    'industry_label' => 'F&B / Kedai Kopi',
                    'stat_badge' => 'Hemat 15% Bahan',
                    'title' => 'Kedai Kopi Nyata: Rekonsiliasi Kasir Instan',
                    'challenge' => 'Tantangan stok manual.',
                    'solution' => 'Solusi sistem ERP.',
                    'solution_route' => 'public.solutions.fnb',
                    'solution_label' => 'Solusi F&B',
                    'verified_data' => [
                        'real_photo_url' => 'https://example.com/photo.jpg',
                        'owner_name' => 'Budi Santoso',
                        'owner_quote' => 'COOCA membantu bisnis kami tertata rapi sejak hari pertama.',
                        'published_date' => '2026-05-01',
                        'permission_granted' => true,
                    ],
                    'details' => [
                        'title' => 'Kedai Kopi Nyata (Yogyakarta)',
                        'description' => 'Kisah nyata terverifikasi implementasi COOCA.',
                        'steps' => [
                            [
                                'step' => 'Langkah 1: Setup',
                                'desc' => 'Setup katalog dan resep.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $response = $this->get('/resources/case-studies');

        $response->assertStatus(200);
        $response->assertSee('Pelanggan Terverifikasi');
        $response->assertSee('COOCA membantu bisnis kami tertata rapi sejak hari pertama.');
        $response->assertSee('Budi Santoso, Yogyakarta');
    }
}

