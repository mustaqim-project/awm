---
name: business-owner-doc-generator
description: Membuat dan memperbarui dokumentasi panduan cara penggunaan sistem (user guideline) dari sudut pandang (POV) Bisnis Owner untuk 20 jenis usaha berbeda di COOCA. Dilengkapi tautan langsung (link/route) ke fitur dan tutorial terkait, bahasa yang bersahaja dan mudah dipahami UMKM tanpa jargon rumit, serta matriks implementasi praktis lintas 20 sektor industri. Aktifkan setiap kali user meminta pembuatan buku panduan, dokumentasi modul, guideline penggunaan, SOP fitur, atau update docs di resources/views/app/docs/.
---

# BUSINESS OWNER DOCUMENTATION GENERATOR (20 INDUSTRI BERBEDA)

Skill operasional ini memandu AI Agent dalam menganalisis modul/fitur antarmuka di repositori COOCA (misal: `resources/views/app/{modul}`) dan menghasilkan **Buku Panduan & Dokumentasi Penggunaan (User Guidelines)** yang berorientasi langsung pada kebutuhan nyata **Pemilik Usaha (Business Owner)** dari **20 jenis industri yang berbeda**.

---

## 🎯 1. Filosofi & Sudut Pandang (Business Owner POV)

Dokumentasi COOCA **bukan dokumentasi teknis programmer** dan **bukan manual instruksi kaku**, melainkan **buku panduan navigasi bisnis praktis**:

1. **Jawab 3 Pertanyaan Utama Pemilik Usaha**:
   - *Apa masalah bisnis saya yang diselesaikan oleh fitur ini?* (Misal: "Mencegah kasir curang", "Otomatis hitung HPP agar tidak rugi jualan", "Menghemat 3 jam waktu rekap pembukuan tiap malam").
   - *Bagaimana cara memakainya dalam 3-5 langkah mudah tanpa pusing?*
   - *Bagaimana fitur ini bekerja spesifik untuk jenis usaha saya?* (Restoran, Bengkel, Konveksi, Laundry, Toko Obat, dll.).
2. **Bahasa Lugas, Hangat & Bebas Jargon IT**:
   - ❌ *Jargon:* "Execute real-time inference query via LLM token routing endpoint."
   - ✅ *Bahasa Owner:* "Ketik pertanyaan seperti mengobrol biasa dengan asisten pintar Anda untuk mengetahui omzet hari ini atau produk yang hampir habis."
3. **Tautan Interaktif Langsung (Actionable Links)**:
   - Setiap panduan **wajib menyediakan tautan/tombol langsung** ke modul atau formulir terkait (`route('...')`), sehingga pemilik usaha tidak perlu mencari-cari menu secara manual.
4. **Matriks Relevansi 20 Jenis Usaha**:
   - Setiap modul wajib diuraikan contoh penerapannya pada **20 jenis industri** di COOCA, agar pemilik restoran, bengkel, toko baju, hingga katering langsung paham cara menggunakannya di tempat usahanya masing-masing.

---

## 🗺️ 2. Peta 6 Klaster & 20 Sektor Industri COOCA

Sistem COOCA dirancang melayani 20 model bisnis UMKM Indonesia yang terbagi dalam 6 klaster:

| Klaster Industri | 20 Jenis Usaha | Kebutuhan Kunci Pemilik Bisnis |
|---|---|---|
| **1. Kuliner & F&B** | 1. Restoran Dine-In<br>2. Coffee Shop & Cafe<br>3. Bakery & Toko Roti<br>4. Cloud Kitchen Online<br>5. Katering Prasmanan | Manajemen meja, tiket dapur (KDS), resep BOM gramasi bumbu, varian level pedas/manis, pesanan terjadwal, pengingat DP katering. |
| **2. Manufaktur & HPP** | 6. Konveksi & Garment<br>7. Percetakan & Offset Digital<br>8. Mebel & Furniture Kayu<br>9. Bengkel Bubut & Logam<br>10. Kerajinan Tangan / Craft | Perhitungan HPP multi-tier (bahan baku + ongkos tukang + susut mesin), estimasi lead time produksi, termin DP pesanan B2B. |
| **3. Ritel & Apotek** | 11. Minimarket & Kelontong<br>12. Apotek & Toko Obat | Scan barcode cepat, multi-satuan (Dus/Renteng/Pcs), batas minimum stok, nomor batch & masa kadaluarsa (Expired Date). |
| **4. Jasa Harian** | 13. Bengkel Mobil & Motor<br>14. Barbershop & Salon<br>15. Laundry Kiloan & Satuan<br>16. Cuci Mobil & Detailing | Form PKB / SPK nopol kendaraan, komisi terapis/mekanik, timbangan kg & nomor loker rak pakaian, riwayat servis pelanggan. |
| **5. Jasa Proyek** | 17. Kontraktor & Renovasi<br>18. Event / Wedding Organizer<br>19. Digital IT & Creative Agency | Surat penawaran (Quotation), penagihan bertahap per termin progress, pelacakan budget belanja material vs pengeluaran riil. |
| **6. Distribusi & Agro** | 20. Distributor Grosir B2B & Agrobisnis | Surat jalan kirim barang, batas jatuh tempo tempo hutang/piutang toko mitra, stok panen musiman, multi-gudang cabang. |

---

## 📋 3. Struktur Baku Dokumentasi per Modul

Saat mendokumentasikan suatu modul ke dalam `resources/views/app/docs/`:
- 1. Header & Quick Links
- 2. Ringkasan Eksekutif (Manfaat Nyata bagi Bisnis Owner)
- 3. Alur Kerja Langkah demi Langkah (3-4 Langkah Praktis)
- 4. Matriks Penerapan untuk 20 Jenis Usaha Berbeda
- 5. Keamanan, Otorisasi & Anti-Fraud
- 6. Tanya Jawab Bisnis Owner (FAQ)
