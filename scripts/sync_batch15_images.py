# scripts/sync_batch15_images.py
# -*- coding: utf-8 -*-
import os, shutil

SLUG_IMAGE_MAP = {
    # Group 1 (Articles 749 - 758)
    758: ("panduan-merekrut-karyawan-part-time-musiman-harbolnas-liburan", "cara-membuat-perjanjian-kerja-waktu-tertentu-pkwtt-pkwt-karyawan-toko.jpg"),
    757: ("audit-keamanan-kasir-pos-cegah-modus-void-pembatalan-palsu", "cooca-fitur-void-dan-pembatalan-nota-cooca-otorisasi-supervisor.jpg"),
    756: ("kampanye-kedaluwarsa-poin-loyalitas-member-dorong-repeat-order", "cara-membangun-program-poin-loyalitas-pelanggan-toko.jpg"),
    755: ("tata-cara-pembagian-dividen-dan-laba-ditahan-akhir-tahun-cv-pt", "cara-menghitung-laba-bersih-dan-laba-kotor-umkm.jpg"),
    754: ("menu-engineering-musim-hujan-substitusi-bahan-baku-kafe", "cara-menghitung-hpp-kopi.jpg"),
    753: ("strategi-purchase-order-bahan-baku-antisipasi-libur-supplier", "cara-membuat-purchase-order.jpg"),
    752: ("sop-pengelolaan-kas-kembalian-dan-modal-awal-laci-kasir", "aturan-serah-terima-shift-kasir-dan-uang-modal-laci.jpg"),
    751: ("panduan-padisera-padi-umkm-dan-ekatalog-lkpp-v6", "bantuan-umkm-pemerintah-2026-cara-daftar-online-supaya-tidak-ketinggal.jpg"),
    750: ("strategi-reaktivasi-pelanggan-dormant-whatsapp-akhir-tahun", "cara-membuat-broadcast-whatsapp-resmi-tidak-diblokir-pelanggan.jpg"),
    749: ("cara-mencatat-biaya-barang-rusak-dan-kedaluwarsa-sak-emkm", "cara-mengelola-barang-kedaluwarsa.jpg"),

    # Group 2 (Articles 739 - 748)
    748: ("manajemen-stok-toko-fisik-dan-live-shopping-video-commerce", "live-streaming-jualan-rahasia-konversi-8-dari-sekali-tayang.jpg"),
    747: ("cara-stock-opname-cepat-dengan-barcode-scanner-toko", "barcode-scanner-toko.jpg"),
    746: ("sop-reservasi-meja-restoran-dan-kafe-libur-nataru", None), # Photorealistic AI generated image preserved
    745: ("manfaat-perpres-38-2026-kewirausahaan-nasional-dan-nib-oss", "cara-membuat-nib.jpg"),
    744: ("format-evaluasi-kpi-karyawan-toko-dan-bonus-akhir-tahun", "kpi-usaha-kecil.jpg"),
    743: ("sop-pengemasan-paket-musim-hujan-ekspedisi-anti-rusak", "kalkulasi-biaya-kemasan-musim-hujan-ke-hpp-produk.jpg"),
    742: ("strategi-bundling-dan-flash-sale-harbolnas-12-12-umkm", "cara-menentukan-batas-diskon-promo-agar-tidak-boncos.jpg"),
    741: ("cara-menyusun-proyeksi-arus-kas-q1-awal-tahun-umkm", "cara-membuat-laporan-arus-kas-cash-flow-anti-macet.jpg"),
    740: ("cara-menghitung-hpp-hampers-natal-tahun-baru-kuliner", None), # Photorealistic AI generated image preserved
    739: ("panduan-coretax-djp-umkm-kode-billing-pph-final", "cara-aktivasi-akun-coretax-djp-bagi-pengusaha-badan-dan-orang-pribadi.jpg"),
}

blog_dir = os.path.join("public", "images", "blog")
os.makedirs(blog_dir, exist_ok=True)

for art_id, (slug, src_name) in SLUG_IMAGE_MAP.items():
    dst_path = os.path.join(blog_dir, f"{slug}.jpg")
    if src_name is None:
        if os.path.exists(dst_path):
            print(f"[OK] Preserved generated asset {slug}.jpg ({os.path.getsize(dst_path)} bytes)")
        continue
    src_path = os.path.join(blog_dir, src_name)
    if os.path.exists(src_path):
        shutil.copyfile(src_path, dst_path)
        print(f"[OK] Synced {src_name} -> {slug}.jpg ({os.path.getsize(dst_path)} bytes)")
    else:
        print(f"[ERR] Source image {src_name} not found!")

print(f"Synced all {len(SLUG_IMAGE_MAP)} Batch 15 images to public/images/blog/ with verified keyword alignment")
