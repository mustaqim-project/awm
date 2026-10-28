<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Inspiration\CommercialUseNotAllowedException;
use App\Domain\Ai\Inspiration\RightsPolicyService;
use App\Domain\Ai\Studio\Security\SsrfProtectionService;
use App\Models\AiStudio\BusinessInspirationProject;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourcePolicy;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class RightsPolicyGateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private InspirationSource $source;
    private InspirationCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->user = User::create([
            'name' => 'Owner Merchant',
            'email' => 'merchant@cooca.id',
            'password' => Hash::make('secret123'),
        ]);

        $this->business = Business::create([
            'name' => 'Toko Herbal Alami',
            'ai_credits_balance' => 100,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->source = InspirationSource::create([
            'code' => 'restricted_source',
            'name' => 'Sumber dengan Lisensi Terbatas',
            'driver_class' => 'App\\Domain\\Ai\\Inspiration\\Drivers\\RestrictedDriver',
            'is_active' => true,
        ]);

        $this->category = InspirationCategory::create([
            'name' => 'Kategori Uji',
            'slug' => 'kategori-uji',
            'industry' => 'general',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    /**
     * AC-002: Item yang memiliki lisensi non-komersial (NC) wajib diblokir oleh RightsPolicyService.
     */
    public function test_blocked_license_cannot_be_used_commercially(): void
    {
        // 1. Kebijakan sumber melarang penggunaan komersial
        InspirationSourcePolicy::create([
            'source_id' => $this->source->id,
            'allow_commercial_use' => false, // DILARANG KOMERSIAL!
            'require_attribution' => true,
            'retention_days' => 30,
        ]);

        $restrictedItem = InspirationItem::create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'external_id' => 'item_nc_999',
            'title' => 'Foto Pribadi Non Komersial',
            'license_type' => 'CC-BY-NC', // Non-commercial explicitly
            'is_commercial_allowed' => false,
            'status' => 'active',
        ]);

        $policyService = new RightsPolicyService;

        // Harus melempar CommercialUseNotAllowedException
        $this->expectException(CommercialUseNotAllowedException::class);
        $policyService->assertCanUseCommercially($restrictedItem);
    }

    /**
     * AC-002: Upaya kompilasi prompt dengan item non-komersial wajib ditolak.
     */
    public function test_compile_prompt_with_non_commercial_item_is_blocked(): void
    {
        InspirationSourcePolicy::create([
            'source_id' => $this->source->id,
            'allow_commercial_use' => false,
            'require_attribution' => true,
        ]);

        $restrictedItem = InspirationItem::create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'external_id' => 'item_nc_456',
            'title' => 'Gambar Terlarang',
            'license_type' => 'Editorial Only / No Commercial',
            'is_commercial_allowed' => false,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('ai-studio.prompt.compile'), [
                'name' => 'Proyek Uji Lisensi',
                'target_channel' => 'instagram',
                'aspect_ratio' => '1:1',
                'style_preset' => 'clean_commercial',
                'inspiration_item_id' => $restrictedItem->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error_code', 'COMMERCIAL_USE_BLOCKED');
    }

    /**
     * AC-002: Upaya pemesanan render dengan referensi non-komersial otomatis diblokir.
     */
    public function test_dispatch_generation_with_non_commercial_reference_is_blocked(): void
    {
        InspirationSourcePolicy::create([
            'source_id' => $this->source->id,
            'allow_commercial_use' => false,
            'require_attribution' => true,
        ]);

        $restrictedItem = InspirationItem::create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'external_id' => 'item_nc_123',
            'title' => 'Gambar Terlarang Render',
            'license_type' => 'CC-BY-NC-ND',
            'is_commercial_allowed' => false,
            'status' => 'active',
        ]);

        $project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Proyek Uji Render Terlarang',
            'target_platform' => 'instagram',
            'inspiration_item_id' => $restrictedItem->id,
            'status' => 'draft',
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('ai-studio.generate'), [
                'project_id' => $project->id,
                'provider' => 'ideogram',
                'model_name' => 'ideogram-v2',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error_code', 'COMMERCIAL_USE_BLOCKED');
    }

    /**
     * TASK-30: Uji komprehensif Proteksi SSRF terhadap IP privat dan metadata endpoint.
     */
    public function test_ssrf_protection_service_blocks_internal_and_private_addresses(): void
    {
        $ssrf = new SsrfProtectionService;

        // 1. Localhost dan Loopback
        $this->assertFalse($ssrf->isSafe('http://localhost/test.jpg'));
        $this->assertFalse($ssrf->isSafe('http://127.0.0.1/admin.png'));
        $this->assertFalse($ssrf->isSafe('http://127.0.0.2:8080/image.png'));

        // 2. Cloud Metadata Endpoints (AWS & GCP)
        $this->assertFalse($ssrf->isSafe('http://169.254.169.254/latest/meta-data/'));
        $this->assertFalse($ssrf->isSafe('http://metadata.google.internal/computeMetadata/v1/'));
        $this->assertFalse($ssrf->isSafe('http://instance-data/'));

        // 3. Private Subnets RFC1918
        $this->assertFalse($ssrf->isSafe('http://10.0.0.1/internal.jpg'));
        $this->assertFalse($ssrf->isSafe('http://192.168.1.1/router.jpg'));
        $this->assertFalse($ssrf->isSafe('http://172.16.0.5/server.png'));
        $this->assertFalse($ssrf->isSafe('http://172.31.255.255/intranet.png'));

        // 4. Non-HTTP Schemes
        $this->assertFalse($ssrf->isSafe('file:///etc/passwd'));
        $this->assertFalse($ssrf->isSafe('ftp://evil.com/image.png'));
        $this->assertFalse($ssrf->isSafe('gopher://evil.com/'));

        // 5. Port yang tidak lazim
        $this->assertFalse($ssrf->isSafe('http://public-domain.com:22/image.png'));
        $this->assertFalse($ssrf->isSafe('http://public-domain.com:3306/image.png'));

        // 6. Valid Public Domain (Harus lolos)
        $this->assertTrue($ssrf->isSafe('https://images.pexels.com/photos/12345/pexels-photo.jpg'));
    }

    /**
     * TASK-30: Memastikan validateUrl melempar InvalidArgumentException untuk URL terlarang.
     */
    public function test_ssrf_protection_throws_exception_on_violation(): void
    {
        $ssrf = new SsrfProtectionService;

        $this->expectException(InvalidArgumentException::class);
        $ssrf->validateUrl('http://127.0.0.1:80/secret.jpg');
    }
}
