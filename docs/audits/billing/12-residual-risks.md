# Residual Risks & Operational Notes   Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Risiko Residual yang Diterima (Accepted Residual Risks)

| ID | Risiko Residual | Alasan Penerimaan / Justifikasi | Rencana Pemantauan |
|---|---|---|---|
| **RR-01** | Ketergantungan Eksternal Server TriPay | Jika server payment gateway TriPay mengalami gangguan jaringan (downtime), pembuatan transaksi baru akan mengalami error sementara. | Sistem menangani exception dengan aman (`try/catch`), menyimpan log error pada kolom `admin_notes`, dan menampilkan pesan ramah tanpa memicu HTTP 500. |
| **RR-02** | Kedaluwarsa Tagihan 15 Menit | Pengguna yang lambat melakukan transfer dalam 15 menit akan mendapati nomor tagihan dibatalkan otomatis. | Diperlukan demi menjaga ketersediaan slot transaksi gateway dan mencegah tagihan menggantung. Tautan re-order satu-klik disediakan. |
| **RR-03** | Rekonsiliasi Disk Storage Fisik Berat | Fitur pemindaian disk storage pada direktori tenant dengan puluhan ribu berkas dapat memakan waktu beberapa detik. | Dibatasi secara ketat hanya dapat dipicu oleh pemegang izin `billing.manage` atau Owner dengan jeda klik yang wajar. |

---

## 2. Catatan Pemeliharaan Berkala

1. **Pembersihan Log Transaksi Kadaluwarsa**: Disarankan menjadwalkan command terjadwal untuk mengarsipkan tagihan berstatus `cancelled` yang berusia lebih dari 90 hari.
2. **Pemantauan Kunci API Gateway**: Pastikan kredensial TriPay diperbarui secara berkala dan notifikasi saldo minimum deposit gateway dipantau oleh tim finance platform.
