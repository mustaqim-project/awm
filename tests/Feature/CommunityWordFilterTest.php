<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Community\ContentModerationService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CommunityPost;
use App\Models\CommunityWordFilter;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommunityWordFilterTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Bisnis Beriman',
            'email' => 'owner@bersih.test',
            'password' => 'password',
        ]);

        $this->business = Business::create(['name' => 'Toko Halal Berkah']);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $this->admin = Admin::create([
            'name' => 'Super Admin Cooca',
            'email' => 'admin@cooca.test',
            'password' => 'password',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Seed default words
        app(\App\Http\Controllers\Admin\AdminWordFilterController::class)->seedDefaults();
    }

    public function test_community_post_blocked_when_containing_judol_word(): void
    {
        $response = $this->actingAs($this->owner)
            ->from(route('community.index'))
            ->post(route('community.store'), [
                'content' => 'Halo kawan, ada link slot gacor hari ini jamin maxwin!',
            ]);

        $response->assertRedirect(route('community.index'));
        $response->assertSessionHas('error');
        $response->assertSessionHasErrors('content');

        $this->assertDatabaseMissing('community_posts', [
            'owner_id' => $this->owner->id,
            'content' => 'Halo kawan, ada link slot gacor hari ini jamin maxwin!',
        ]);
    }

    public function test_community_post_blocked_when_containing_scam_word(): void
    {
        $response = $this->actingAs($this->owner)
            ->from(route('community.index'))
            ->post(route('community.store'), [
                'content' => 'Selamat dapat hadiah undian tapi wajib transfer dulu biaya admin ya.',
            ]);

        $response->assertRedirect(route('community.index'));
        $response->assertSessionHas('error');
        $response->assertSessionHasErrors('content');

        $this->assertDatabaseMissing('community_posts', [
            'owner_id' => $this->owner->id,
        ]);
    }

    public function test_community_comment_blocked_when_containing_kata_kasar(): void
    {
        $post = CommunityPost::create([
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'content' => 'Diskusi pembukuan kas UMKM yang benar.',
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson(route('community.comment', $post), [
                'content' => 'Trik kamu goblok dan anjing banget!',
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('dilarang', $response->json('message'));

        $this->assertDatabaseMissing('community_comments', [
            'post_id' => $post->id,
            'content' => 'Trik kamu goblok dan anjing banget!',
        ]);
    }

    public function test_community_comment_blocked_when_containing_pornografi(): void
    {
        $post = CommunityPost::create([
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'content' => 'Sharing tips seputar stok barang.',
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson(route('community.comment', $post), [
                'content' => 'Yuk mampir nonton bokep dan open bo.',
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('dilarang', $response->json('message'));

        $this->assertDatabaseMissing('community_comments', [
            'post_id' => $post->id,
        ]);
    }

    public function test_clean_content_is_allowed_to_post_and_comment(): void
    {
        // 1. Post clean content
        $postResponse = $this->actingAs($this->owner)
            ->post(route('community.store'), [
                'content' => 'Strategi meningkatkan omset penjualan kasir POS dengan resep HPP yang tepat.',
            ]);

        $postResponse->assertSessionHas('success');
        $post = CommunityPost::where('owner_id', $this->owner->id)->firstOrFail();
        $this->assertSame('Strategi meningkatkan omset penjualan kasir POS dengan resep HPP yang tepat.', $post->content);

        // 2. Comment clean content
        $commentResponse = $this->actingAs($this->owner)
            ->postJson(route('community.comment', $post), [
                'content' => 'Sangat bermanfaat, terima kasih tipsnya pak owner!',
            ]);

        $commentResponse->assertStatus(200);
        $commentResponse->assertJson(['success' => true]);
        $this->assertDatabaseHas('community_comments', [
            'post_id' => $post->id,
            'content' => 'Sangat bermanfaat, terima kasih tipsnya pak owner!',
        ]);
    }

    public function test_admin_can_view_word_filters_index(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.word-filters.index'));

        $response->assertStatus(200);
        $response->assertSee('Filter &amp; Moderasi Kata Komunitas', false);
        $response->assertSee('Judi Online');
        $response->assertSee('Penipuan (Scam)');
        $response->assertSee('Kata Kasar');
        $response->assertSee('Pornografi');
    }

    public function test_admin_can_add_custom_word_filter_and_immediately_blocks_content(): void
    {
        // 1. Admin adds new banned word
        $addResponse = $this->actingAs($this->admin, 'admin')->post(route('admin.word-filters.store'), [
            'word' => 'investasi peternakan fiktif',
            'category' => 'scam',
            'action' => 'block',
            'description' => 'Scam baru berkedok ternak',
        ]);

        $addResponse->assertSessionHas('success');
        $this->assertDatabaseHas('community_word_filters', [
            'word' => 'investasi peternakan fiktif',
            'category' => 'scam',
            'is_active' => true,
        ]);

        // 2. Owner tries to post with that new word
        auth()->guard('admin')->logout();
        Context::setBusiness($this->business);

        $postResponse = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->from(route('community.index'))
            ->post(route('community.store'), [
                'content' => 'Gabung yuk bisnis investasi peternakan fiktif untung 500% sebulan',
            ]);

        $postResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('community_posts', [
            'content' => 'Gabung yuk bisnis investasi peternakan fiktif untung 500% sebulan',
        ]);
    }

    public function test_admin_can_bulk_add_multiple_word_filters(): void
    {
        $bulkResponse = $this->actingAs($this->admin, 'admin')->post(route('admin.word-filters.bulk-store'), [
            'words' => "depo pulsa receh\nbandar togel hongkong, taruhan dadu online",
            'category' => 'judol',
            'action' => 'block',
            'description' => 'Import batch judol',
        ]);

        $bulkResponse->assertSessionHas('success');

        $this->assertDatabaseHas('community_word_filters', ['word' => 'depo pulsa receh']);
        $this->assertDatabaseHas('community_word_filters', ['word' => 'bandar togel hongkong']);
        $this->assertDatabaseHas('community_word_filters', ['word' => 'taruhan dadu online']);
    }

    public function test_admin_can_toggle_and_delete_word_filter(): void
    {
        $filter = CommunityWordFilter::create([
            'word' => 'kata uji coba',
            'category' => 'custom',
            'action' => 'block',
            'is_active' => true,
        ]);

        // Toggle to inactive
        $toggleResponse = $this->actingAs($this->admin, 'admin')->post(route('admin.word-filters.toggle', $filter));
        $toggleResponse->assertSessionHas('success');
        $filter->refresh();
        $this->assertFalse($filter->is_active);

        // Inactive word does NOT block post
        auth()->guard('admin')->logout();
        Context::setBusiness($this->business);

        $postResponse = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->from(route('community.index'))
            ->post(route('community.store'), [
                'content' => 'Ini adalah postingan dengan kata uji coba yang sudah dinonaktifkan filternya.',
            ]);
        $postResponse->assertSessionHas('success');

        // Delete filter
        $deleteResponse = $this->actingAs($this->admin, 'admin')->delete(route('admin.word-filters.destroy', $filter));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('community_word_filters', ['id' => $filter->id]);
    }
}
