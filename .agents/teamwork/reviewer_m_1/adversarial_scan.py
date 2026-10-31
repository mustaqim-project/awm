import os, re, sys

ID_KEYWORDS = set([
    'dan', 'atau', 'yang', 'untuk', 'dengan', 'pada', 'ke', 'di', 'dari', 'ini', 'itu',
    'adalah', 'akan', 'bisa', 'dapat', 'tidak', 'belum', 'sudah', 'telah', 'sedang',
    'masuk', 'daftar', 'simpan', 'batal', 'hapus', 'ubah', 'edit', 'lihat', 'detail',
    'kembali', 'lanjut', 'kirim', 'proses', 'unggah', 'unduh', 'cari', 'filter',
    'pilih', 'tutup', 'buka', 'konfirmasi', 'berhasil', 'gagal', 'peringatan',
    'kesalahan', 'silakan', 'masukkan', 'nomor', 'nama', 'telepon', 'email', 'sandi',
    'pengaturan', 'akun', 'profil', 'keluar', 'halaman', 'hubungkan', 'metode', 'resmi',
    'panduan', 'bantuan', 'kontak', 'tentang', 'harga', 'paket', 'fitur', 'solusi',
    'layanan', 'produk', 'kategori', 'stok', 'pesanan', 'transaksi', 'laporan', 'kasir',
    'meja', 'struk', 'bayar', 'pelanggan', 'pemasok', 'karyawan', 'cabang', 'gudang',
    'kuota', 'gratis', 'percakapan', 'bulan', 'tahun', 'hari', 'jam', 'menit', 'detik',
    'apakah', 'anda', 'yakin', 'ingin', 'menghapus', 'menambah', 'memperbarui',
    'catatan', 'keterangan', 'status', 'total', 'subtotal', 'diskon', 'pajak', 'biaya',
    'pengiriman', 'alamat', 'kota', 'provinsi', 'kode', 'pos', 'pembayaran', 'tunai',
    'transfer', 'kartu', 'qris', 'riwayat', 'saldo', 'semua', 'aktif', 'nonaktif',
    'wajib', 'diisi', 'minimal', 'maksimal', 'karakter', 'digit',
    'kadaluarsa', 'verifikasi', 'otomatis', 'langsung', 'mudah', 'cepat', 'lengkap',
    'terbaik', 'terpercaya', 'bisnis', 'usaha', 'umkm', 'indonesia', 'sistem', 'aplikasi',
    'beranda', 'ringkasan', 'tata', 'kelola', 'otorisasi', 'bertingkat', 'disetujui',
    'ditolak', 'menunggu', 'persetujuan', 'pembuat', 'staf', 'alasan', 'sebelum'
])

def scan_text_nodes(filepath):
    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()

    # Remove Blade comments {{-- ... --}}
    content = re.sub(r'\{\{--.*?--\}\}', ' ', content, flags=re.DOTALL)
    # Remove script and style blocks
    content = re.sub(r'<script\b[^>]*>.*?</script>', ' ', content, flags=re.DOTALL | re.I)
    content = re.sub(r'<style\b[^>]*>.*?</style>', ' ', content, flags=re.DOTALL | re.I)
    # Remove PHP blocks <?php ... ?> and {{ ... }} and {!! ... !!}
    content = re.sub(r'<\?php.*?\?>', ' ', content, flags=re.DOTALL)
    content = re.sub(r'\{\{.*?\}\}', ' ', content, flags=re.DOTALL)
    content = re.sub(r'\{!!.*?!!\}', ' ', content, flags=re.DOTALL)
    # Remove Blade directives @directive(...)
    content = re.sub(r'@[a-zA-Z0-9_]+\s*(?:\((?:[^()]|\([^()]*\))*\))?', ' ', content)
    # Remove HTML tags <...>
    content = re.sub(r'<[^>]+>', ' ', content)

    # Now we have raw visible text
    lines = content.splitlines()
    raw_issues = []
    for idx, line in enumerate(lines, 1):
        clean = re.sub(r'[^a-zA-Z\s]', ' ', line.lower()).strip()
        words = clean.split()
        if not words:
            continue
        matches = [w for w in words if w in ID_KEYWORDS]
        # Ignore false positives like common english words (in, to, or, is, on, as)
        # Note: 'di', 'ke', 'dan', 'ini', 'itu', 'pada', 'dari'
        strong_id = [w for w in matches if w not in ['in', 'is', 'to', 'on', 'as', 'or']]
        if len(strong_id) >= 2 or (len(words) <= 4 and len(strong_id) >= 1 and strong_id[0] not in ['pos', 'email', 'status', 'total', 'detail', 'filter', 'edit', 'reset', 'login']):
            raw_issues.append((idx, line.strip(), strong_id))

    return raw_issues

dirs = ['resources/views/app', 'resources/views/admin']
total_found = 0
for d in dirs:
    for root, _, files in os.walk(d):
        for f in files:
            if f.endswith('.blade.php'):
                p = os.path.join(root, f)
                iss = scan_text_nodes(p)
                if iss:
                    total_found += len(iss)
                    print(f"=== {p} ({len(iss)} potential raw text lines) ===")
                    for line_no, text, matched in iss[:5]:
                        print(f"  Line {line_no}: [{', '.join(matched)}] {text[:80]}")

print(f"\nTotal potential unlocalized raw text lines: {total_found}")
