# scripts/harmonize_40_articles.py
# -*- coding: utf-8 -*-
"""
Harmonizes all 40 evergreen articles to achieve strict 0 FAIL and 0 WARN:
1. Exact Primary Keyword matching across H1, first 100 words, meta_title, meta_description, and at least one H2.
2. Exact meta_title (<=60 chars) and meta_description (120-160 chars) lengths.
3. Valid internal links (3-5 unique links from blog_links.json).
4. Zero em dash (U+2014) and zero en dash (U+2013).
5. Body word count >= 1500.
6. FAQ section present in H2.
"""

import re

def harmonize_article(art):
    aid = art["id"]
    slug = art["slug"]
    pk = art["primary_keyword"].lower()
    body = art["body"]

    # -------------------------------------------------------------
    # Pillar 1 (Articles 1-8)
    # -------------------------------------------------------------
    if aid == 1:
        # Replace unapproved link with approved link
        body = body.replace(
            "https://cooca.id/blog/menentukan-batas-diskon-promo-agar-tidak-boncos",
            "https://cooca.id/blog/cara-hitung-diskon-agar-tidak-rugi"
        )
        body = body.replace(
            "[menentukan batas diskon promo agar tidak boncos]",
            "[cara hitung diskon agar tidak rugi]"
        )
        # Update H2 to include primary keyword
        body = body.replace(
            "## Konsep Dasar dan Rumus Contribution Margin bagi Pemilik Usaha",
            "## Konsep Dasar dan Cara Menghitung Contribution Margin Produk"
        )
    elif aid == 2:
        body = body.replace(
            "## Pengertian dan Manfaat Rasio Likuiditas bagi UMKM",
            "## Pengertian dan Manfaat Analisis Rasio Likuiditas Usaha Kecil"
        )
    elif aid == 3:
        body = body.replace(
            "## Panduan Langkah Demi Langkah Menyusun Anggaran Tahunan",
            "## Panduan Cara Membuat Anggaran Operasional Tahunan UMKM"
        )
        # Add 3rd internal link
        target_str = "1. Mencampur Kebutuhan Rumah Tangga dengan Kas Toko: Pemilik mengambil uang di laci kasir sesuka hati untuk belanja pribadi tanpa mencatatnya sebagai prive. Hal ini langsung merusak seluruh struktur anggaran yang telah dibuat."
        repl_str = "1. Mencampur Kebutuhan Rumah Tangga dengan Kas Toko: Pemilik mengambil uang di laci kasir sesuka hati untuk belanja pribadi tanpa mencatatnya sebagai prive. Pelajari panduan [cara memisahkan rekening pribadi dan rekening bisnis UMKM](https://cooca.id/blog/cara-memisahkan-rekening-pribadi-dan-rekening-bisnis-umkm) agar arus kas operasional tidak tercampur aduk dan merusak anggaran."
        body = body.replace(target_str, repl_str)
    elif aid == 4:
        body = body.replace(
            "## Prinsip Dasar Manajemen Piutang pada Usaha Dagang",
            "## Prinsip Dasar Manajemen Piutang Usaha Dagang"
        )
        # Add 3rd internal link
        if "sop-rekonsiliasi-bank-pencocokan-mutasi-rekening-toko" not in body:
            target_str = "Surat konfirmasi saldo piutang dikirimkan secara berkala"
            repl_str = "Surat konfirmasi saldo piutang dicocokkan dengan [SOP rekonsiliasi bank pencocokan mutasi rekening toko](https://cooca.id/blog/sop-rekonsiliasi-bank-pencocokan-mutasi-rekening-toko) secara berkala"
            if target_str in body:
                body = body.replace(target_str, repl_str)
            else:
                body = body.replace("## Rekonsiliasi Saldo Piutang Berkala (Confirmation of Balance)", "## Rekonsiliasi Saldo Piutang Berkala (Confirmation of Balance)\n\nLakukan pencocokan berkala dengan [SOP rekonsiliasi bank pencocokan mutasi rekening toko](https://cooca.id/blog/sop-rekonsiliasi-bank-pencocokan-mutasi-rekening-toko) agar pencatatan pelunasan faktur valid.")
    elif aid == 5:
        body = body.replace(
            "## Konsep Dasar Metode Garis Lurus (Straight Line Method)",
            "## Konsep Dasar dan Cara Menghitung Depresiasi Aset Tetap"
        )
    elif aid == 6:
        body = body.replace(
            "## Komponen Modal Awal Usaha yang Wajib Diidentifikasi",
            "## Komponen dan Cara Mencatat Modal Awal Usaha"
        )
    elif aid == 7:
        body = body.replace(
            "## Konsep Dasar Rumus ROI dan Payback Period",
            "## Konsep dan Cara Menghitung ROI Investasi Usaha"
        )
    elif aid == 8:
        body = body.replace(
            "## Prinsip Pengendalian Internal Klaim Biaya Karyawan",
            "## Prinsip dan SOP Pencatatan Reimbursement Biaya Operasional"
        )

    # -------------------------------------------------------------
    # Pillar 2 (Articles 9-16)
    # -------------------------------------------------------------
    elif aid == 10:
        body = body.replace(
            "## Klasifikasi Tiga Kelompok Stok dalam Analisis ABC",
            "## Klasifikasi dan Analisis ABC Persediaan Barang Toko"
        )
    elif aid == 11:
        body = body.replace(
            "## Konsep Dasar dan Rumus Inventory Turnover Ratio (ITR)",
            "## Konsep dan Cara Menghitung Inventory Turnover Ratio"
        )
    elif aid == 12:
        body = body.replace(
            "## Prinsip Dasar Pengelolaan Barang Cacat dan Expired",
            "## Prinsip dan SOP Karantina Barang Rusak dan Kedaluwarsa"
        )
    elif aid == 13:
        body = body.replace(
            "## Prinsip Alur Gerak Barang: Mencegah Kemacetan Arus Kerja",
            "## Prinsip dan Cara Menata Layout Gudang Toko Kecil"
        )
    elif aid == 14:
        body = body.replace(
            "## Prinsip Pengendalian Internal Mutasi Barang Antar Cabang",
            "## Prinsip dan SOP Transfer Stok Antar Cabang Toko"
        )
    elif aid == 15:
        body = body.replace(
            "## Mengapa Bisnis Kuliner dan Manufaktur Membutuhkan Buffer Stock?",
            "## Urgensi dan Cara Menghitung Buffer Stock Bahan Baku"
        )
    elif aid == 16:
        body = body.replace(
            "## Tiga Tingkatan Pelabelan Barcode dalam Gudang Ritel",
            "## Standar dan Metode Barcode Labeling Inventori Gudang"
        )

    # -------------------------------------------------------------
    # Pillar 3 (Articles 17-24)
    # -------------------------------------------------------------
    elif aid == 17:
        body = body.replace(
            "## Mengapa Laci Kasir Tidak Boleh Menyimpan Terlalu Banyak Uang?",
            "## Urgensi dan SOP Blind Cash Drop Kasir Toko"
        )
    elif aid == 18:
        art["title"] = "Pencegahan Fraud Kasir Metode Phantom Transaction dan Void Struk"
        art["meta_title"] = "Pencegahan Fraud Kasir Metode Phantom Transaction"
        art["meta_description"] = "Pelajari pencegahan fraud kasir metode phantom transaction, manipulasi void struk, dan audit log POS untuk melindungi arus kas toko Anda."
        # Update H1
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        # Ensure PK is in first 100 words
        intro_target = "Dalam bisnis ritel, kafe, restoran, dan toko kelontong modern, kasir adalah garda terdepan interaksi finansial langsung dengan pelanggan."
        intro_repl = "Dalam bisnis ritel, kafe, dan restoran modern, pencegahan fraud kasir metode phantom transaction merupakan prioritas utama pemilik toko untuk mencegah kebocoran kasir."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        # Update H2
        body = body.replace(
            "## Membedah Anatomi Modus Phantom Transaction di Meja Kasir",
            "## Anatomi dan Pencegahan Fraud Kasir Metode Phantom Transaction"
        )
        # Reduce body keyword occurrence by 1 so density <= 2.5%
        body = body.replace(
            "Pencegahan fraud kasir metode phantom transaction bukan tentang bersikap curiga tanpa dasar kepada karyawan",
            "Pencegahan fraud kasir dan modus phantom transaction bukan tentang bersikap curiga tanpa dasar kepada karyawan"
        )
    elif aid == 19:
        body = body.replace(
            "## Mengapa Uang Kasir Bisa Mengalami Selisih Kurang atau Lebih?",
            "## Penyebab dan SOP Penanganan Selisih Uang Kasir Toko"
        )
    elif aid == 20:
        body = body.replace(
            "## Tantangan Operasional dan Risiko Salah Catat Split Payment",
            "## Panduan dan Alur Transaksi Split Payment Kasir POS"
        )
    elif aid == 21:
        body = body.replace(
            "## Perbedaan Mendasar Antara Void, Retur, dan Refund",
            "## Konsep dan SOP Pembatalan Pesanan Void Kasir POS"
        )
    elif aid == 22:
        art["title"] = "Cara Audit Log Kasir POS untuk Deteksi Transaksi Mencurigakan"
        art["meta_title"] = "Cara Audit Log Kasir POS Deteksi Transaksi Curiga"
        art["meta_description"] = "Panduan cara audit log kasir POS untuk mendeteksi transaksi mencurigakan, pembatalan sepihak, buka laci manual, dan kebocoran kas toko."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Audit log kasir POS merekam setiap jejak aktivitas digital yang terjadi di layar kasir secara real time."
        intro_repl = "Penerapan cara audit log kasir POS merekam setiap jejak aktivitas digital yang terjadi di layar kasir secara real time untuk mendeteksi anomali."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## 5 Pola Transaksi Mencurigakan yang Wajib Dicari di Audit Log",
            "## 5 Pola dan Cara Audit Log Kasir POS Toko Ritel"
        )
    elif aid == 23:
        art["title"] = "SOP Kas Kecil Kasir Laci Kas: Modal Awal dan Uang Kembalian"
        art["meta_title"] = "SOP Kas Kecil Kasir Laci Kas dan Uang Kembalian"
        art["meta_description"] = "Standar SOP kas kecil kasir laci kas untuk menentukan modal awal uang kembalian pecahan dan prosedur serah terima pergantian shift toko."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Kas kecil kasir atau float money adalah sejumlah uang tunai pecahan yang disiapkan di dalam laci kasir"
        intro_repl = "Penerapan SOP kas kecil kasir laci kas memastikan sejumlah uang tunai pecahan disiapkan di dalam laci kasir"
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## Menentukan Jumlah Nominal Modal Awal Laci yang Ideal",
            "## Ketentuan SOP Kas Kecil Kasir Laci Kas dan Modal Awal"
        )
    elif aid == 24:
        art["meta_description"] = "Panduan integrasi EDC bank kasir POS toko ritel dan SOP rekonsiliasi harian slip gesek kartu debit kredit agar mutasi rekening bank akurat."
        body = body.replace(
            "## Cara Kerja Integrasi Mesin EDC dengan Aplikasi Kasir POS",
            "## Cara Kerja dan Panduan Integrasi EDC Bank Kasir POS"
        )

    # -------------------------------------------------------------
    # Pillar 4 (Articles 25-32)
    # -------------------------------------------------------------
    elif aid == 25:
        body = body.replace(
            "## Mengenal Struktur 5 Digit Kode KBLI 2020",
            "## Struktur dan Cara Memilih Kode KBLI Usaha UMKM"
        )
    elif aid == 26:
        body = body.replace(
            "## Kriteria Produk Pangan yang Boleh Menggunakan Izin PIRT",
            "## Kriteria dan Syarat PIRT Pangan Olahan Risiko Rendah"
        )
    elif aid == 27:
        body = body.replace(
            "## Memahami Aturan Main PPh Final 0,5% dan Batas Bebas Pajak Rp500 Juta",
            "## Regulasi dan Tata Cara Pencatatan Pajak Peredaran Bruto UMKM"
        )
    elif aid == 28:
        art["title"] = "Cara Membuat Perjanjian Kerja Waktu Tertentu UMKM untuk Karyawan Toko"
        art["meta_title"] = "Cara Membuat Perjanjian Kerja Waktu Tertentu UMKM"
        art["meta_description"] = "Format dan cara membuat perjanjian kerja waktu tertentu UMKM untuk karyawan toko, klausul kontrak kerja, masa percobaan, upah, dan hak staf."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Hubungan kerja antara pemilik usaha dan staf toko atau gudang seringkali hanya dilandasi atas kesepakatan lisan tanpa dokumen tertulis."
        intro_repl = "Panduan cara membuat perjanjian kerja waktu tertentu UMKM sangat krusial agar hubungan kerja pemilik usaha dan staf toko dilandasi kontrak resmi."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## Syarat Sah PKWT Sesuai UU Cipta Kerja dan PP 35 Tahun 2021",
            "## Syarat dan Cara Membuat Perjanjian Kerja Waktu Tertentu UMKM"
        )
        body = body.replace(
            "Mengetahui cara membuat perjanjian kerja waktu tertentu umkm adalah bukti kedewasaan",
            "Mengetahui cara membuat perjanjian kerja waktu tertentu bagi UMKM adalah bukti kedewasaan"
        )
    elif aid == 29:
        art["title"] = "Prosedur Sertifikasi Halal SJPH UMKM: Panduan Manual Sistem Jaminan"
        art["meta_title"] = "Prosedur Sertifikasi Halal SJPH UMKM Manual Sistem"
        art["meta_description"] = "Langkah mudah prosedur sertifikasi halal SJPH UMKM, cara membuat manual Sistem Jaminan Produk Halal, penunjukan penyelia halal, dan audit bahan."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Kewajiban sertifikasi halal bagi produk makanan dan minuman kini telah menjadi keharusan hukum di Indonesia."
        intro_repl = "Memahami prosedur sertifikasi halal SJPH UMKM kini telah menjadi keharusan hukum bagi seluruh pengusaha makanan dan minuman di Indonesia."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## 5 Kriteria Utama Sistem Jaminan Produk Halal (SJPH)",
            "## 5 Kriteria dalam Prosedur Sertifikasi Halal SJPH UMKM"
        )
    elif aid == 30:
        art["meta_title"] = "Cara Aktivasi Akun Coretax DJP bagi Pengusaha UMKM"
        body = body.replace(
            "## Mengenal Fitur Unggulan Coretax DJP Dibanding Sistem Lama",
            "## Fitur dan Cara Aktivasi Akun Coretax DJP bagi Pengusaha"
        )
    elif aid == 31:
        body = body.replace(
            "## Kedudukan Hukum Nota Penjualan dalam KUHPerdata",
            "## Kedudukan dan Aturan Nota Penjualan Berkekuatan Hukum"
        )
    elif aid == 32:
        art["title"] = "Pembukuan Sederhana SAK EMKM untuk Usaha Kecil: 3 Laporan Wajib"
        art["meta_title"] = "Pembukuan Sederhana SAK EMKM untuk Usaha Kecil"
        art["meta_description"] = "Standar pembukuan sederhana SAK EMKM untuk usaha kecil: laporan posisi keuangan, laporan laba rugi, dan catatan atas laporan keuangan resmi."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Banyak pemilik usaha mikro, kecil, dan menengah menganggap bahwa standar akuntansi keuangan hanya diperuntukkan bagi perseroan terbuka atau korporasi multinasional."
        intro_repl = "Standar pembukuan sederhana SAK EMKM untuk usaha kecil dirancang khusus agar UMKM dapat menyusun laporan keuangan yang diakui perbankan dan investor."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## Mengapa UMKM Wajib Beralih Menggunakan SAK EMKM?",
            "## Standar Pembukuan Sederhana SAK EMKM untuk Usaha Kecil"
        )

    # -------------------------------------------------------------
    # Pillar 5 (Articles 33-40)
    # -------------------------------------------------------------
    elif aid == 33:
        art["title"] = "Cara Menghitung Customer Lifetime Value UMKM Ritel dan Kafe"
        art["meta_title"] = "Cara Menghitung Customer Lifetime Value UMKM Ritel"
        art["meta_description"] = "Panduan cara menghitung customer lifetime value UMKM ritel dan kafe, rumus CLV dan CAC untuk mengukur loyalitas pelanggan toko Anda."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Sebagian besar pebisnis ritel dan pemilik kafe menghabiskan anggaran pemasaran yang besar untuk menjaring pelanggan baru."
        intro_repl = "Memahami cara menghitung customer lifetime value UMKM ritel dan kafe membantu pebisnis mengukur nilai jangka panjang setiap pelanggan setia."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## Mengapa Menghitung CLV Sangat Krusial Dibandingkan Sekadar Omzet?",
            "## Urgensi dan Cara Menghitung Customer Lifetime Value UMKM"
        )
    elif aid == 34:
        body = body.replace(
            "## Mengapa Membership Bertingkat Jauh Lebih Ampuh Dibanding Diskon Biasa?",
            "## Keunggulan Strategi Membership Tier Loyalitas Pelanggan"
        )
    elif aid == 35:
        art["title"] = "Alur Follow Up Keranjang Terbengkalai WhatsApp Katalog Toko"
        art["meta_title"] = "Alur Follow Up Keranjang Terbengkalai WhatsApp Toko"
        art["meta_description"] = "Trik alur follow up keranjang terbengkalai WhatsApp dari katalog toko online, template pesan ramah, dan timing pengingat checkout otomatis."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Dalam bisnis e-commerce dan toko online berbasis WhatsApp katalog, salah satu kebocoran omzet terbesar adalah fenomena keranjang belanja terbengkalai"
        intro_repl = "Menerapkan alur follow up keranjang terbengkalai WhatsApp katalog toko secara tepat dapat menyelamatkan potensi omzet penjualan hingga 30 persen."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## Mengapa Calon Pembeli Membatalkan Pesanan di Detik Terakhir?",
            "## Pola Masalah dan Alur Follow Up Keranjang Terbengkalai WhatsApp"
        )
    elif aid == 36:
        art["meta_title"] = "SOP Penanganan Ulasan Negatif di Google Maps Toko"
        body = body.replace(
            "## Anatomi Psikologis Konsumen di Balik Komplain Bintang 1",
            "## Prinsip dan SOP Penanganan Ulasan Negatif di Google Maps"
        )
    elif aid == 37:
        body = body.replace(
            "## Psikologi di Balik Keberhasilan Skema Insentif Dua Arah (Two-Sided Referral)",
            "## Skema dan Cara Membuat Program Referral Pelanggan Bawa Teman"
        )
    elif aid == 38:
        art["title"] = "Segmentasi Database Pelanggan Berdasarkan RFM untuk Toko Ritel"
        art["meta_title"] = "Segmentasi Database Pelanggan Berdasarkan RFM Toko"
        art["meta_description"] = "Panduan segmentasi database pelanggan berdasarkan RFM recency frequency monetary untuk promosi toko yang tepat sasaran dan hemat biaya."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Banyak pemilik toko ritel memperlakukan seluruh pelanggan dalam database mereka secara seragam."
        intro_repl = "Penerapan segmentasi database pelanggan berdasarkan RFM recency frequency monetary membagi pembeli ke dalam kelompok perilaku yang presisi."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## Tiga Dimensi Utama dalam Model Analisis RFM",
            "## Tiga Dimensi Segmentasi Database Pelanggan Berdasarkan RFM"
        )
    elif aid == 39:
        art["title"] = "Cara Membuat Broadcast WhatsApp Resmi Tidak Diblokir Pelanggan"
        art["meta_title"] = "Cara Membuat Broadcast WhatsApp Resmi Tidak Diblokir"
        art["meta_description"] = "Ketahui cara membuat broadcast WhatsApp resmi tidak diblokir pelanggan, aturan opt in kontak, jadwal broadcast aman, dan personalisasi pesan."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "WhatsApp adalah saluran komunikasi paling intim dan memiliki tingkat keterbacaan (open rate) tertinggi dibandingkan kanal digital lainnya."
        intro_repl = "Pelajari cara membuat broadcast WhatsApp resmi tidak diblokir pelanggan agar pesan promosi dan edukasi toko diterima dengan senang hati."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## Mengapa Nomor WhatsApp Bisnis Toko Bisa Diblokir (Banned)?",
            "## Risiko Banned dan Cara Membuat Broadcast WhatsApp Resmi Tidak Diblokir"
        )
    elif aid == 40:
        art["title"] = "Strategi Cross Selling dan Up Selling di Kasir Ritel dan FnB"
        art["meta_title"] = "Strategi Cross Selling dan Up Selling di Kasir"
        art["meta_description"] = "Trik strategi cross selling dan up selling di kasir ritel dan FnB untuk menaikkan nilai transaksi belanja rata-rata tanpa membuat pembeli risih."
        body = re.sub(r"^# .+$", "# " + art["title"], body, count=1, flags=re.M)
        intro_target = "Titik kasir atau meja pembayaran adalah momen paling strategis dalam perjalanan belanja konsumen (customer journey)."
        intro_repl = "Penerapan strategi cross selling dan up selling di kasir ritel dan FnB mampu mendongkrak rata-rata nilai belanja per transaksi pembeli."
        if intro_target in body:
            body = body.replace(intro_target, intro_repl)
        body = body.replace(
            "## Memahami Perbedaan Mendasar Antara Cross-Selling dan Up-Selling",
            "## Pengertian dan Strategi Cross Selling dan Up Selling di Kasir"
        )

    # Clean dashes just in case
    body = body.replace("\u2014", " - ").replace("\u2013", "-")
    art["body"] = body
    return art

def harmonize_all(articles):
    return [harmonize_article(a) for a in articles]
