# Residual Risks & Continuous Monitoring - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## 1. Analisis Risiko Residual (Residual Risks)
Setelah seluruh perbaikan dan hardening diimplementasikan, risiko residual yang tersisa berada pada level minimal yang dapat diterima (*acceptable low risk*):

| ID Risiko | Deskripsi Risiko Residual | Tingkat Risiko Residual | Rekomendasi Mitigasi Berkelanjutan |
|---|---|---|---|
| **RES-SET-01** | **Kebocoran Kredensial Pengguna Akibat Malware Perangkat Klien:** Akun owner diretas di luar kendali server (misal: keylogger di laptop pemilik). | Rendah | Mengaktifkan Two-Factor Authentication (2FA) via OTP WhatsApp atau Authenticator App untuk akses menu Pengaturan. |
| **RES-SET-02** | **Rotasi Token AI yang Jarang Dilakukan:** Token MCP dibiarkan aktif tanpa masa kadaluarsa selama berbulan-bulan. | Rendah | Memberikan rekomendasi di antarmuka agar pengguna mengatur masa kadaluarsa (misal: 30 atau 90 hari) dan melakukan rotasi berkala. |
| **RES-SET-03** | **Perubahan Endpoint Vendor AI Pihak Ketiga:** Perubahan skema API pada pembaruan Claude Desktop, ChatGPT Actions, atau Google Gemini. | Rendah | Pemantauan berkala spesifikasi Model Context Protocol (MCP) dan OpenAPI connector untuk pembaruan dokumentasi panduan. |

## 2. Strategi Pemantauan Berkelanjutan (Continuous Monitoring)
1. **Peringatan Audit Log Real-Time:** Menyiapkan notifikasi otomatis ke WhatsApp owner jika terjadi perubahan rekening bank bisnis atau eksekusi Master Kill Switch.
2. **Pemantauan Volume Panggilan AI:** Memantau lonjakan abnormal pada metrik panggilan perkakas MCP di tabel `mcp_activity_logs`.
3. **Pembersihan Log Berkala:** Mengatur scheduler pembersihan log aktivitas MCP yang berusia lebih dari 90 hari untuk menghemat ruang penyimpanan database.
