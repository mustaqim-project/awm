<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ResetAdminTwoFactorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-2fa 
                            {email? : Alamat email administrator yang akan di-reset}
                            {--all : Reset 2FA untuk semua akun administrator}
                            {--force : Lewati konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset atau nonaktifkan Google Authenticator (2FA) untuk akun administrator (darurat HP hilang/rusak)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info(' COOCA Platform — Emergency Admin 2FA Reset Utility ');
        $this->info('====================================================');

        if ($this->option('all')) {
            return $this->resetAllAdmins();
        }

        $email = $this->argument('email');

        if (! $email) {
            $adminsWith2FA = Admin::whereNotNull('two_factor_confirmed_at')->pluck('email')->all();

            if (empty($adminsWith2FA)) {
                $this->warn('Tidak ada akun administrator yang sedang mengaktifkan 2FA.');
                return self::SUCCESS;
            }

            $email = $this->choice(
                'Pilih email administrator yang ingin di-reset 2FA-nya:',
                $adminsWith2FA
            );
        }

        $email = strtolower(trim((string) $email));
        /** @var Admin|null $admin */
        $admin = Admin::where('email', $email)->first();

        if (! $admin) {
            $this->error("Administrator dengan email '{$email}' tidak ditemukan dalam database.");
            return self::FAILURE;
        }

        if (! $admin->hasTwoFactorEnabled()) {
            $this->warn("Akun administrator '{$email}' saat ini TIDAK memiliki 2FA aktif.");
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Apakah Anda yakin ingin menonaktifkan Google Authenticator untuk '{$email}'?", true)) {
            $this->info('Operasi dibatalkan.');
            return self::SUCCESS;
        }

        $admin->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        try {
            AuditLog::create([
                'id' => (string) Str::uuid(),
                'business_id' => null,
                'user_id' => null,
                'auditable_type' => Admin::class,
                'auditable_id' => $admin->id,
                'action' => 'admin.2fa_emergency_reset',
                'risk_level' => AuditLog::RISK_HIGH,
                'risk_reason' => 'Emergency 2FA Reset via CLI (Lost Device Recovery)',
                'notes' => "2FA Google Authenticator untuk administrator {$email} berhasil dinonaktifkan via artisan CLI command.",
                'ip_address' => '127.0.0.1 (CLI)',
                'user_agent' => 'Console Artisan CLI',
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ignored if audit logs schema differences occur
        }

        $this->info("BERHASIL: 2FA Google Authenticator untuk '{$email}' telah dinonaktifkan.");
        $this->info("Administrator kini dapat login menggunakan Email dan Kata Sandi di https://cooca.id/admin/login.");
        $this->comment("Disarankan untuk mengaktifkan kembali 2FA melalui menu Profil setelah berhasil masuk.");

        return self::SUCCESS;
    }

    private function resetAllAdmins(): int
    {
        $admins = Admin::whereNotNull('two_factor_confirmed_at')->get();

        if ($admins->isEmpty()) {
            $this->warn('Tidak ada akun administrator yang sedang mengaktifkan 2FA.');
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Apakah Anda yakin ingin menonaktifkan 2FA untuk SEMUA ({$admins->count()}) administrator?", false)) {
            $this->info('Operasi dibatalkan.');
            return self::SUCCESS;
        }

        foreach ($admins as $admin) {
            $admin->update([
                'two_factor_secret' => null,
                'two_factor_confirmed_at' => null,
            ]);
            $this->line("• 2FA di-reset untuk: {$admin->email}");
        }

        $this->info("Selesai! Sebanyak {$admins->count()} akun administrator telah di-reset 2FA-nya.");
        return self::SUCCESS;
    }
}
