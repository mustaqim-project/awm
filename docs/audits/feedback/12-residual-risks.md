# Residual Risks - feedback

> Modul: Platform Feedback & Bug Support | Commit: 38cdcdf | Tanggal: 2026-10-25

## Analisis Risiko Residual Pasca-Audit

Meskipun seluruh temuan kritis dan perbaikan arsitektural telah diselesaikan dan diverifikasi dengan tes otomatis, terdapat beberapa risiko residual operasional yang perlu diperhatikan:

1. **Ekskalasi Tiket dengan Deskripsi Kurang Jelas:**
   - *Deskripsi:* Pemilik usaha mungkin mengirimkan laporan kendala tanpa menyertakan langkah mereproduksi (*steps to reproduce*) atau tangkapan layar.
   - *Tingkat Risiko:* Low (Operasional)
   - *Tindakan Mitigasi:* Sistem telah menyediakan placeholder form yang memandu serta petunjuk visual di halaman pembuatan laporan. Tim dukungan COOCA dapat merespons melalui linimasa update status untuk meminta keterangan tambahan.

2. **Lonjakan Ukuran Disk Lampiran pada Tenant Aktif Lama:**
   - *Deskripsi:* Akumulasi berkas screenshot lampiran bug yang menumpuk seiring berjalannya waktu.
   - *Tingkat Risiko:* Low (Infrastruktur)
   - *Tindakan Mitigasi:* Sistem telah terintegrasi dengan `OwnerStorageQuotaService` yang secara otomatis menghitung kuota pemakaian disk tenant, mencegah lonjakan tak terkendali.

3. **Keterbatasan Format Berkas Lampiran:**
   - *Deskripsi:* Pengguna mencoba mengunggah rekaman video (misal: format `.mp4` atau `.mov`) yang saat ini ditolak oleh validasi sistem (`mimes:jpg,jpeg,png,pdf`).
   - *Tingkat Risiko:* Informational / UX
   - *Rekomendasi Mendatang:* Mempertimbangkan integrasi penyimpanan video cloud atau layanan screencast eksternal (misal: Loom) untuk versi rilis mendatang jika dibutuhkan.
