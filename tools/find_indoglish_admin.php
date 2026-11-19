<?php

$id = include __DIR__ . '/../lang/id/admin.php';
$en = include __DIR__ . '/../lang/en/admin.php';

$indoWords = [
    'halaman', 'buka', 'tutup', 'kelola', 'hapus', 'simpan', 'tambah', 'lihat', 'cari', 'belum',
    'tidak', 'daftar', 'paket', 'harga', 'riwayat', 'catatan', 'pengguna', 'bisnis', 'pesanan',
    'laporan', 'masuk', 'keluar', 'kembali', 'ubah', 'ganti', 'pilih', 'semua', 'unggah', 'unduh',
    'rincian', 'bukti', 'permohonan', 'persetujuan', 'tercatat', 'ditemukan', 'terdaftar', 'muat',
    'ulang', 'bawaan', 'batas', 'kuota', 'fitur', 'pengunjung', 'mengunduh', 'kredensial', 'kunci',
    'antrean', 'arus', 'pulihkan', 'manfaat', 'tangguhkan', 'kosongkan', 'salinan', 'cadangan',
    'rekening', 'tayangan', 'ketentuan', 'syarat', 'tata', 'kelola', 'hibah', 'sumber', 'inspirasi',
    'penawaran', 'postingan', 'kata sandi', 'meja uji', 'otentik', 'kepemilikan'
];

$flagged = [];
foreach ($en as $key => $val) {
    if (!is_string($val)) continue;
    $lower = strtolower($val);
    $matched = [];
    foreach ($indoWords as $w) {
        if (preg_match('/\b' . preg_quote($w, '/') . '\b/i', $lower)) {
            $matched[] = $w;
        }
    }
    if (!empty($matched)) {
        $flagged[$key] = [
            'id' => $id[$key] ?? '',
            'en' => $val,
            'words' => $matched,
        ];
    }
}

file_put_contents(__DIR__ . '/indoglish_list.json', json_encode($flagged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo "Saved " . count($flagged) . " items to tools/indoglish_list.json\n";
