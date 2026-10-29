<?php

declare(strict_types=1);

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
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'seo_title')) {
                $table->string('seo_title', 255)->nullable()->after('meta_title');
            }
            if (! Schema::hasColumn('posts', 'topic_cluster')) {
                $table->string('topic_cluster', 64)->nullable()->after('cluster');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn(['seo_title', 'topic_cluster']);
        });
    }
};

