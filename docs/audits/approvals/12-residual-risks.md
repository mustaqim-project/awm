# Residual Risks - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Risiko Residual Pasca-Hardening

| ID | Risiko Residual | Dampak Residual | Rencana Mitigasi Jangka Panjang |
| :--- | :--- | :--- | :--- |
| **RES-APP-01** | **Eskalasi Waktu Tunggu Otorisasi:** Dokumen tertahan jika pemberi wewenang (Supervisor/Manager) tidak aktif atau sedang berhalangan. | Low (Owner dapat melakukan override darurat) | Fitur pengingat notifikasi otomatis via WhatsApp / Webhook untuk tiket yang pending lebih dari 24 jam. |
| **RES-APP-02** | **Konflik Aturan Plafon Tumpang Tindih:** Pengguna memasukkan rentang plafon nominal yang bersinggungan untuk tipe dokumen yang sama. | Low (Sistem otomatis memprioritaskan aturan dengan `min_amount` tertinggi via query sorting) | Penambahan visual overlap warning pada UI formulir saat menyetel rentang nominal. |
| **RES-APP-03** | **Volume Arsip History:** Tabel `approval_requests` dan `approval_logs` terus membesar seiring pertumbuhan transaksi bisnis korporat. | Low (Kueri riwayat dibatasi limit 50 dan diindeks komposit) | Penjadwalan job pengarsipan otomatis tahunan (*cold storage partition*). |
