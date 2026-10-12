<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentationFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Bisnis Cooca',
            'email' => 'owner@docs.test',
            'password' => 'password',
        ]);

        $this->business = Business::create(['name' => 'Toko Dokumentasi']);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    public function test_authenticated_user_can_access_documentation_page(): void
    {
        $response = $this->actingAs($this->owner)->get(route('docs.index'));

        $response->assertOk();
        $response->assertSee('Buku Panduan &amp; Dokumentasi Cooca', false);
        $response->assertSee('Kasir Modern POS, Pembayaran &amp; Shift', false);
        $response->assertSee('Inventori, Resep &amp; Kalkulasi HPP', false);
        $response->assertSee('Tabel Pintasan Keyboard (Keyboard Shortcuts)', false);
    }

    public function test_unauthenticated_user_cannot_access_documentation(): void
    {
        $response = $this->get(route('docs.index'));

        $response->assertRedirect(route('login'));
    }
}
