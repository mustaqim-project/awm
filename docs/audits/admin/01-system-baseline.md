# System Baseline & Asset Inventory - Modul Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Commit: `a17c5ae` | Guard: `admin` | Prefix: `/admin` | Tanggal Audit: 2026-10-26

---

## 1. Lingkup Modul & Batasan Sistem

Modul `admin` merupakan sub-sistem backoffice pusat dari COOCA. Platform ini didesain eksklusif untuk operasional platform developer, superadmin, dan staf pengelola pusat dengan isolasi total dari guard aplikasi tenant (`web`) maupun konsumen toko daring (`customer`).

### Parameter Kunci Sistem:
- **Prefix URL:** `/admin`
- **Route Name Prefix:** `admin.`
- **Guard Autentikasi:** `admin` (provider: `admins`, model: `App\Models\Admin`)
- **Tabel Basis Data Utama:** `admins`, `admin_roles`, `admin_permissions`, `admin_role_permission`, `admin_admin_role`, `system_settings`, `payment_accounts`, `account_recovery_requests`, `billing_packages`, `subscription_payments`, `leads`, `error_logs`.
- **Layout Induk:** [`resources/views/layouts/admin.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/admin.blade.php)

---

## 2. Inventaris Kontroler (30 Controllers)

Seluruh kontroler admin terletak di direktori [`app/Http/Controllers/Admin/`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Admin):

| No | Kontroler | Tipe Autentikasi | Controller-Level RBAC | Tanggung Jawab Utama |
| :---: | :--- | :---: | :---: | :--- |
| 1 | `AdminAuthController` | Guest | N/A (Guest) | Login, otentikasi sesi superadmin, logout |
| 2 | `AdminPasswordResetController` | Guest | N/A (Guest) | Permintaan & atur ulang kata sandi admin |
| 3 | `AdminDashboardController` | Auth (`admin`) | `admin.dashboard.view` | Dashboard metrik agregat platform, omzet, MRR |
| 4 | `AdminBusinessController` | Auth (`admin`) | `admin.businesses.*` | Manajemen tenant UMKM, switch status, impersonasi |
| 5 | `AdminUserController` | Auth (`admin`) | `admin.users.*` | Manajemen data pengguna, reset akses, ekspor CSV |
| 6 | `AdminAccountRecoveryController` | Auth (`admin`) | `admin.account_recoveries.*` | Verifikasi berkas KTP/NIB & persetujuan pemulihan akun |
| 7 | `AdminSubscriptionController` | Auth (`admin`) | `admin.subscriptions.*` | Monitoring transaksi langganan SaaS & filter status |
| 8 | `AdminBillingPackageController` | Auth (`admin`) | `admin.billing_packages.*` | Konfigurasi paket langganan UMKM & add-on storage |
| 9 | `AdminPaymentAccountController` | Auth (`admin`) | `admin.payment_accounts.*` | Manajemen rekening bank penerima pembayaran manual |
| 10 | `AdminFinanceController` | Auth (`admin`) | `admin.finance.*` | Rekapitulasi keuangan platform, payout, revenue |
| 11 | `AdminSettlementController` | Auth (`admin`) | `admin.settlements.*` | Rekonsiliasi & penyelesaian dana transaksi TriPay/POS |
| 12 | `AdminAiTokenController` | Auth (`admin`) | `admin.ai_tokens.*` | Pemantauan kuota token AI & hibah token tenant |
| 13 | `AdminSettingController` | Auth (`admin`) | `admin.settings.*` | Pengaturan platform, TriPay, SMTP, Meta WhatsApp |
| 14 | `AdminWhatsAppController` | Auth (`admin`) | `admin.whatsapp.*` | Template pesan Meta WA Cloud & broadcast sistem |
| 15 | `AdminSmtpController` | Auth (`admin`) | `admin.smtp.*` | Konfigurasi mailer server & uji pengiriman email |
| 16 | `AdminRoleController` | Auth (`admin`) | `admin.roles.*` | Manajemen role & matriks 92 permissions RBAC |
| 17 | `AdminProfileController` | Auth (`admin`) | `admin.profile.*` | Profil pribadi admin & pembaruan kata sandi |
| 18 | `AdminLeadController` | Auth (`admin`) | `admin.leads.*` | CRM pendaftaran prospek calon tenant COOCA |
| 19 | `AdminInboxController` | Auth (`admin`) | `admin.inbox.*` | Pusat pesan masuk bantuan, tiket, & pertanyaan |
| 20 | `AdminFeedbackController` | Auth (`admin`) | `admin.feedback.*` | Kurasi feedback & rating pengguna terhadap fitur |
| 21 | `AdminB2bController` | Auth (`admin`) | `admin.b2b.*` | Operasional order, invoice, PO, & return B2B |
| 22 | `AdminErrorLogController` | Auth (`admin`) | `admin.error_logs.*` | Inspeksi jejak error sistem Laravel & stack trace |
| 23 | `AdminTemplateController` | Auth (`admin`) | `admin.templates.*` | Master template file Excel impor master data |
| 24 | `AdminSocialMediaController` | Auth (`admin`) | `admin.social_media.*` | Integrasi akun media sosial & tautan publik |
| 25 | `AdminPostController` | Auth (`admin`) | `admin.posts.*` | CMS artikel blog & pengumuman resmi platform |
| 26 | `AdminLegalPageController` | Auth (`admin`) | `admin.legal_pages.*` | Pengelolaan laman Syarat Ketentuan & Kebijakan Privasi |
| 27 | `AdminCommunityEventController`| Auth (`admin`) | `admin.community_events.*` | Agenda webinar, meetup, & pelatihan UMKM |
| 28 | `AdminMarketingCampaignController`| Auth (`admin`) | `admin.marketing_campaigns.*`| Manajemen kampanye diskon & promo langganan |
| 29 | `AdminDataRetentionController` | Auth (`admin`) | `admin.data_retention.*` | Kebijakan pembersihan log lama & audit trail |
| 30 | `AdminHelpDeskController` | Auth (`admin`) | `admin.helpdesk.*` | Manajemen basis pengetahuan & artikel panduan |

---

## 3. Metrik Routing (`routes/admin.php`)

- **Total Route Terdaftar:** 204 routes
- **Metode HTTP:**
  - `GET / HEAD`: 118 routes
  - `POST`: 54 routes
  - `PUT`: 18 routes
  - `DELETE`: 14 routes
- **Grup Middleware Global:**
  - Guest Routes: `['guest:admin']` dengan pembatasan *rate limiting* `throttle:5,1` dan `throttle:3,1`.
  - Authenticated Workspace: `['auth:admin', \App\Http\Middleware\EnsureAdminIsActive::class]`.
  - Granular Security: Controller constructor / `middleware()` mendefinisikan `require.admin.permission:{slug}`.

---

## 4. Struktur View Template (`resources/views/admin/`)

Terdapat 28 sub-direktori dengan lebih dari 80 berkas Blade template:

```
resources/views/admin/
├── account-recoveries/      # 3 views (index, show, document-viewer)
├── ai-tokens/               # 2 views (index, grant-modal)
├── auth/                    # 4 views (login, forgot-password, reset-password, verify)
├── b2b/                     # 6 views & tabs (index, invoices, orders, quotations, returns, show)
├── billing_packages/        # 4 views (index, create, edit, show)
├── businesses/              # 4 views (index, show, impersonate-banner, tabs)
├── community_events/        # 3 views (index, form, attendees)
├── dashboard.blade.php      # Main KPI dashboard
├── error-logs/              # 2 views (index, log-viewer)
├── feedback/                # 2 views (index, detail-drawer)
├── finance/                 # 4 views (index, payouts, revenue, modal-lightbox)
├── inbox/                   # 3 views (index, conversation, quick-reply)
├── leads/                   # 3 views (index, status-pipeline, detail)
├── legal-pages/             # 3 views (index, editor, preview)
├── marketing_campaigns/     # 3 views (index, builder, statistics)
├── payment_accounts/        # 4 views (index, create, edit, audit-rekening)
├── posts/                   # 4 views (index, create, edit, categories)
├── profile/                 # 2 views (index, security-tab)
├── roles/                   # 3 views (index, matrix-table, role-modal)
├── settlements/             # 3 views (index, batch-settle, detail)
├── settings/                # 9 views (index, tabs: system, payment, whatsapp, smtp, etc.)
├── smtp/                    # 2 views (index, test-mail-modal)
├── social_media/            # 2 views (index, channels)
├── subscriptions/           # 3 views (index, filter-bento, invoice-modal)
├── templates/               # 3 views (index, create, edit)
├── users/                   # 3 views (index, impersonate-user, export)
└── whatsapp/                # 4 views (index, templates, logs, broadcast-form)
```

---

## 5. Layanan Eksternal & Integrasi Kunci

1. **TriPay Payment Gateway:** Dikelola via [`app/Domain/Payment/TripayService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Payment/TripayService.php) untuk otomasi QRIS dinamis & VA pembayaran biaya berlangganan platform.
2. **Meta WhatsApp Cloud API:** Dikelola via [`app/Domain/WhatsApp/AdminWhatsAppService.php`](file:///c:/laragon/www/cooca_core/app/Domain/WhatsApp/AdminWhatsAppService.php) untuk notifikasi resmi persetujuan pemulihan akun, tagihan, dan OTP verifikasi.
3. **SaaS Entitlement Engine:** Dikelola via [`app/Domain/Billing/EntitlementService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Billing/EntitlementService.php) untuk kalkulasi kuota AI token, limit cabang, dan masa aktif langganan tenant.
