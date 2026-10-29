<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts') && !Schema::hasColumn('posts', 'topic_cluster')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('topic_cluster', 64)->nullable()->after('cluster');
                $table->index('topic_cluster');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'topic_cluster')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropIndex(['topic_cluster']);
                $table->dropColumn('topic_cluster');
            });
        }
    }
};

