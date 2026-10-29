<?php

declare(strict_types=1);

use App\Models\Business;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $demoSlugs = [
            'dapur-sedap-rasa',
            'mahkota-roti-pastry',
            'restoran-nusantara-rasa',
            'ghost-box-culinary',
            'garasi-motor-jaya',
            'serba-jaya-retail-mart',
            'salju-nusantara-frozen-food',
            'lentera-seni-kreasi',
            'jati-makmur-woodworking',
            'apotek-sehat-medika',
            'cahaya-offset-printing',
            'presisi-baja-plastik',
            'berkah-prasmanan-catering',
            'pixelbyte-digital-studio',
            'citra-busana-konveksi',
            'kilau-bersih-laundry',
            'karya-megah-bangunan',
            'gentlemen-cuts-barbershop',
            'mahligai-indah-wedding',
            'kopi-senja-utama',
        ];

        Business::whereIn('slug', $demoSlugs)->update([
            'is_demo' => true,
        ]);

        Business::where('slug', 'meta-reviewer-store')->update([
            'is_test' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op or reset to false
    }
};

