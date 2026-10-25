# Residual Risks - crm

> Modul: CRM & Loyalty | Tanggal: 2026-10-25

## 1. Daftar Risiko Residual Pasca-Audit

Meskipun perbaikan keamanan, RBAC, dan ergonomi antarmuka telah berhasil diterapkan, terdapat beberapa risiko residual operasional yang perlu diperhatikan oleh pemilik usaha:

| ID Risiko | Area Risiko | Deskripsi Risiko Residual | Rekomendasi Pemantauan / SOP |
| :--- | :--- | :--- | :--- |
| **RES-CRM-01** | **Operasional Kasir** | Kasir menerima setoran pelunasan kasbon dari pelanggan tetapi lupa memilih akun kas yang tepat saat menginput formulir di sistem Cooca. | Lakukan rekonsiliasi kas harian (*cash opname*) antara fisik uang laci kasir dengan mutasi kas masuk pelunasan piutang di laporan kas harian. |
| **RES-CRM-02** | **Kupon Promosi** | Kupon promosi dengan diskon persentase besar dibagikan secara luas di media sosial tanpa pembatas batas potongan rupiah maksimal (`max_discount_amount`). | Wajibkan penetapan `max_discount_amount` pada SOP penerbitan voucher diskon persentase toko. |
| **RES-CRM-03** | **Koneksi Jaringan** | Terputusnya koneksi internet pada perangkat kasir saat proses pelunasan kasbon sedang dikirimkan ke server. | Sistem terlindungi transaksi database atomik (ACID). Jika terjadi kegagalan transmisi, kasir cukup me-refresh halaman untuk memastikan apakah saldo telah terpotong sebelum mencoba submit ulang. |

## 2. Jadwal Review Berkala
- **Audit Rutin Kasbon:** Disarankan dilakukan setiap akhir bulan untuk memverifikasi pelanggan dengan usia piutang lebih dari 30 hari (*aging receivables*).
- **Audit Voucher Kedaluwarsa:** Pemilik usaha dapat membersihkan atau menonaktifkan voucher promosi lama setiap pergantian kuartal guna menjaga kerapian katalog promosi toko.
