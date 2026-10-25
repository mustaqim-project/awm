# Residual Risks - customers

> Modul: Pelanggan (Customers Master Directory) | Tanggal: 2026-10-25

## 1. Daftar Risiko Residual Pasca-Audit

Meskipun perbaikan keamanan, Controller-Level RBAC, Deletion Guard saldo kasbon, dan Formula Sanitization telah diterapkan, beberapa risiko residual operasional tetap perlu diantisipasi:

| ID Risiko | Area Risiko | Deskripsi Risiko Residual | Rekomendasi Pemantauan / SOP |
| :--- | :--- | :--- | :--- |
| **RES-CUST-01** | **Kualitas Data** | Staf salah memasukkan nomor telepon pelanggan (misal kurang 1 digit) sehingga struk WhatsApp gagal terkirim. | Berikan panduan kasir untuk selalu mengonfirmasi ulang nomor WhatsApp saat mendaftarkan member baru di meja kasir. |
| **RES-CUST-02** | **Batas Kuota Pelanggan** | Jumlah pelanggan mencapai batas paket langganan aktif toko (*entitlement:customer*). | Pemilik usaha dapat memantau kuota di dashboard langganan dan melakukan upgrade paket bila bisnis mengalami ekspansi pesat. |
| **RES-CUST-03** | **Format Impor Excel** | Pengguna mengunggah file Excel dengan ratusan baris data kosong di bagian bawah tabel. | Fitur *Smart Import Preview* telah menyaring baris kosong secara otomatis, namun disarankan pengguna tetap memeriksa kolom preview sebelum mengonfirmasi impor. |

## 2. Jadwal Review Berkala
- **Audit Rutin Piutang Pelanggan:** Disarankan meninjau pelanggan dengan saldo kasbon menggantung secara mingguan di menu CRM.
- **Pembersihan Data Tidak Aktif:** Meninjau pelanggan yang tidak pernah bertransaksi selama lebih dari 12 bulan untuk program reaktivasi promo toko.
