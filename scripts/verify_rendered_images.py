import os
import csv
import sys
import hashlib
from PIL import Image

BASE_DIR = r"c:\laragon\www\cooca_core"
OUTPUT_DIR = os.path.join(BASE_DIR, "documentation", "feature-ui-images")

CATEGORIES = [
    ("A-kasir-operasional", 16),
    ("B-keamanan-otorisasi", 7),
    ("C-produksi-hpp", 9),
    ("D-penjualan-distribusi-b2b", 10),
    ("E-inventory-supply-chain", 10),
    ("F-finance-accounting", 10),
    ("G-ecommerce-digital-store", 8),
    ("H-whatsapp-social-media", 4),
    ("I-crm-customer-loyalty", 4),
    ("J-hrm-payroll", 5),
    ("K-ai-agent-automation", 3),
    ("L-analytics-business-intelligence", 4)
]

R3_TARGETS = [
    # A (16)
    "terminal-pos-modern.png", "pencarian-produk-instan.png", "multi-metode-pembayaran.png",
    "hold-resume-order.png", "manajemen-shift-kasir-uang-laci.png", "rekonsiliasi-kas-harian.png",
    "integrasi-printer-thermal.png", "pengiriman-struk-digital-whatsapp.png", "kitchen-display-system-kds.png",
    "alur-status-pesanan-dapur.png", "denah-meja-interaktif-dine-in.png", "pindah-gabung-meja.png",
    "split-bill.png", "katalog-tarif-jasa.png", "perintah-kerja-spk.png", "transaksi-jasa-sparepart-terintegrasi.png",
    # B (7)
    "otorisasi-pin-supervisor.png", "aturan-persetujuan-diskon.png", "approval-purchase-order-bertingkat.png",
    "approval-selisih-stock-opname.png", "audit-trail-keamanan-real-time.png",
    "manajemen-role-permission-granular-rbac.png", "matriks-hak-akses-karyawan.png",
    # C (9)
    "bill-of-materials-bom-resep-formula.png", "pengurangan-stok-bahan-baku-otomatis.png",
    "wastage-yield-calculation.png", "labor-cost-calculation.png", "machine-depreciation-rate.png",
    "simulator-margin-profitabilitas.png", "break-even-point-bep.png",
    "simulasi-sensitivitas-harga-bahan-baku.png", "varian-topping-modifiers.png",
    # D (10)
    "surat-penawaran-harga-quotation.png", "konversi-quotation-ke-sales-order.png",
    "pesanan-penjualan-grosir-sales-orders.png", "jadwal-pengiriman-bertahap-multi-drop.png",
    "faktur-penjualan-invoice-bertahap.png", "aging-receivables-piutang-usaha.png",
    "pengingat-tagihan-otomatis.png", "retur-penjualan-nota-kredit.png",
    "portal-pelanggan-b2b.png", "riwayat-tagihan-pelanggan-b2b.png",
    # E (10)
    "kartu-stok-otomatis-real-time.png", "peringatan-stok-minimum-low-stock-alerts.png",
    "multi-cabang-multi-lokasi-gudang.png", "surat-jalan-transfer-antar-cabang.png",
    "stock-opname-fisik-berkala.png", "database-riwayat-supplier.png",
    "purchase-order-po-pembelian.png", "penerimaan-barang-goods-receipts.png",
    "hutang-usaha-tagihan-vendor.png", "retur-pembelian-ke-supplier.png",
    # F (10)
    "pencatatan-kas-multi-rekening-bank.png", "mutasi-transfer-dana-internal.png",
    "manajemen-beban-operasional-expenses.png", "pembukuan-jurnal-otomatis-double-entry.png",
    "bagan-akun-standar-chart-of-accounts.png", "buku-besar-neraca-saldo.png",
    "laporan-neraca-posisi-keuangan.png", "laporan-laba-rugi-profit-loss.png",
    "rekonsiliasi-bank-settlement-edc-qris.png", "pengaturan-pajak-usaha-otomatis.png",
    # G (8)
    "toko-online-instan.png", "theme-branding-editor.png", "checkout-mandiri-notifikasi-pesanan.png",
    "cetak-label-pengiriman-shipping-label.png", "aturan-ongkos-kirim-fleksibel.png",
    "reservasi-booking-jadwal-online.png", "mini-bio-link-landing-page-builder.png",
    "hub-marketplace-terintegrasi.png",
    # H (4)
    "struk-digital-kasir-whatsapp.png", "whatsapp-blast-broadcast-promosi.png",
    "kalender-konten-media-sosial.png", "kotak-masuk-medsos-terpadu-social-inbox.png",
    # I (4)
    "database-pelanggan-terpusat.png", "program-loyalitas-tingkatan-member.png",
    "reward-poin-belanja.png", "kupon-voucher-diskon-promo.png",
    # J (5)
    "database-karyawan-multi-outlet.png", "roster-shift-jadwal-kerja.png",
    "presensi-gps-geofencing.png", "kasbon-pinjaman-karyawan.png",
    "kalkulator-penggajian-payroll-engine.png",
    # K (3)
    "mcp-connector-integrasi-multi-ai.png", "tanya-jawab-data-bisnis-real-time-ai.png",
    "automasi-perintah-bisnis-berbasis-ai.png",
    # L (4)
    "dashboard-eksekutif-real-time-ponsel.png", "analitik-jam-paling-ramai-peak-hours.png",
    "analitik-keranjang-belanja-produk-terlaris.png", "konsolidasi-laporan-multi-cabang.png"
]

MOBILE_FILES = {
    "presensi-gps-geofencing.png",
    "dashboard-eksekutif-real-time-ponsel.png"
}

def verify_all():
    print("=" * 70)
    print("COOCA 90-FEATURE UI AUDIT & RENDERING: AUTOMATED VERIFICATION")
    print("=" * 70)

    errors = []

    # 1. Verify category directories
    print("\n[1/5] Verifying 12 Category Subdirectories...")
    for cat, count in CATEGORIES:
        cat_dir = os.path.join(OUTPUT_DIR, cat)
        if not os.path.isdir(cat_dir):
            errors.append(f"Directory missing: {cat_dir}")
            print(f"  ❌ {cat}: MISSING")
        else:
            files_in_cat = [f for f in os.listdir(cat_dir) if f.endswith(".png")]
            if len(files_in_cat) != count:
                errors.append(f"Count mismatch in {cat}: found {len(files_in_cat)}, expected {count}")
                print(f"  ❌ {cat}: found {len(files_in_cat)} PNGs, expected {count}")
            else:
                print(f"  ✅ {cat}: exactly {count} PNGs present")

    # 2. Verify all 90 PNG files
    print("\n[2/5] Verifying 90 Individual PNG Images (Existence, Dimensions, Size)...")
    verified_png_count = 0
    total_bytes = 0

    for cat, _ in CATEGORIES:
        cat_dir = os.path.join(OUTPUT_DIR, cat)
        if not os.path.isdir(cat_dir):
            continue
        for fname in os.listdir(cat_dir):
            if not fname.endswith(".png"):
                continue
            fpath = os.path.join(cat_dir, fname)
            size = os.path.getsize(fpath)
            total_bytes += size

            if size == 0:
                errors.append(f"Empty image file: {fpath}")
                print(f"  ❌ {fname}: 0 BYTES")
                continue

            try:
                img = Image.open(fpath)
                expected_dims = (393, 852) if fname in MOBILE_FILES else (1600, 1000)
                if img.size != expected_dims:
                    errors.append(f"Dimension mismatch for {fname}: got {img.size}, expected {expected_dims}")
                    print(f"  ❌ {fname}: {img.size} != {expected_dims}")
                else:
                    verified_png_count += 1
            except Exception as e:
                errors.append(f"Corrupted image {fpath}: {e}")
                print(f"  ❌ {fname}: CORRUPTED ({e})")

    print(f"  -> Total verified PNG files: {verified_png_count} / 90")
    print(f"  -> Total image payload: {total_bytes / (1024*1024):.2f} MB")

    # Check against exact R3 list
    found_filenames = set()
    for cat, _ in CATEGORIES:
        cat_dir = os.path.join(OUTPUT_DIR, cat)
        if os.path.isdir(cat_dir):
            for f in os.listdir(cat_dir):
                if f.endswith(".png"):
                    found_filenames.add(f)

    missing_r3 = set(R3_TARGETS) - found_filenames
    extra_r3 = found_filenames - set(R3_TARGETS)
    if missing_r3:
        errors.append(f"Missing R3 filenames: {missing_r3}")
        print(f"  ❌ Missing target filenames: {missing_r3}")
    else:
        print("  ✅ All 90 filenames match R3 specification exactly (0 missing).")

    # 2b. Verify SHA-256 Cryptographic Uniqueness (0 Bit-for-Bit Duplicates)
    print("\n[2b/5] Verifying SHA-256 Cryptographic Uniqueness Across All 90 Images...")
    hashes = {}
    dup_errors = []
    for cat, _ in CATEGORIES:
        cat_dir = os.path.join(OUTPUT_DIR, cat)
        if not os.path.isdir(cat_dir):
            continue
        for fname in sorted(os.listdir(cat_dir)):
            if not fname.endswith(".png"):
                continue
            fpath = os.path.join(cat_dir, fname)
            with open(fpath, "rb") as f:
                h = hashlib.sha256(f.read()).hexdigest()
            if h in hashes:
                dup_errors.append(f"EXACT DUPLICATE DETECTED: {fname} matches {os.path.basename(hashes[h])} (SHA-256: {h})")
            hashes[h] = fpath

    if dup_errors:
        for err in dup_errors:
            errors.append(err)
            print(f"  ❌ {err}")
    else:
        print(f"  ✅ Exactly {len(hashes)} unique SHA-256 hashes verified across 90 PNGs (0 duplicates).")

    # 2c. Verify 256-Bit Pairwise Perceptual Difference (dHash Hamming Distance > 0)
    print("\n[2c/5] Verifying Pairwise Perceptual Difference (256-bit dHash)...")
    def calc_dhash(img_path, hash_size=16):
        with Image.open(img_path) as im:
            im = im.convert('L').resize((hash_size + 1, hash_size), Image.Resampling.LANCZOS)
            pixels = list(im.get_flattened_data() if hasattr(im, 'get_flattened_data') else im.getdata())
            return [pixels[r * (hash_size + 1) + c] > pixels[r * (hash_size + 1) + c + 1]
                    for r in range(hash_size) for c in range(hash_size)]

    all_png_paths = [os.path.join(OUTPUT_DIR, cat, f) for cat, _ in CATEGORIES for f in os.listdir(os.path.join(OUTPUT_DIR, cat)) if f.endswith('.png')]
    dhashes = {p: calc_dhash(p) for p in all_png_paths}
    clone_errors = []
    for i in range(len(all_png_paths)):
        for j in range(i + 1, len(all_png_paths)):
            p1, p2 = all_png_paths[i], all_png_paths[j]
            dist = sum(b1 != b2 for b1, b2 in zip(dhashes[p1], dhashes[p2]))
            if dist == 0:
                clone_errors.append(f"Perceptual clone (dHash distance == 0): {os.path.basename(p1)} vs {os.path.basename(p2)}")

    if clone_errors:
        for err in clone_errors:
            errors.append(err)
            print(f"  ❌ {err}")
    else:
        print(f"  ✅ All {len(all_png_paths) * (len(all_png_paths) - 1) // 2} pairwise combinations have dHash distance > 0 (0 perceptual clones).")

    # 3. Verify image-manifest.csv
    print("\n[3/5] Verifying image-manifest.csv...")
    csv_path = os.path.join(OUTPUT_DIR, "image-manifest.csv")
    expected_headers = [
        "feature_id", "category", "feature_name", "filename", "image_path",
        "source_blade", "related_files", "route", "rendering_method",
        "implementation_status", "viewport", "verification_status", "notes"
    ]

    if not os.path.isfile(csv_path):
        errors.append("image-manifest.csv missing!")
        print("  ❌ image-manifest.csv: MISSING")
    else:
        with open(csv_path, "r", encoding="utf-8") as f:
            reader = csv.reader(f)
            header = next(reader, None)
            rows = list(reader)

        if header != expected_headers:
            errors.append(f"Header mismatch in CSV: got {header}, expected {expected_headers}")
            print("  ❌ Header mismatch in CSV")
        else:
            print("  ✅ Exactly 13 required headers present in CSV")

        if len(rows) != 90:
            errors.append(f"Row count mismatch in CSV: found {len(rows)}, expected 90")
            print(f"  ❌ Found {len(rows)} rows, expected 90")
        else:
            print("  ✅ Exactly 90 feature rows present in CSV")

        # Verify no empty fields for critical columns
        for row_idx, r in enumerate(rows, 1):
            if len(r) != 13:
                errors.append(f"Row {row_idx} column count is {len(r)}, expected 13")
            if not r[0] or not r[1] or not r[2] or not r[3] or not r[4]:
                errors.append(f"Row {row_idx} has empty critical fields")

    # 3b. Verify Manifest Path Integrity (Zero Dead Links)
    print("\n[3b/5] Verifying Manifest Related Files Exist on Disk...")
    dead_links = []
    if os.path.isfile(csv_path):
        for row_idx, r in enumerate(rows, 1):
            rel_files = r[6]
            if rel_files:
                for p in rel_files.split(';'):
                    p = p.strip()
                    if p and not os.path.exists(os.path.join(BASE_DIR, p)):
                        dead_links.append(f"Row {row_idx} ({r[0]}): Dead link '{p}'")

    if dead_links:
        for err in dead_links:
            errors.append(err)
            print(f"  ❌ {err}")
    else:
        print("  ✅ All related_files paths in image-manifest.csv physically exist on disk (0 dead links).")

    # 4. Verify Technical Reports
    print("\n[4/5] Verifying Technical Documentation Reports...")
    reports = [
        "source-analysis-report.md",
        "generation-report.md",
        "missing-implementation-report.md"
    ]

    for rep in reports:
        rep_path = os.path.join(OUTPUT_DIR, rep)
        if not os.path.isfile(rep_path):
            errors.append(f"Missing report: {rep}")
            print(f"  ❌ {rep}: MISSING")
        else:
            size_kb = os.path.getsize(rep_path) / 1024
            if size_kb < 2.0:
                errors.append(f"Report {rep} suspiciously small: {size_kb:.1f} KB")
                print(f"  ❌ {rep}: TOO SMALL ({size_kb:.1f} KB)")
            else:
                print(f"  ✅ {rep}: {size_kb:.1f} KB (substantive)")

    # 5. Final Verdict
    print("\n" + "=" * 70)
    if len(errors) == 0:
        print("🎉 ALL ACCEPTANCE CRITERIA VERIFIED! 100% PASS (0 ERRORS)")
        print(f"  - 12 Category Subdirectories: Verified")
        print(f"  - 90 Target PNG Images: Verified (88 Desktop 1600x1000, 2 Mobile 393x852)")
        print(f"  - Cryptographic Uniqueness: 90 / 90 Unique SHA-256 Hashes (0 Duplicates)")
        print(f"  - Perceptual Authenticity: 4,005 Pairwise dHash Comparisons > 0 (0 Clones)")
        print(f"  - image-manifest.csv: Verified (13 Columns, 90 Rows, 0 Dead Links)")
        print(f"  - 3 Technical Markdown Reports: Verified")
        print("=" * 70)
        return True
    else:
        print(f"❌ VERIFICATION FAILED WITH {len(errors)} ERROR(S):")
        for err in errors:
            print(f"  - {err}")
        print("=" * 70)
        return False

if __name__ == "__main__":
    success = verify_all()
    sys.exit(0 if success else 1)
