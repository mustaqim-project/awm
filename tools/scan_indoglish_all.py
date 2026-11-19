import glob, os, re

indo_words = [
    'halaman', 'buka', 'tutup', 'kelola', 'hapus', 'simpan', 'tambah', 'lihat', 'cari', 'belum',
    'tidak', 'daftar', 'paket', 'harga', 'riwayat', 'catatan', 'pengguna', 'bisnis', 'pesanan',
    'laporan', 'masuk', 'keluar', 'kembali', 'ubah', 'ganti', 'pilih', 'semua', 'unggah', 'unduh',
    'rincian', 'bukti', 'permohonan', 'persetujuan', 'tercatat', 'ditemukan', 'terdaftar', 'muat',
    'ulang', 'bawaan', 'batas', 'kuota', 'fitur', 'pengunjung', 'mengunduh', 'kredensial', 'kunci',
    'antrean', 'arus', 'pulihkan', 'manfaat', 'tangguhkan', 'kosongkan', 'salinan', 'cadangan',
    'rekening', 'tayangan', 'ketentuan', 'syarat', 'tata', 'kelola', 'hibah', 'sumber', 'inspirasi',
    'penawaran', 'postingan', 'kata sandi', 'meja uji', 'otentik', 'kepemilikan', 'kasir', 'bahan baku',
    'gudang', 'faktur', 'pelanggan', 'pemasok', 'karyawan', 'pembayaran', 'stok'
]

flagged = {}
for path in glob.glob('lang/en/*.php'):
    fname = os.path.basename(path)
    with open(path, 'r', encoding='utf-8') as f:
        for idx, line in enumerate(f, 1):
            m = re.match(r'^\s*[\'\"]([a-zA-Z0-9_\-]+)[\'\"]\s*=>\s*[\'\"](.*)[\'\"],?\s*$', line)
            if m:
                k, val = m.group(1), m.group(2)
                lower = val.lower()
                hits = [w for w in indo_words if re.search(r'\b' + re.escape(w) + r'\b', lower)]
                if hits:
                    flagged.setdefault(fname, []).append((idx, k, val, hits))

for fname, items in flagged.items():
    print(f'{fname}: {len(items)} flagged lines')
