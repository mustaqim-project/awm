<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\WhatsApp\AdminWhatsAppService;
use Illuminate\Console\Command;

/**
 * Hapus template pesan Meta WhatsApp selain Bahasa Indonesia dan Bahasa Inggris.
 *
 * Usage:
 *   php artisan whatsapp:clean-non-id-en
 */
class CleanWhatsAppTemplatesCommand extends Command
{
    protected $signature = 'whatsapp:clean-non-id-en';

    protected $description = 'Hapus template WhatsApp selain yang berbahasa Indonesia (id) dan Bahasa Inggris (en)';

    public function handle(AdminWhatsAppService $service): int
    {
        $this->components->info('Memeriksa dan membersihkan template selain Bahasa Indonesia dan Bahasa Inggris...');

        $result = $service->purgeNonIdEnTemplates();

        if ($result['purged_count'] > 0) {
            $this->components->info("Selesai: {$result['purged_count']} template non-ID/EN dihapus ({$result['meta_deleted']} dihapus dari Meta WABA).");
        } else {
            $this->components->info('Seluruh template sudah sesuai standar (hanya Bahasa Indonesia dan Bahasa Inggris).');
        }

        return self::SUCCESS;
    }
}
