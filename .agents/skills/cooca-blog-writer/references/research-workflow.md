# Alur riset keyword dan trend

Semua riset memakai `web_search` dan `web_fetch`. Tidak ada tool berbayar yang diasumsikan tersedia. Karena itu, jangan menyebut angka volume pencarian kecuali ada sumber yang terlihat.

## 1. Pemetaan topik
- Mulai dari masalah nyata pemilik UMKM Indonesia: hitung HPP, tentukan harga jual, stok berantakan, catatan kas, pajak UMKM, kasir, WhatsApp untuk jualan, promosi media sosial.
- Hubungkan ke fitur Cooca yang relevan dari Product Brief, tetapi pilih topik dari kebutuhan pembaca, bukan dari fitur.

## 2. Kueri pencarian (contoh pola)
Ganti `[topik]` dengan topik. Selalu pakai bahasa Indonesia sehari-hari yang diketik orang.
- `[topik]` (kueri inti)
- `cara [topik]`, `[topik] untuk UMKM`, `[topik] adalah`
- `[topik] excel` atau `template [topik]` bila relevan
- `[topik] 2026` atau `[topik] terbaru` untuk melihat tren dan perubahan aturan
- `kesalahan [topik]`, `contoh [topik]`
- Opsional: coba `web_fetch` ke `https://suggestqueries.google.com/complete/search?client=firefox&hl=id&q=[kata kunci]` untuk saran autocomplete. Jika gagal, lewati.

## 3. Yang dicatat dari tiap hasil
- Search intent: informasional, komersial, transaksional, atau navigasional
- Format dominan (panduan langkah, daftar, template, kalkulator)
- Struktur H2 dan H3 artikel peringkat atas
- Pertanyaan yang dijawab pesaing dan pertanyaan yang tidak mereka jawab
- Tahun atau tanggal pembaruan (indikator kesegaran)
- Celah: contoh angka rupiah konkret, studi kasus UMKM kecil, kesalahan umum, bahasa yang terlalu teknis

## 4. Menilai tren
- Prioritaskan sumber dalam 12 bulan terakhir. Sebut tahun hanya jika terverifikasi.
- Hal yang layak dicatat: perubahan regulasi (misalnya pajak UMKM), perilaku konsumen (pembayaran digital, QRIS, belanja lewat WhatsApp), musim (Ramadan, gajian, akhir tahun), perubahan platform.
- Tandai tiap tren dengan keyakinan: terverifikasi (ada sumber), indikatif (muncul di banyak hasil tanpa angka), atau spekulatif (jangan dipakai di artikel).

## 5. Output: Keyword Brief
```
Topik:
Primary keyword:
Secondary keywords (5-10):
Search intent:
Pembaca sasaran:
Angle unik (celah pesaing):
Tren yang dipakai (dengan tingkat keyakinan):
Pertanyaan FAQ (4-6):
Outline H2 awal:
```
Tampilkan Keyword Brief ke user secara ringkas bersama artikel final agar mudah dikoreksi.
