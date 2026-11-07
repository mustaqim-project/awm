# Panduan Pemecahan Masalah (Troubleshooting) - Modul Tim AI Bisnis (`cooca-ai`)

> Modul: `cooca-ai` | Fokus: Diagnosis Gangguan, Kode Galat, & Langkah Solutif | Tanggal: 2026-11-07

---

## 1. Matriks Gejala Masalah & Solusi Cepat

| Gejala / Pesan Galat | Kemungkinan Penyebab | Langkah Solutif Mandiri |
| :--- | :--- | :--- |
| **"Belum ada provider AI aktif yang dikonfigurasi"** | Belum ada kunci API yang aktif atau kunci bawaan platform sedang dalam masa pemeliharaan. | Buka menu [`/cooca-ai/providers`](https://cooca.id/cooca-ai/providers). Pilih Google Gemini atau OpenAI, masukkan kunci API resmi Anda, lalu klik **Simpan & Aktifkan**. |
| **"Error 401: Invalid API Key / Unauthorized"** | Kunci API yang dimasukkan salah, kadaluwarsa, atau dicabut di konsol Google Cloud / OpenAI. | Buat kunci API baru di [Google AI Studio](https://aistudio.google.com/) atau konsol OpenAI, salin ulang ke COOCA tanpa spasi tambahan, dan klik **Uji Koneksi**. |
| **"Error 429: Rate limit exceeded / Quota reached"** | Kuota panggilan gratis per menit dari provider pihak ketiga telah habis. | Tunggu beberapa menit, atau aktifkan akun berbayar (*pay-as-you-go*) pada konsol provider Anda. Sistem COOCA akan otomatis mengaktifkan *Fallback Rule Engine* agar operasional kasir tidak terhenti. |
| **"Tombol Setujui Proposal tidak muncul atau tidak dapat diklik"** | Akun yang sedang login tidak memiliki hak otorisasi `ai.approve` atau proposal sudah pernah disetujui sebelumnya. | Pastikan Anda login menggunakan akun Pemilik Usaha (*Owner*) atau minta Superadmin memberikan izin `ai.approve` pada menu Manajemen Peran & Hak Akses. |
| **"Tampilan 3D Virtual Office terasa lambat atau patah-patah"** | Akselerasi perangkat keras (*Hardware Acceleration*) pada browser dinonaktifkan atau memori perangkat penuh. | Buka pengaturan browser (Chrome/Edge) $\rightarrow$ aktifkan *Use hardware acceleration when available*. Anda juga dapat mengklik tombol **Tampilan 2D Ringan** di pojok kanan bawah canvas. |

---

## 2. Prosedur Pemulihan Koneksi Provider Mandiri

Jika integrasi AI mendadak tidak merespons:
1. Buka halaman [`/cooca-ai/providers`](https://cooca.id/cooca-ai/providers).
2. Temukan kartu provider yang sedang aktif (ditandai dengan badge hijau *Aktif*).
3. Klik tombol **Uji Koneksi**.
4. Jika lampu indikator berubah menjadi hijau dengan pesan *"Koneksi berhasil terhubung"*, sistem siap digunakan kembali.
5. Jika gagal, periksa apakah metode pembayaran kartu kredit di akun Google Cloud/OpenAI Anda masih aktif.

---

## 3. Kapan Harus Menghubungi Technical Support COOCA?

Hubungi tim bantuan resmi jika:
- Hasil query AI menghasilkan galat basis data (*Database Exception*) yang persisten.
- Proposal tindakan yang sudah disetujui gagal mengubah status data transaksi.
- Tiket bantuan dapat dibuat langsung melalui menu [`/feedback/bugs/create`](https://cooca.id/feedback/bugs/create).

