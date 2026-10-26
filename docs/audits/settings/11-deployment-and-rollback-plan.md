# Deployment & Rollback Plan - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## 1. Rencana Rilis Produksi (Deployment Plan)
Seluruh modifikasi pada modul Pengaturan Bisnis bersifat *backward-compatible* dan tidak memerlukan perubahan skema tabel (*zero-migration deployment*).

### Langkah-Langkah Deployment:
1. **Pull Kode Terbaru:**
   ```bash
   git pull origin main
   ```
2. **Kompilasi & Cache Konfigurasi:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. **Uji Asap Pasca-Deploy (Smoke Testing):**
   - Buka menu `/settings` menggunakan akun Owner. Pastikan halaman menampilkan tab Profil, Cabang, Keuangan, dan Modul secara presisi.
   - Buka menu `/settings/integrations/mcp`. Pastikan hanya tab Claude, ChatGPT, dan Gemini yang tampil.
   - Uji pembuatan token dummy baru dan pastikan token berhasil digenerate serta dicatat di `audit_logs`.

## 2. Rencana Pemulihan (Rollback Plan)
Jika terjadi insiden tidak terduga pada lingkungan produksi:
1. **Rollback Git Commit:**
   ```bash
   git revert --no-edit <commit-hash>
   git push origin main
   ```
2. **Pembersihan Cache:**
   ```bash
   php artisan view:clear
   php artisan route:clear
   php artisan config:clear
   ```
3. **Verifikasi Integritas:**
   Pastikan controller dan template kembali ke baseline sebelumnya tanpa meninggalkan berkas yatim piatu.
