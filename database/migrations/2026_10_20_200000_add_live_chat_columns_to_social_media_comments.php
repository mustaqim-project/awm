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
        Schema::table('social_media_comments', function (Blueprint $table): void {
            if (! Schema::hasColumn('social_media_comments', 'from_email')) {
                $table->string('from_email', 255)->nullable()->after('from_name');
            }
            if (! Schema::hasColumn('social_media_comments', 'from_phone')) {
                $table->string('from_phone', 50)->nullable()->after('from_email');
            }
            if (! Schema::hasColumn('social_media_comments', 'session_token')) {
                $table->string('session_token', 100)->nullable()->index()->after('from_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_comments', function (Blueprint $table): void {
            if (Schema::hasColumn('social_media_comments', 'session_token')) {
                $table->dropColumn('session_token');
            }
            if (Schema::hasColumn('social_media_comments', 'from_phone')) {
                $table->dropColumn('from_phone');
            }
            if (Schema::hasColumn('social_media_comments', 'from_email')) {
                $table->dropColumn('from_email');
            }
        });
    }
};
