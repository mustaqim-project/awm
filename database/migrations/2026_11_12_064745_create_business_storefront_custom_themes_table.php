<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_storefront_custom_themes', function (Blueprint $table) {
            $table->id();
            $table->uuid('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->string('theme_preset', 64)->index(); // misal: bakery_cake, agriculture_farm
            $table->string('file_key', 64)->index(); // hero, navbar, footer, features_banner, product_card, custom_css
            $table->string('file_name', 128)->nullable(); // hero.blade.php, navbar.blade.php, custom.css
            
            // Kode asli bawaan sistem sebagai baseline perbandingan/reset
            $table->longText('baseline_content')->nullable();
            
            // Kode draft editan merchant (langsung aktif di mode preview sandbox)
            $table->longText('draft_content')->nullable();
            
            // Kode terpublikasi resmi untuk production publik (hanya aktif setelah di-ACC admin)
            $table->longText('published_content')->nullable();
            
            // Status alur kerja persetujuan
            // 'default'       : menggunakan kode bawaan resmi, tanpa customisasi (langsung live tanpa acc admin)
            // 'draft'         : sedang diedit merchant, hanya aktif di mode preview
            // 'under_review'  : diajukan merchant ke admin untuk diterapkan ke production
            // 'published'     : telah di-ACC & diterbitkan admin ke live production
            // 'rejected'      : ditolak admin dengan catatan revisi
            $table->string('status', 32)->default('default')->index();
            
            $table->boolean('has_custom_modifications')->default(false)->index();
            $table->text('admin_review_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            
            $table->timestamps();

            $table->unique(['business_id', 'theme_preset', 'file_key'], 'uniq_biz_theme_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_storefront_custom_themes');
    }
};
