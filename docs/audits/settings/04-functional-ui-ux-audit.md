# Functional & UI/UX Audit - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## 1. Standar Desain Apple Human Interface Guidelines (HIG)
Antarmuka Pengaturan Bisnis dirancang dengan prinsip Apple HIG untuk menjamin kejelasan informasi (*Clarity*), kedalaman visual (*Depth*), dan kenyamanan interaksi sentuh (*Deference & Ergonomics*):
- **Bento Grid Architecture:** Mengelompokkan pengaturan yang saling terkait ke dalam kartu-kartu Bento dengan kontras visual tinggi, sudut membulat elegan (`rounded-2xl` / `rounded-xl`), dan border halus bernuansa slate modern.
- **Segmented Control Navigation:** Navigasi antar-tab (Umum, Cabang & Outlet, Keuangan & POS, Modul) menggunakan kontrol tersegmentasi dengan efek transisi aktif yang halus.

## 2. Hardening Touch Target (Minimal 36-44px)
Sesuai standar Apple HIG dan WCAG 2.1 AAA:
1. **Branch Operating Hours Checkboxes:** Seluruh elemen checkbox hari operasional toko (Senin s/d Minggu) di `resources/views/app/settings/index.blade.php` telah dibungkus dengan kontainer label sentuh berukuran minimum 44px (`min-h-[44px]`).
2. **Segmented Tabs in Cockpit MCP:** Tombol tab navigasi provider AI di `resources/views/app/settings/integrations/mcp.blade.php` memiliki tinggi sentuh minimum 36px (`min-h-[36px] px-4 py-2`).
3. **Action Buttons & Modals:** Tombol simpan pengaturan, buat cabang baru, salin token AI, dan saklar Master Toggle memiliki tap-target nyaman dengan efek klik responsif.

## 3. Kebijakan Zero Unicode Emoji (100% Lucide SVG Icons)
Seluruh berkas tampilan di bawah `resources/views/app/settings/` telah diaudit bebas dari emoji Unicode platform:
- Pada `resources/views/app/settings/integrations/whatsapp.blade.php`: Karakter teks seperti `✓`, `📄`, `✓✓` telah diganti menjadi ikon Lucide SVG standar (`check`, `file-text`, `check-check`).
- Pada `resources/views/app/settings/integrations/mcp.blade.php`: Karakter emoji lama di panel Dola (`🔑`, `📋`, `🔐`, `❌`) telah dibersihkan secara tuntas seiring pembersihan provider non-resmi.
- Verifikasi scanner `scan_settings.py`: **0 emoji ditemukan** di seluruh 5 template Blade Settings.

## 4. Penataan Ekosistem AI MCP (Exclusive Claude, ChatGPT, Gemini)
Sebelumnya, antarmuka kokpit MCP menampilkan 8 provider AI yang membingungkan pemilik usaha UMKM:
- **Provider yang Dihapus:** Cursor, Ollama, n8n, LangChain, Dola.
- **Provider yang Dipertahankan:**
  1. **Claude (Anthropic):** Petunjuk instalasi Claude Desktop dan Claude Code via file konfigurasi `claude_desktop_config.json`.
  2. **ChatGPT (OpenAI):** Panduan konfigurasi Custom GPT Actions dan OAuth 2.0 Connectors.
  3. **Gemini (Google):** Panduan konfigurasi Google Antigravity dan Gemini Extensions via OpenAPI spec.
- **Modal Pembuatan & Edit Token:** Pilihan dropdown provider hint disederhanakan menjadi:
  - `Semua Platform (Universal)`
  - `Claude Desktop & Code`
  - `ChatGPT & OpenAI Apps`
  - `Google Gemini & Antigravity`

## 5. Umpan Balik Validasi & Pencegahan Human Error
1. **Pratinjau Token Sekali Tampil:** Modal peringatan keamanan khusus saat token baru dibuat, menginstruksikan pengguna untuk segera menyalin token sebelum ditutup.
2. **Dialog Konfirmasi Destruktif:** Tindakan pencabutan token AI atau penghapusan cabang dilindungi modal konfirmasi dengan deskripsi konsekuensi operasional yang jelas.
3. **Pemberitahuan Sukses Interaktif:** Notifikasi flash banner berbasis Alpine.js dengan pesan ramah pengguna dan opsi tutup cepat.
