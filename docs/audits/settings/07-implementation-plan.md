# Implementation Plan - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## 1. Ikhtisar Rencana Implementasi
Rencana implementasi penguatan modul Pengaturan Bisnis dan Cockpit AI MCP dijalankan melalui 8 tahapan terstruktur (*Phase A s/d H*) dengan prinsip *Zero-Downtime & Non-Destructive Refactoring*:

| Fase | Nama Fase | Status | Hasil Deliverable Utama |
|---|---|---|---|
| **Fase A** | Discovery & Scanning | Selesai | Pemetaan seluruh rute, controller, model, dan scanner otomatis untuk emoji, i18n, serta touch target. |
| **Fase B** | Security & RBAC Hardening | Selesai | Implementasi `HasMiddleware` pada `SettingWebController` dan `McpIntegrationWebController` serta integrasi `AuditLog`. |
| **Fase C** | MCP AI Scoping & Purge | Selesai | Pembatasan eksklusif provider AI pada Claude, ChatGPT, Gemini; pembersihan total provider non-resmi. |
| **Fase D** | UI/UX & Ergonomics Polish | Selesai | Penguatan touch target >= 44px pada checkbox operasional dan standardisasi 100% ikon Lucide SVG. |
| **Fase E** | i18n Translation Parity | Selesai | Penambahan kunci terjemahan simetris ID dan EN untuk 100% paritas bahasa. |
| **Fase F** | Automated Testing Suite | Selesai | Penulisan dan verifikasi suite pengujian fitur lengkap (15 tests, 71 assertions, 100% green). |
| **Fase G** | Comprehensive User Guides | Selesai | Penyusunan panduan pemilik bisnis, FAQ, dan troubleshooting di `docs/user-guides/settings/`. |
| **Fase H** | Production Gate & Rollout | Selesai | Verifikasi 10 kriteria evaluasi produksi dan pengesahan status gate PASSED. |

## 2. Rincian Eksekusi Teknis
1. **Pembersihan Konfigurasi MCP:**
   - Direktori konfigurasi IDE (`C:\Users\Ulfah Ramadhani\.gemini\config\mcp_config.json` dan `antigravity\mcp_config.json`) diamankan dengan backup `.bak`.
   - Menghapus server eksternal Hostinger dan Meta yang tidak lagi digunakan, hanya mempertahankan tools resmi.
2. **Refactoring Blade Template `mcp.blade.php`:**
   - Menghapus tab navigasi dan blok konfigurasi untuk Cursor, Ollama, n8n, LangChain, dan Dola.
   - Menyederhanakan modal pembuatan/edit token untuk hanya menawarkan opsi provider Claude, OpenAI/ChatGPT, dan Gemini.
   - Menghapus metode JavaScript/Alpine.js yang sudah usang (`cursorMode`, `dolaMode`, `getCursorSseConfig`, `getDolaConfig`, dll).
3. **Penyesuaian Touch Target & Ikon:**
   - Menghilangkan seluruh karakter emoji platform di `whatsapp.blade.php` dan `mcp.blade.php`.
   - Mengganti elemen dengan ikon SVG Lucide profesional.
   - Membungkus checkbox hari kerja di `index.blade.php` dengan kontainer label 44px.
4. **Verifikasi Otomatis:**
   - Menjalankan `php vendor/phpunit/phpunit/phpunit tests/Feature/SettingsModuleAuditTest.php` hingga seluruh 15 pengujian lulus.
