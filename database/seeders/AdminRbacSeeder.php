<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Single Source of Truth for Platform Admin Console Roles & Permissions.
 * Idempotent, non-destructive, and syncs across Admin UI & Controllers.
 */
final class AdminRbacSeeder extends Seeder
{
    /**
     * Matrix of all granular platform admin permissions.
     * Format: 'slug' => ['name', 'category', 'description']
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public function adminPermissionMatrix(): array
    {
        return [
            // ─── 1. Platform Dashboard ────────────────────────────────
            'admin.dashboard.view'                 => ['Lihat Dashboard Admin', 'admin_dashboard', 'Melihat ringkasan metrik platform SaaS, statistik tenant, dan pertumbuhan GMV.'],

            // ─── 2. Tenants / Bisnis ──────────────────────────────────
            'admin.businesses.view'                => ['Lihat Tenant Bisnis', 'admin_tenants', 'Melihat daftar seluruh tenant bisnis UMKM yang terdaftar di platform.'],
            'admin.businesses.toggle_status'       => ['Ubah Status Tenant', 'admin_tenants', 'Mengaktifkan atau membekukan (suspend) akses tenant bisnis.'],

            // ─── 3. Basis Pengguna Platform ───────────────────────────
            'admin.users.view'                     => ['Lihat Pengguna Platform', 'admin_tenants', 'Melihat seluruh akun pengguna dan owner bisnis terdaftar.'],
            'admin.users.export'                   => ['Export Pengguna Platform', 'admin_tenants', 'Export data pengguna platform ke format CSV/Excel.'],
            'admin.users.delete'                   => ['Hapus Pengguna Platform', 'admin_tenants', 'Menghapus akun pengguna dari sistem platform.'],

            // ─── 4. Pemulihan Akun Bisnis ──────────────────────────────
            'admin.account_recoveries.view'          => ['Lihat Tiket Pemulihan Akun', 'admin_support', 'Melihat daftar permohonan pemulihan akses akun pemilik bisnis.'],
            'admin.account_recoveries.view_document' => ['Lihat Dokumen Verifikasi', 'admin_support', 'Melihat lampiran dokumen identitas (KTP/SIUP/NIB) pemohon pemulihan akun.'],
            'admin.account_recoveries.approve'       => ['Setujui Pemulihan Akun', 'admin_support', 'Menyetujui dan mereset kredensial akun bisnis yang sah.'],
            'admin.account_recoveries.reject'        => ['Tolak Pemulihan Akun', 'admin_support', 'Menolak permohonan pemulihan akun yang tidak valid atau mencurigakan.'],

            // ─── 5. Omnichannel Inbox / CS Hub ────────────────────────
            'admin.inbox.view'                     => ['Lihat Inbox CS Platform', 'admin_support', 'Melihat obrolan dan pesan masuk dari berbagai saluran media platform.'],
            'admin.inbox.reply'                    => ['Balas Pesan Pelanggan', 'admin_support', 'Membalas obrolan langsung ke pelanggan via Inbox.'],
            'admin.inbox.manage'                   => ['Kelola Tag & Status Inbox', 'admin_support', 'Mengubah status tiket, tagging, dan konfigurasi integrasi inbox.'],

            // ─── 6. Feedback & Bug Reports ────────────────────────────
            'admin.feedback.view'                  => ['Lihat Feedback & Laporan Bug', 'admin_support', 'Melihat daftar laporan kendala, bug, dan usulan fitur dari pengguna.'],
            'admin.feedback.manage'                => ['Kelola & Update Status Feedback', 'admin_support', 'Mengubah status tindak lanjut bug atau fitur yang dilaporkan.'],

            // ─── 7. Langganan SaaS & Billing ──────────────────────────
            'admin.subscriptions.view'             => ['Lihat Langganan SaaS', 'admin_finance', 'Melihat riwayat langganan bisnis, status invoice, dan bukti bayar transfer.'],
            'admin.subscriptions.approve'          => ['Setujui Pembayaran Langganan', 'admin_finance', 'Memverifikasi bukti transfer dan mengaktifkan paket langganan tenant.'],
            'admin.subscriptions.reject'           => ['Tolak Pembayaran Langganan', 'admin_finance', 'Menolak bukti transfer langganan yang tidak valid.'],

            // ─── 8. Pencairan Merchant / Settlements ───────────────────
            'admin.settlements.view'               => ['Lihat Pencairan Merchant', 'admin_finance', 'Melihat daftar pengajuan penarikan dana/settlement saldo merchant.'],
            'admin.settlements.approve'            => ['Setujui Pencairan Saldo', 'admin_finance', 'Menyetujui pencairan dana dan mengunggah bukti transfer bank.'],
            'admin.settlements.reject'             => ['Tolak Pencairan Saldo', 'admin_finance', 'Menolak pengajuan pencairan dana yang tidak memenuhi syarat.'],

            // ─── 9. Paket & Harga SaaS ────────────────────────────────
            'admin.billing_packages.view'          => ['Lihat Paket Langganan', 'admin_finance', 'Melihat katalog paket langganan fitur platform.'],
            'admin.billing_packages.create'        => ['Tambah Paket Langganan', 'admin_finance', 'Menambahkan varian paket langganan baru.'],
            'admin.billing_packages.edit'          => ['Edit Paket Langganan', 'admin_finance', 'Mengubah harga, kuota limit, dan deskripsi paket langganan.'],
            'admin.billing_packages.toggle'        => ['Aktif/Nonaktifkan Paket', 'admin_finance', 'Mengubah status publikasi paket langganan.'],

            // ─── 10. Kode Promo & Voucher SaaS ────────────────────────
            'admin.promos.view'                    => ['Lihat Kode Promo', 'admin_finance', 'Melihat daftar kode kupon diskon langganan SaaS.'],
            'admin.promos.create'                  => ['Tambah Kode Promo', 'admin_finance', 'Membuat kode promo dan voucher diskon baru.'],
            'admin.promos.edit'                    => ['Edit Kode Promo', 'admin_finance', 'Mengubah nilai diskon, kuota pemakaian, dan masa berlaku promo.'],
            'admin.promos.toggle'                  => ['Aktif/Nonaktifkan Promo', 'admin_finance', 'Mengubah status aktif/nonaktif kupon promo.'],
            'admin.promos.delete'                  => ['Hapus Kode Promo', 'admin_finance', 'Menghapus kode kupon promo dari database.'],

            // ─── 11. Rekening Bank Platform ───────────────────────────
            'admin.payment_accounts.view'          => ['Lihat Rekening Platform', 'admin_finance', 'Melihat daftar rekening bank penampung platform.'],
            'admin.payment_accounts.create'        => ['Tambah Rekening Platform', 'admin_finance', 'Menambahkan nomor rekening bank baru platform.'],
            'admin.payment_accounts.edit'          => ['Edit Rekening Platform', 'admin_finance', 'Mengubah data rekening bank platform.'],
            'admin.payment_accounts.delete'        => ['Hapus Rekening Platform', 'admin_finance', 'Menghapus rekening bank platform.'],

            // ─── 12. Buku Kas & Akuntansi Platform ─────────────────────
            'admin.finance.view'                   => ['Lihat Buku Kas & Keuangan', 'admin_finance', 'Melihat buku kas platform, ringkasan laba rugi, dan rekonsiliasi billing & settlement.'],
            'admin.finance.manage'                 => ['Kelola Transaksi Kas Platform', 'admin_finance', 'Mencatat transaksi masuk/keluar, mengunggah bukti lampiran gambar, dan mengubah jurnal.'],
            'admin.finance.export'                 => ['Export Laporan Keuangan', 'admin_finance', 'Mengekspor data transaksi buku kas dan laporan laba rugi ke file CSV.'],
            'admin.finance.sync'                   => ['Sinkronisasi Billing & Settlement', 'admin_finance', 'Menyinkronkan transaksi otomatis dari tagihan langganan dan pencairan saldo merchant.'],

            // ─── 12B. Penjualan B2B & Faktur Platform ──────────────────
            'admin.b2b.view'                       => ['Lihat Penjualan B2B & Faktur', 'admin_finance', 'Melihat surat penawaran, pesanan penjualan, faktur komersial, retur, dan mengunduh PDF.'],
            'admin.b2b.manage'                     => ['Kelola Transaksi B2B & Faktur', 'admin_finance', 'Menerbitkan penawaran, konversi ke SO, penerbitan faktur, catat pembayaran, dan otorisasi retur.'],
            'admin.b2b.export'                     => ['Export Data Penjualan B2B', 'admin_finance', 'Mengekspor laporan pipeline B2B, faktur, dan retur ke file CSV.'],

            // ─── 13. WhatsApp Gateway Platform ────────────────────────
            'admin.whatsapp.view'                  => ['Lihat WhatsApp Gateway', 'admin_system', 'Melihat status koneksi Meta WhatsApp API dan sesi aktif.'],
            'admin.whatsapp.blast'                 => ['Kirim Blast & Pengingat WA', 'admin_support', 'Mengirim pesan broadcast pengumuman atau reminder tagihan ke tenant.'],
            'admin.whatsapp.templates'             => ['Kelola Template Meta WA', 'admin_system', 'Sinkronisasi dan pendaftaran template pesan WhatsApp ke Meta Cloud API.'],
            'admin.whatsapp.config'                => ['Konfigurasi Gateway WhatsApp', 'admin_system', 'Mengatur kredensial token dan webhook WhatsApp platform.'],

            // ─── 13. Media Sosial Platform ────────────────────────────
            'admin.social_media.view'              => ['Lihat Media Sosial Platform', 'admin_content', 'Melihat akun medsos platform dan log postingan.'],
            'admin.social_media.connect'           => ['Hubungkan Akun Medsos', 'admin_system', 'Menghubungkan integrasi API medsos (LinkedIn, TikTok, Meta).'],
            'admin.social_media.post'              => ['Buat & Jadwalkan Postingan', 'admin_content', 'Membuat dan menjadwalkan konten postingan ke medsos platform.'],
            'admin.social_media.publish'           => ['Publikasikan Postingan Medsos', 'admin_content', 'Menerbitkan langsung postingan ke medsos platform.'],
            'admin.social_media.reply'             => ['Balas Komentar Medsos', 'admin_support', 'Membalas komentar audiens di kanal media sosial.'],

            // ─── 14. Token AI Platform ────────────────────────────────
            'admin.ai_tokens.view'                 => ['Lihat Monitoring Token AI', 'admin_system', 'Melihat pemakaian kuota token AI dan model LLM per tenant.'],
            'admin.ai_tokens.grant'                => ['Berikan Bonus Token AI', 'admin_system', 'Menambahkan kuota token AI tambahan ke tenant bisnis.'],

            // ─── 15. Database Leads ───────────────────────────────────
            'admin.leads.view'                     => ['Lihat Database Leads', 'admin_support', 'Melihat data kontak prospek pengguna baru.'],
            'admin.leads.export'                   => ['Export Database Leads', 'admin_support', 'Mengunduh data prospek leads ke file Excel/CSV.'],

            // ─── 16. Event Komunitas ──────────────────────────────────
            'admin.events.view'                    => ['Lihat Event Komunitas', 'admin_content', 'Melihat kalender acara dan daftar peserta event komunitas.'],
            'admin.events.create'                  => ['Buat Event Komunitas', 'admin_content', 'Menambahkan jadwal event seminar/webinar UMKM baru.'],
            'admin.events.edit'                    => ['Edit Event Komunitas', 'admin_content', 'Mengubah rincian, lokasi, atau tanggal pelaksanaan event.'],
            'admin.events.checkin'                 => ['Proses Check-in Peserta', 'admin_content', 'Memvalidasi kehadiran peserta di lokasi event.'],
            'admin.events.export'                  => ['Export Data Peserta Event', 'admin_content', 'Export daftar registrasi peserta event ke Excel.'],

            // ─── 17. Moderasi Kata Terlarang ──────────────────────────
            'admin.word_filters.view'              => ['Lihat Filter Kata Terlarang', 'admin_content', 'Melihat daftar kata terlarang (judol, sara, scam).'],
            'admin.word_filters.create'            => ['Tambah Filter Kata', 'admin_content', 'Menambahkan kata atau frasa terlarang baru ke blacklist.'],
            'admin.word_filters.toggle'            => ['Aktif/Nonaktifkan Filter', 'admin_content', 'Mengaktifkan atau mematikan filter moderasi kata.'],
            'admin.word_filters.delete'            => ['Hapus Filter Kata', 'admin_content', 'Menghapus kata dari daftar saringan terlarang.'],

            // ─── 18. Artikel & Edukasi CMS ────────────────────────────
            'admin.posts.view'                     => ['Lihat Artikel Blog Edukasi', 'admin_content', 'Melihat daftar artikel dan materi edukasi bisnis.'],
            'admin.posts.create'                   => ['Buat Artikel Blog', 'admin_content', 'Menulis artikel edukasi baru untuk publikasi portal.'],
            'admin.posts.edit'                     => ['Edit Artikel Blog', 'admin_content', 'Mengubah isi, gambar hero, dan optimasi SEO artikel.'],
            'admin.posts.categories'               => ['Kelola Kategori & Cluster', 'admin_content', 'Mengatur taksonomi kategori dan topik materi blog.'],

            // ─── 19. Template Excel CMS ───────────────────────────────
            'admin.templates.view'                 => ['Lihat Template Excel', 'admin_content', 'Melihat daftar template spreadsheet yang disediakan untuk UMKM.'],
            'admin.templates.create'               => ['Upload Template Excel', 'admin_content', 'Mengunggah file template dokumen baru.'],
            'admin.templates.edit'                 => ['Edit Informasi Template', 'admin_content', 'Mengubah nama, deskripsi, atau mengganti berkas template.'],
            'admin.templates.delete'               => ['Hapus Template Excel', 'admin_content', 'Menghapus file template dari platform.'],
            'admin.templates.download'             => ['Unduh File Template', 'admin_content', 'Mengunduh berkas template untuk pengecekan isi.'],

            // ─── 20. Kebijakan Legalitas CMS ──────────────────────────
            'admin.legal_pages.view'               => ['Lihat Halaman Kebijakan Legal', 'admin_content', 'Melihat isi Syarat & Ketentuan serta Kebijakan Privasi.'],
            'admin.legal_pages.edit'               => ['Edit Halaman Kebijakan Legal', 'admin_content', 'Memperbarui dokumen hukum dan klausul privasi platform.'],
            'admin.legal_pages.toggle'             => ['Aktif/Nonaktifkan Halaman', 'admin_content', 'Mengatur status visibilitas halaman legal.'],

            // ─── 21. Pengaturan Sistem & API ──────────────────────────
            'admin.settings.view'                  => ['Lihat Pengaturan Sistem', 'admin_system', 'Melihat konfigurasi platform, API key, dan integrasi.'],
            'admin.settings.update'                => ['Ubah Pengaturan Umum', 'admin_system', 'Menyimpan perubahan branding, SEO, dan konfigurasi platform.'],
            'admin.settings.billing'               => ['Ubah Pengaturan Billing SaaS', 'admin_system', 'Mengatur konfigurasi payment gateway Tripay dan auto-billing.'],
            'admin.settings.test'                  => ['Uji Koneksi Integrasi API', 'admin_system', 'Menjalankan tes koneksi API (Tripay, Biteship, WA, medsos).'],

            // ─── 22. Konfigurasi SMTP ─────────────────────────────────
            'admin.smtp.view'                      => ['Lihat Konfigurasi SMTP', 'admin_system', 'Melihat konfigurasi mail server platform.'],
            'admin.smtp.update'                    => ['Ubah Konfigurasi SMTP', 'admin_system', 'Menyimpan kredensial host, port, dan user SMTP.'],
            'admin.smtp.test'                      => ['Kirim Email Uji Coba (Test)', 'admin_system', 'Mengirimkan email percobaan untuk verifikasi koneksi SMTP.'],

            // ─── 23. Log Error & Diagnostik ───────────────────────────
            'admin.error_logs.view'                => ['Lihat Log Error Sistem', 'admin_system', 'Melihat log pengecualian (exception) dan pesan error aplikasi.'],
            'admin.error_logs.download'            => ['Download Berkas Log Error', 'admin_system', 'Mengunduh file laravel.log untuk investigasi mendalam.'],
            'admin.error_logs.clear'               => ['Bersihkan File Log Error', 'admin_system', 'Mengosongkan isi file log error untuk merapikan disk.'],

            // ─── 24. Manajemen Role & Hak Akses Admin ─────────────────
            'admin.roles.view'                     => ['Lihat Peran & Hak Akses Admin', 'admin_system', 'Melihat daftar role platform admin, matriks hak akses permission, dan staf admin terdaftar.'],
            'admin.roles.manage'                   => ['Kelola Peran & Hak Akses Admin', 'admin_system', 'Membuat atau mengedit hak akses permission role admin, menghapus role custom, dan mengubah peran staf admin.'],

            // ─── 25. Profil Admin ─────────────────────────────────────
            'admin.profile.manage'                 => ['Kelola Profil Sendiri', 'admin_dashboard', 'Mengubah data diri dan kata sandi akun administrator.'],
        ];
    }

    /**
     * Definition of Admin Platform Roles and mapped permissions.
     *
     * @return array<string, array{name: string, description: string, permissions: list<string>}>
     */
    public function adminRoleDefinitions(): array
    {
        $allKeys = array_keys($this->adminPermissionMatrix());

        // Extract read-only permissions (ending in .view)
        $readOnlyPermissions = array_values(array_filter($allKeys, fn (string $k) => str_ends_with($k, '.view')));

        return [
            'super_admin' => [
                'name'        => 'Super Administrator Platform',
                'description' => 'Akses penuh tanpa batas ke seluruh modul, pengaturan, dan finansial platform COOCA.',
                'permissions' => $allKeys,
            ],
            'operations_admin' => [
                'name'        => 'Platform Operations Admin',
                'description' => 'Mengelola tenant bisnis, basis pengguna, tiket pemulihan akun, feedback, dan leads.',
                'permissions' => [
                    'admin.dashboard.view',
                    'admin.businesses.view',
                    'admin.businesses.toggle_status',
                    'admin.users.view',
                    'admin.users.export',
                    'admin.users.delete',
                    'admin.account_recoveries.view',
                    'admin.account_recoveries.view_document',
                    'admin.account_recoveries.approve',
                    'admin.account_recoveries.reject',
                    'admin.feedback.view',
                    'admin.feedback.manage',
                    'admin.b2b.view',
                    'admin.b2b.manage',
                    'admin.leads.view',
                    'admin.leads.export',
                    'admin.profile.manage',
                ],
            ],
            'finance_admin' => [
                'name'        => 'Platform Finance & Billing Admin',
                'description' => 'Mengelola langganan SaaS, verifikasi pembayaran, pencairan saldo merchant, paket billing, dan voucher promo.',
                'permissions' => [
                    'admin.dashboard.view',
                    'admin.subscriptions.view',
                    'admin.subscriptions.approve',
                    'admin.subscriptions.reject',
                    'admin.settlements.view',
                    'admin.settlements.approve',
                    'admin.settlements.reject',
                    'admin.billing_packages.view',
                    'admin.billing_packages.create',
                    'admin.billing_packages.edit',
                    'admin.billing_packages.toggle',
                    'admin.promos.view',
                    'admin.promos.create',
                    'admin.promos.edit',
                    'admin.promos.toggle',
                    'admin.promos.delete',
                    'admin.payment_accounts.view',
                    'admin.payment_accounts.create',
                    'admin.payment_accounts.edit',
                    'admin.payment_accounts.delete',
                    'admin.finance.view',
                    'admin.finance.manage',
                    'admin.finance.export',
                    'admin.finance.sync',
                    'admin.b2b.view',
                    'admin.b2b.manage',
                    'admin.b2b.export',
                    'admin.profile.manage',
                ],
            ],
            'support_admin' => [
                'name'        => 'Platform Support & Helpdesk Admin',
                'description' => 'Merespon inbox omnichannel, menindaklanjuti feedback/bugs, tiket pemulihan akun, dan blast pengumuman.',
                'permissions' => [
                    'admin.dashboard.view',
                    'admin.inbox.view',
                    'admin.inbox.reply',
                    'admin.inbox.manage',
                    'admin.feedback.view',
                    'admin.feedback.manage',
                    'admin.account_recoveries.view',
                    'admin.whatsapp.view',
                    'admin.whatsapp.blast',
                    'admin.social_media.reply',
                    'admin.leads.view',
                    'admin.profile.manage',
                ],
            ],
            'content_admin' => [
                'name'        => 'Platform Content & Community Admin',
                'description' => 'Mengelola materi edukasi/artikel blog, kalender event komunitas, template Excel, halaman legalitas, dan moderasi kata.',
                'permissions' => [
                    'admin.dashboard.view',
                    'admin.posts.view',
                    'admin.posts.create',
                    'admin.posts.edit',
                    'admin.posts.categories',
                    'admin.events.view',
                    'admin.events.create',
                    'admin.events.edit',
                    'admin.events.checkin',
                    'admin.events.export',
                    'admin.templates.view',
                    'admin.templates.create',
                    'admin.templates.edit',
                    'admin.templates.delete',
                    'admin.templates.download',
                    'admin.legal_pages.view',
                    'admin.legal_pages.edit',
                    'admin.legal_pages.toggle',
                    'admin.word_filters.view',
                    'admin.word_filters.create',
                    'admin.word_filters.toggle',
                    'admin.word_filters.delete',
                    'admin.social_media.view',
                    'admin.social_media.post',
                    'admin.social_media.publish',
                    'admin.profile.manage',
                ],
            ],
            'system_admin' => [
                'name'        => 'Platform System & DevOps Admin',
                'description' => 'Konfigurasi platform, API gateways, mail SMTP, kuota token AI, dan pemantauan error log server.',
                'permissions' => [
                    'admin.dashboard.view',
                    'admin.settings.view',
                    'admin.settings.update',
                    'admin.settings.billing',
                    'admin.settings.test',
                    'admin.smtp.view',
                    'admin.smtp.update',
                    'admin.smtp.test',
                    'admin.error_logs.view',
                    'admin.error_logs.download',
                    'admin.error_logs.clear',
                    'admin.ai_tokens.view',
                    'admin.ai_tokens.grant',
                    'admin.whatsapp.view',
                    'admin.whatsapp.templates',
                    'admin.whatsapp.config',
                    'admin.social_media.connect',
                    'admin.roles.view',
                    'admin.roles.manage',
                    'admin.profile.manage',
                ],
            ],
            'auditor' => [
                'name'        => 'Platform Auditor & Compliance',
                'description' => 'Akses hanya lihat (read-only) ke seluruh metrik, tenant, transaksi, dan log sistem untuk audit kepatuhan.',
                'permissions' => array_merge($readOnlyPermissions, ['admin.profile.manage']),
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ── 1. Seed all Admin Permissions ──────────────────────────────
        $permsBySlug = [];
        foreach ($this->adminPermissionMatrix() as $slug => [$name, $category, $description]) {
            $permsBySlug[$slug] = Permission::updateOrCreate(
                ['slug' => $slug],
                compact('name', 'category', 'description')
            );
        }

        $this->command?->info('  ✓ ' . count($permsBySlug) . ' admin permissions seeded.');

        // ── 2. Seed Admin Global Roles (business_id = null) ───────────
        foreach ($this->adminRoleDefinitions() as $slug => $data) {
            /** @var Role $role */
            $role = Role::updateOrCreate(
                ['business_id' => null, 'slug' => $slug],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                ]
            );

            $syncData = [];
            foreach ($data['permissions'] as $permSlug) {
                if (isset($permsBySlug[$permSlug])) {
                    $syncData[$permsBySlug[$permSlug]->id] = [
                        'id' => (string) Str::uuid(),
                    ];
                }
            }

            $role->permissions()->sync($syncData);

            // Invalidate cache for this admin role
            Cache::forget("admin_role_perms_{$slug}");

            $this->command?->line("  ✓ Admin Role [{$slug}] → " . count($syncData) . ' permissions synced.');
        }

        $this->command?->info('  ✓ Admin RBAC seeder complete.');
    }
}
