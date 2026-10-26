# Remediation Task List - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## Daftar Tugas Audit & Penguatan (15/15 Selesai & Terverifikasi)

- [x] **TSK-SET-01: Backup Konfigurasi MCP IDE Antigravity**
  - *Deskripsi:* Membuat salinan cadangan berkas `mcp_config.json` di direktori config IDE sebelum pembersihan.
  - *Berkas Terkait:* `C:\Users\Ulfah Ramadhani\.gemini\config\mcp_config.json.bak`, `antigravity\mcp_config.json.bak`.
  - *Verifikasi:* Berkas backup berhasil dibuat dan tersimpan aman.

- [x] **TSK-SET-02: Pembersihan Provider MCP di IDE Global**
  - *Deskripsi:* Menghapus konfigurasi MCP pihak ketiga (Hostinger, Meta) dan mempertahankan ekosistem resmi Gemini.
  - *Berkas Terkait:* `mcp_config.json`.
  - *Verifikasi:* Konfigurasi bersih terverifikasi valid JSON.

- [x] **TSK-SET-03: Implementasi HasMiddleware pada SettingWebController**
  - *Deskripsi:* Memindahkan pengawasan izin RBAC ke controller menggunakan `implements HasMiddleware` (`settings.view`, `settings.edit`, `require.role:owner`, `users.manage`).
  - *Berkas Terkait:* `app/Http/Controllers/Web/SettingWebController.php`.
  - *Verifikasi:* Pengujian fitur `SettingsModuleAuditTest::test_staff_without_settings_view_permission_is_forbidden_403` lulus.

- [x] **TSK-SET-04: Implementasi HasMiddleware pada McpIntegrationWebController**
  - *Deskripsi:* Melindungi rute kokpit AI dan mutasi token dengan `implements HasMiddleware` (`settings.view`, `settings.edit`).
  - *Berkas Terkait:* `app/Http/Controllers/Web/Mcp/McpIntegrationWebController.php`.
  - *Verifikasi:* Pengujian otentikasi dan otorisasi lulus 100%.

- [x] **TSK-SET-05: Integrasi AuditLog pada Master Toggle MCP**
  - *Deskripsi:* Menambahkan pencatatan jejak audit level tinggi (`RISK_HIGH`) saat Master Kill Switch AI diaktifkan/dinonaktifkan.
  - *Berkas Terkait:* `app/Http/Controllers/Web/Mcp/McpIntegrationWebController.php`.
  - *Verifikasi:* Pengujian `test_mcp_toggle_master_switches_all_tokens_and_logs_audit` berhasil memverifikasi baris di tabel `audit_logs`.

- [x] **TSK-SET-06: Pembersihan Tab Provider MCP Non-Resmi**
  - *Deskripsi:* Menghapus tab navigasi Cursor, Ollama, n8n, LangChain, dan Dola dari kokpit MCP.
  - *Berkas Terkait:* `resources/views/app/settings/integrations/mcp.blade.php`.
  - *Verifikasi:* Pengujian `test_mcp_blade_contains_zero_unicode_emojis_and_only_allowed_providers` memverifikasi string tab lama tidak ada lagi.

- [x] **TSK-SET-07: Penyederhanaan Dropdown Provider Hint pada Modal MCP**
  - *Deskripsi:* Membatasi opsi provider pada modal pembuatan dan pembaruan token hanya pada Claude, ChatGPT, dan Gemini.
  - *Berkas Terkait:* `resources/views/app/settings/integrations/mcp.blade.php`.
  - *Verifikasi:* Opsi modal dropdown diverifikasi bersih.

- [x] **TSK-SET-08: Pembersihan Metode Alpine.js yang Usang di MCP Blade**
  - *Deskripsi:* Menghapus fungsi JavaScript pembantu provider yang dihapus (`getCursorSseConfig`, `getDolaConfig`, dll).
  - *Berkas Terkait:* `resources/views/app/settings/integrations/mcp.blade.php`.
  - *Verifikasi:* Script Alpine.js bersih tanpa error konsol.

- [x] **TSK-SET-09: Eliminasi Emoji Unicode pada WhatsApp Blade**
  - *Deskripsi:* Mengganti karakter emoji/simbol lama (`✓`, `📄`, `✓✓`) dengan ikon Lucide SVG profesional (`check`, `file-text`, `check-check`).
  - *Berkas Terkait:* `resources/views/app/settings/integrations/whatsapp.blade.php`.
  - *Verifikasi:* Scanner regex menemukan 0 karakter emoji.

- [x] **TSK-SET-10: Eliminasi Emoji Unicode pada MCP Blade**
  - *Deskripsi:* Menghapus seluruh karakter emoji di panel Dola yang telah dibersihkan.
  - *Berkas Terkait:* `resources/views/app/settings/integrations/mcp.blade.php`.
  - *Verifikasi:* Scanner `scan_settings.py` mengonfirmasi 0 emoji tersisa.

- [x] **TSK-SET-11: Hardening Touch Target Checkbox Hari Operasional**
  - *Deskripsi:* Membungkus elemen checkbox hari kerja dengan kontainer label `min-h-[44px]` sesuai Apple HIG.
  - *Berkas Terkait:* `resources/views/app/settings/index.blade.php`.
  - *Verifikasi:* Touch target memenuhi standar aksesibilitas layar sentuh.

- [x] **TSK-SET-12: Standardisasi Simetris i18n Translation Keys**
  - *Deskripsi:* Menambahkan kunci `'close' => 'Tutup'` di `lang/id/settings.php` dan `'close' => 'Close'` di `lang/en/settings.php`.
  - *Berkas Terkait:* `lang/id/settings.php`, `lang/en/settings.php`.
  - *Verifikasi:* Pengujian `test_all_translation_keys_are_present_in_id_and_en` memverifikasi 100% paritas kunci.

- [x] **TSK-SET-13: Pembuatan Suite Pengujian Fitur Otomatis**
  - *Deskripsi:* Menulis 15 metode pengujian fitur komprehensif mencakup RBAC, audit logs, branch lifecycle, MCP tokens, emoji, dan i18n.
  - *Berkas Terkait:* `tests/Feature/SettingsModuleAuditTest.php`.
  - *Verifikasi:* Eksekusi PHPUnit menghasilkan 15 passed, 71 assertions (100% Green).

- [x] **TSK-SET-14: Penyusunan Dokumentasi Audit Produksi Lengkap**
  - *Deskripsi:* Menulis 13 dokumen audit terperinci dan berkas `audit-state.json` di `docs/audits/settings/`.
  - *Berkas Terkait:* `docs/audits/settings/*.md`.
  - *Verifikasi:* Seluruh dokumen tersusun rapi dengan tautan valid.

- [x] **TSK-SET-15: Penyusunan Panduan Pengguna Berorientasi Bisnis Owner**
  - *Deskripsi:* Menulis panduan penggunaan, FAQ, dan troubleshooting berorientasi pemilik bisnis di `docs/user-guides/settings/`.
  - *Berkas Terkait:* `docs/user-guides/settings/index.md`, `troubleshooting.md`, `faq.md`.
  - *Verifikasi:* Bahasa bersahaja, instruksi langkah demi langkah, dan tautan rute internal.
