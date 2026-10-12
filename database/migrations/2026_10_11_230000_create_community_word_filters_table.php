<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('community_word_filters', function (Blueprint $table): void {
            $table->id();
            $table->string('word', 128)->index();
            $table->enum('category', ['judol', 'scam', 'kata_kasar', 'pornografi', 'custom'])->default('judol')->index();
            $table->enum('action', ['block', 'censor'])->default('block');
            $table->boolean('is_active')->default(true)->index();
            $table->string('description', 255)->nullable();
            $table->foreignUuid('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        // Seed default words for Indonesia content moderation
        $defaultWords = [
            // Kategori: Judol (Judi Online)
            ['word' => 'slot', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'slot gacor', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'gacor', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'maxwin', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'zeus slot', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'kakek zeus', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'pragmatic play', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'pragmatic', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'pgsoft', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'mahjong ways', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'judi online', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'judol', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'situs judi', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'bandar judi', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'togel', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'slot88', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'sbobet', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'casino online', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'taruhan bola', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'bocoran rtp', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'deposit pulsa tanpa potongan', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'freebet', 'category' => 'judol', 'action' => 'block'],
            ['word' => 'jackpot slot', 'category' => 'judol', 'action' => 'block'],

            // Kategori: Scam (Penipuan)
            ['word' => 'transfer dulu', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'dana kaget palsu', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'pinjol ilegal', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'cair tanpa jaminan', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'investasi bodong', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'gandakan uang', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'pesugihan online', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'hadiah tanpa diundi', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'biaya administrasi transfer', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'hack akun', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'giveaway jutaan tanpa syarat', 'category' => 'scam', 'action' => 'block'],
            ['word' => 'klik link wa.me', 'category' => 'scam', 'action' => 'block'],

            // Kategori: Kata Kasar (Profanity / Toxic)
            ['word' => 'anjing', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'babi', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'bangsat', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'kontol', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'memek', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'bajingan', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'goblok', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'tolol', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'ngentot', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'pantek', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'jancuk', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'asu', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'kampang', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'puki', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'pepek', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'itil', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'jembut', 'category' => 'kata_kasar', 'action' => 'block'],
            ['word' => 'titit', 'category' => 'kata_kasar', 'action' => 'block'],

            // Kategori: Pornografi
            ['word' => 'bokep', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'porn', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'porno', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'bugil', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'bokep jepang', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'vcs', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'open bo', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'lendir', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'sex bebas', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'video syur', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'toket', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'coli', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'crot', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'ngocok', 'category' => 'pornografi', 'action' => 'block'],
            ['word' => 'mesum', 'category' => 'pornografi', 'action' => 'block'],
        ];

        $now = now();
        $payload = array_map(function (array $item) use ($now): array {
            return [
                'word' => strtolower(trim($item['word'])),
                'category' => $item['category'],
                'action' => $item['action'],
                'is_active' => true,
                'description' => 'Kata filter bawaan sistem Cooca',
                'created_by_admin_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $defaultWords);

        DB::table('community_word_filters')->insert($payload);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_word_filters');
    }
};
