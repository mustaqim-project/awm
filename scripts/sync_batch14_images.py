# scripts/sync_batch14_images.py
import os, shutil

SLUG_IMAGE_MAP = {
    719: ("aturan-upah-minimum-umkm-pasca-putusan-mk-pp-49-2025", "photo-1486406146926-c627a92ad1ab.jpg"),
    720: ("strategi-packing-gudang-harbolnas-12-12-alur-batch-shipping", "photo-1553413077-190dd305871c.jpg"),
    721: ("sop-stock-opname-tutup-buku-akhir-tahun-sak-emkm", "photo-1578916171728-46686eac8d58.jpg"),
    722: ("cara-menyusun-laporan-keuangan-sak-emkm-akhir-tahun", "photo-1554224154-26032ffc0d07.jpg"),
    723: ("manajemen-penagihan-piutang-b2b-akhir-tahun-anti-macet", "photo-1450133064473-71024230f91b.jpg"),
    724: ("mitigasi-retur-cod-marketplace-musim-hujan-cek-whatsapp", "photo-1611746872915-64382b5c76da.jpg"),
    725: ("kalkulasi-biaya-kemasan-musim-hujan-ke-hpp-produk", "photo-1501339847302-ac426a4a7cbb.jpg"),
    726: ("sinkronisasi-stok-kasir-pos-dan-marketplace-harbolnas-12-12", "photo-1556740738-b6a63e27c4df.jpg"),
    727: ("evaluasi-biaya-operasional-dan-overhead-akhir-tahun-umkm", "photo-1554224155-8d04cb21cd6c.jpg"),
    728: ("rekonsiliasi-settlement-marketplace-akhir-tahun-toko-online", "photo-1460925895917-afdab827c52f.jpg"),
    729: ("sop-serah-terima-kasir-shift-malam-dan-brankas-libur-nataru", "photo-1556742502-ec7c0e9f34b1.jpg"),
    730: ("strategi-promo-tukar-poin-loyalitas-member-akhir-tahun", "photo-1556740758-90de374c12ad.jpg"),
    731: ("sop-penerimaan-barang-retur-dan-klaim-supplier-toko", "photo-1553413077-190dd305871c.jpg"),
    732: ("cara-menghitung-target-omzet-dan-bep-cabang-baru-2027", "photo-1554224155-8d04cb21cd6c.jpg"),
    733: ("panduan-surat-jalan-dan-delivery-order-pengiriman-grosir-b2b", "photo-1454165804606-c3d57bc86b40.jpg"),
    734: ("strategi-menu-engineering-kafe-resto-evaluasi-akhir-tahun", "photo-1501339847302-ac426a4a7cbb.jpg"),
    735: ("sop-antrean-kasir-jam-sibuk-split-bill-struk-dapur-cepat", "photo-1556741533-411cf82e4e2d.jpg"),
    736: ("panduan-perpanjangan-sertifikat-halal-dan-izin-pirt-umkm", "photo-1589829545856-d10d557cf95f.jpg"),
    737: ("strategi-whatsapp-broadcast-tersegmentasi-promo-gajian-12-12", "photo-1611746872915-64382b5c76da.jpg"),
    738: ("manajemen-kasbon-karyawan-dan-potong-payroll-slip-gaji-digital", "photo-1519389950473-47ba0277781c.jpg"),
}

blog_dir = os.path.join("public", "images", "blog")
os.makedirs(blog_dir, exist_ok=True)

for art_id, (slug, src_name) in SLUG_IMAGE_MAP.items():
    src_path = os.path.join(blog_dir, src_name)
    dst_path = os.path.join(blog_dir, f"{slug}.jpg")
    if os.path.exists(src_path):
        shutil.copyfile(src_path, dst_path)
        print(f"[OK] Copied {src_name} -> {slug}.jpg ({os.path.getsize(dst_path)} bytes)")
    else:
        print(f"[ERR] Source image {src_name} not found!")

print(f"Synced all {len(SLUG_IMAGE_MAP)} Batch 14 images to public/images/blog/")
