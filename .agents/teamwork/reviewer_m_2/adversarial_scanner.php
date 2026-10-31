<?php
error_reporting(E_ALL);

$base_dir = dirname(__DIR__, 3);
$view_dirs = [
    $base_dir . '/resources/views/app',
    $base_dir . '/resources/views/admin'
];

$id_keywords = [
    'adalah', 'akan', 'antara', 'apakah', 'atau', 'bagaimana', 'bahkan', 'bahwa', 'batal', 'beberapa',
    'belum', 'berada', 'berapa', 'berhasil', 'berikut', 'bersama', 'bisa', 'boleh', 'bukan', 'dalam',
    'dan', 'dapat', 'dari', 'daripada', 'dengan', 'depan', 'di', 'dia', 'dimana', 'diri', 'disetujui',
    'ditolak', 'faktur', 'gagal', 'hanya', 'harus', 'hingga', 'ini', 'ingin', 'itu', 'jika', 'juga',
    'karena', 'kasir', 'ke', 'kembali', 'kemudian', 'kepada', 'keterangan', 'ketika', 'khusus', 'kita',
    'klien', 'kuota', 'lain', 'langsung', 'lebih', 'lihat', 'maka', 'mampu', 'masih', 'masuk', 'melalui',
    'memiliki', 'menambah', 'menghapus', 'mengubah', 'menolak', 'menunggu', 'menyetujui', 'merupakan',
    'metode', 'mungkin', 'nama', 'namun', 'oleh', 'pada', 'paling', 'para', 'pasti', 'pelanggan',
    'pemasok', 'pembayaran', 'pengaturan', 'pengguna', 'penjualan', 'percakapan', 'peringatan', 'periksa',
    'pesanan', 'pihak', 'produk', 'profil', 'proses', 'riwayat', 'saat', 'saja', 'sangat', 'saya',
    'sebagaimana', 'sebagai', 'sebelum', 'secara', 'sedang', 'sehingga', 'selain', 'selama', 'seluruh',
    'semua', 'seperti', 'serta', 'sesuai', 'setelah', 'setiap', 'siap', 'silakan', 'simpan', 'status',
    'stok', 'sudah', 'tagihan', 'tanpa', 'tapi', 'telah', 'tentang', 'terdapat', 'terhadap', 'terjadi',
    'termasuk', 'tersedia', 'tetapi', 'tidak', 'total', 'transaksi', 'tutup', 'ubah', 'untuk', 'wajib', 'yakin', 'yang'
];

$findings = [];

foreach ($view_dirs as $vdir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vdir));
    foreach ($it as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $path = $file->getPathname();
            $rel = str_replace($base_dir . DIRECTORY_SEPARATOR, '', $path);
            $lines = file($path);

            foreach ($lines as $lineNum => $line) {
                // Strip comments
                $clean = preg_replace('/\{\{--.*?--\}\}/', ' ', $line);
                // Strip Blade tags {{ ... }} and {!! ... !!}
                $clean = preg_replace('/\{\{.*?\}\}/', ' ', $clean);
                $clean = preg_replace('/\{!!.*?!!\}/', ' ', $clean);
                // Strip Blade directives @if, @php, @extends, etc.
                $clean = preg_replace('/@[a-zA-Z_]+(?:\([^\)]*\))?/', ' ', $clean);
                // Strip HTML tags
                $clean = preg_replace('/<[^>]+>/', ' ', $clean);
                $clean = trim($clean);

                if (empty($clean) || strlen($clean) < 4) {
                    continue;
                }

                // Check words against Indonesian dictionary
                $words = preg_split('/[^a-zA-Z]+/', strtolower($clean), -1, PREG_SPLIT_NO_EMPTY);
                if (count($words) < 2) {
                    continue;
                }

                $matches = array_intersect($words, $id_keywords);
                // If at least 2 distinct Indonesian words and >= 25% of words are Indonesian
                if (count($matches) >= 2 && (count($matches) / count($words)) >= 0.25) {
                    // Check if it's pure JS code or variable
                    if (str_contains($clean, 'function(') || str_contains($clean, 'const ') || str_contains($clean, 'let ')) {
                        continue;
                    }
                    $findings[] = [
                        'file' => $rel,
                        'line' => $lineNum + 1,
                        'text' => substr(trim($clean), 0, 100),
                        'matched_words' => array_values($matches)
                    ];
                }
            }
        }
    }
}

echo "Total raw Indonesian text lines detected: " . count($findings) . "\n\n";
foreach (array_slice($findings, 0, 30) as $f) {
    echo "{$f['file']}:{$f['line']}\n  Text: \"{$f['text']}\"\n  Matched: " . implode(', ', $f['matched_words']) . "\n\n";
}
