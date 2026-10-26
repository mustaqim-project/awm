<?php

declare(strict_types=1);

use App\Models\ExcelTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        ExcelTemplate::seedDefaultTemplatesIfEmpty();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for template seed migration
    }
};
