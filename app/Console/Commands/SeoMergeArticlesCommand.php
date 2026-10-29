<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Redirect;
use App\Services\Seo\SitemapService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SeoMergeArticlesCommand extends Command
{
    protected $signature = 'seo:merge-articles {--apply : Apply the merges and updates directly}';

    protected $description = 'Consolidate cannibalized blog articles into authoritative pillar posts and set up 301 redirects (Prompt 6)';

    public function handle(SitemapService $sitemapService): int
    {
        $apply = (bool) $this->option('apply');

        $this->info('=== PROMPT 6: CONSOLIDATING CANNIBALIZED BLOG ARTICLES ===');

        $redirectMap = [];

        // ══════════════════════════════════════════════════════════════
        // 1. CLUSTER NIB -> Pillar: /blog/cara-membuat-nib
        // ══════════════════════════════════════════════════════════════
        $nibPillarSlug = 'cara-membuat-nib';
        $nibMergedSlugs = [
            'nib-gratis-tapi-bingung-mulai-dari-mana-ini-7-langkah-praktis-untuk-um',
            'umkm-wajib-nib-di-2026-aturan-baru-yang-perlu-kamu-tahu-sebelum-mulai',
            '5-kesalahan-saat-daftar-nib-yang-bikin-pengajuan-ditolak-oss',
            'usaha-rumahan-tanpa-nib-ini-risiko-tersembunyi-yang-bikin-rugi-puluhan',
        ];
        $nibDerivativeSlugs = [
            'cara-cek-nib-sudah-terbit-atau-belum-tanpa-harus-ke-kantor-oss' => 'cara-cek-nib',
            'modal-nib-sudah-ada-lalu-apa-5-dokumen-wajib-umkm-setelahnya' => 'dokumen-usaha-setelah-nib',
            'bisnis-pulsa-top-up-juga-butuh-nib-ini-jawaban-untuk-umkm-digital' => 'nib-usaha-pulsa',
            'punya-nib-bagaimana-cara-pasang-di-toko-online-shop-panduan-branding-u' => 'cara-pasang-nib-di-toko',
        ];

        // ══════════════════════════════════════════════════════════════
        // 2. CLUSTER STOK OPNAME -> Pillar: /blog/cara-stok-opname
        // ══════════════════════════════════════════════════════════════
        $stokPillarSlug = 'cara-stok-opname';
        $stokMergedSlugs = [
            'stok-opname-warung-sederhana-cara-hitung-selisih-tanpa-drama-dalam-30',
            'stok-opname-30-menit-selesai-tahapan-yang-bisa-kamu-tiru-untuk-warung',
        ];

        // ══════════════════════════════════════════════════════════════
        // 3. CLUSTER PEMBUKUAN WARUNG -> Pillar: /blog/cara-pembukuan-warung
        // ══════════════════════════════════════════════════════════════
        $pembukuanPillarSlug = 'cara-pembukuan-warung';
        $pembukuanMergedSlugs = [
            'catatan-keuangan-warung-buku-tulis-vs-aplikasi-mana-yang-lebih-akurat',
            'kelola-keuangan-warung-dari-nol-7-kebiasaan-owner-yang-bikin-rekening',
            'rapikan-keuangan-warung-dalam-14-hari-step-by-step-untuk-owner-sibuk',
        ];
        $pembukuanDerivativeSlug = [
            'laporan-keuangan-sederhana-umkm-sak-emkm-vs-pembukuan-sederhana' => 'laporan-keuangan-sederhana-umkm',
        ];

        // Plan redirects
        foreach ($nibMergedSlugs as $slug) {
            $redirectMap['/blog/' . $slug] = '/blog/' . $nibPillarSlug;
        }
        foreach ($nibDerivativeSlugs as $oldSlug => $newSlug) {
            $redirectMap['/blog/' . $oldSlug] = '/blog/' . $newSlug;
        }
        foreach ($stokMergedSlugs as $slug) {
            $redirectMap['/blog/' . $slug] = '/blog/' . $stokPillarSlug;
        }
        foreach ($pembukuanMergedSlugs as $slug) {
            $redirectMap['/blog/' . $slug] = '/blog/' . $pembukuanPillarSlug;
        }
        foreach ($pembukuanDerivativeSlug as $oldSlug => $newSlug) {
            $redirectMap['/blog/' . $oldSlug] = '/blog/' . $newSlug;
        }

        $this->table(['Old URL (301 Source)', 'Target URL (301 Destination)'], collect($redirectMap)->map(fn($target, $source) => [$source, $target])->all());

        if (!$apply) {
            $this->warn('Run with --apply to execute the content updates and record 301 redirects.');
            return 0;
        }

        // Apply Cluster 1: NIB
        $this->updateNibPillar($nibPillarSlug);
        $this->updateDerivativeSlugs($nibDerivativeSlugs, 'legalitas', $nibPillarSlug, 'cara membuat NIB');

        // Apply Cluster 2: Stok Opname
        $this->updateStokPillar($stokPillarSlug);

        // Apply Cluster 3: Pembukuan Warung
        $this->updatePembukuanPillar($pembukuanPillarSlug);
        $this->updateDerivativeSlugs($pembukuanDerivativeSlug, 'keuangan', $pembukuanPillarSlug, 'cara pembukuan warung');

        // Record redirects in redirects table & remove merged posts from active published
        foreach ($redirectMap as $oldPath => $newPath) {
            Redirect::updateOrCreate(
                ['source_url' => $oldPath],
                [
                    'target_url' => $newPath,
                    'status_code' => 301,
                ]
            );

            // If old path was a merged post (not just a slug rename), unpublish it so it redirects cleanly
            $mergedOldSlugs = array_merge($nibMergedSlugs, $stokMergedSlugs, $pembukuanMergedSlugs);
            $slugOnly = str_replace('/blog/', '', $oldPath);
            if (in_array($slugOnly, $mergedOldSlugs, true)) {
                Post::where('slug', $slugOnly)->update([
                    'is_published' => false,
                ]);
            }
        }

        // Regenerate sitemap_blog.xml
        $xml = $sitemapService->generateBlogXml();
        File::put(public_path('sitemap_blog.xml'), $xml);
        $this->info('Regenerated public/sitemap_blog.xml successfully.');

        $this->info('Cluster consolidation and 301 redirects completed successfully!');
        return 0;
    }

    private function updateNibPillar(string $slug): void
    {
        $post = Post::firstOrNew(['slug' => $slug]);

        $post->title = 'Cara Membuat NIB Online OSS: Panduan UMKM Lengkap';
        $post->seo_title = 'Cara Membuat NIB Online OSS: Panduan UMKM Lengkap';
        $post->meta_description = 'Panduan lengkap cara membuat NIB online lewat OSS untuk UMKM, usaha rumahan & perorangan. Dilengkapi syarat dokumen, 7 langkah praktis, dan 5 kesalahan fatal.';
        $post->category = 'Legalitas Usaha';
        $post->topic_cluster = 'legalitas';
        $post->author_name = 'Tim Edukasi Regulasi COOCA';
        $post->is_published = true;
        if (!$post->published_at) {
            $post->published_at = Carbon::now()->subDays(30);
        }
        $post->updated_at = Carbon::now();

        $content = <<<'MARKDOWN'
<p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    <span>Diperbarui per 29 Oktober 2026 &bull; Sesuai Regulasi OSS RBA &amp; PP No. 5 Tahun 2021</span>
</p>

Setiap pelaku usaha di Indonesia, mulai dari pedagang warung kelontong, kedai kopi, usaha rumahan kue basah, hingga konveksi rumahan, kini diwajibkan memiliki Nomor Induk Berusaha (NIB). NIB berfungsi sebagai identitas resmi izin berusaha yang menggantikan SIUP, TDP, dan IUI dalam satu berkas tunggal yang diterbitkan oleh Kementerian Investasi/BKPM melalui sistem Online Single Submission (OSS).

Bagi sebagian besar pemilik usaha mikro dan kecil (UMK), istilah izin usaha sering terdengar menakutkan dan dibayangkan butuh biaya jutaan rupiah ke kantor calo. Kenyataannya, sejak berlakunya sistem OSS Berbasis Risiko (OSS RBA), pembuatan NIB untuk kategori UMK dengan tingkat risiko rendah adalah **100% GRATIS**, prosesnya berlangsung online, dan berkas resmi langsung terbit dalam waktu kurang dari 30 menit tanpa perlu datang ke kantor dinas fisik.

Berikut adalah panduan lengkap dan komprehensif cara membuat NIB online dari nol hingga terbit, lengkap dengan syarat dokumen, langkah pendaftaran, kesalahan yang sering bikin pengajuan ditolak, serta apa yang harus dilakukan setelah NIB berada di tangan Anda.

---

## Daftar Isi
1. [Apa Itu NIB dan Mengapa Usaha Rumahan Wajib Memilikinya?](#apa-itu-nib)
2. [Risiko Tersembunyi Menjalankan Usaha Tanpa NIB](#risiko-tanpa-nib)
3. [Syarat Dokumen yang Wajib Disiapkan](#syarat-dokumen)
4. [7 Langkah Praktis Mendaftar NIB di Portal oss.go.id](#langkah-pendaftaran)
5. [5 Kesalahan Fatal Saat Mendaftar NIB yang Bikin Pengajuan Ditolak](#kesalahan-fatal)
6. [Langkah Wajib Selanjutnya Setelah NIB Terbit](#setelah-nib-terbit)
7. [Pertanyaan yang Sering Diajukan (FAQ NIB UMKM)](#faq-nib)

---

<h2 id="apa-itu-nib">1. Apa Itu NIB dan Mengapa Usaha Rumahan Wajib Memilikinya?</h2>

Nomor Induk Berusaha (NIB) adalah nomor identitas pelaku usaha yang diterbitkan oleh lembaga OSS setelah pendaftaran berhasil disetujui. Berdasarkan Peraturan Pemerintah Nomor 5 Tahun 2021 tentang Penyelenggaraan Perizinan Berusaha Berbasis Risiko, NIB tidak hanya berfungsi sebagai tanda pengenal legal, melainkan sekaligus berlaku sebagai:
* **Tanda Daftar Perusahaan (TDP)**: Bukti resmi bahwa usaha Anda terdaftar sah dalam lembaran database negara.
* **Angka Pengenal Impor (API)**: Jika kelak Anda mendatangkan bahan baku atau mesin dari luar negeri.
* **Hak Akses Kepabeanan**: Untuk keperluan ekspor produk lokal ke pasar internasional.
* **Jaminan Sosial Otomatis**: NIB terintegrasi dengan pendaftaran kepesertaan BPJS Ketenagakerjaan dan BPJS Kesehatan UMK.

Bagi usaha mikro perseorangan, NIB dengan kategori risiko rendah otomatis berlaku sebagai izin edar dasar, sertifikasi standar kelayakan, dan pernyataan mandiri (self-declaration) jaminan produk halal.

---

<h2 id="risiko-tanpa-nib">2. Risiko Tersembunyi Menjalankan Usaha Tanpa NIB</h2>

Banyak pemilik warung atau produsen camilan rumahan berpikir: *"Usaha saya kan masih kecil-kecilan di dapur rumah, buat apa repot bikin izin usaha?"* Pola pikir ini menyimpan sejumlah risiko finansial dan operasional yang nyata:

1. **Gagal Mengakses Pembiayaan Bank (KUR)**: Syarat mutlak pengajuan Kredit Usaha Rakyat (KUR) berbunga subsidi rendah di bank pemerintah (BRI, Mandiri, BNI) adalah kepemilikan NIB yang masih aktif.
2. **Tidak Bisa Menjual Produk ke Pasar Modern & Marketplace B2B**: Minimarket, swalayan, atau platform e-katalog pemerintah mewajibkan supplier memiliki legalitas NIB untuk menerbitkan faktur resmi.
3. **Tertinggal Program Bantuan & Pelatihan Pemerintah**: Kementerian Koperasi dan UKM serta Dinas Perindagkop hanya menyalurkan bantuan hibah alat produksi dan subsidi sertifikasi Halal/BPOM kepada UMKM yang ber-NIB.
4. **Rentan Terkena Penertiban Operasional**: Usaha yang belum berizin rentan dipersoalkan oleh aparat lingkungan setempat saat timbul perselisihan batas wilayah operasional atau komplain konsumen.

---

<h2 id="syarat-dokumen">3. Syarat Dokumen yang Wajib Disiapkan</h2>

Sebelum mengakses situs OSS, pastikan dokumen berikut sudah siap di depan Anda:
* **Nomor Induk Kependudukan (NIK)**: Sesuai KTP pemilik usaha perseorangan (wajib sudah valid di database Dukcapil nasional).
* **Nomor Pokok Wajib Pajak (NPWP)**: NPWP pribadi pemilik usaha atau penanggung jawab.
* **Alamat Email Aktif**: Gunakan email pribadi yang dapat menerima tautan aktivasi dan verifikasi OTP.
* **Nomor WhatsApp Aktif**: Untuk penerimaan kode keamanan transaksi OSS.
* **Data Detail Usaha**:
  * Nama merek dagang usaha.
  * Alamat lokasi usaha lengkap (termasuk RT/RW, kelurahan, kecamatan, dan kode pos).
  * Perkiraan modal awal usaha (di luar tanah dan bangunan tempat usaha).
  * Jumlah tenaga kerja Indonesia yang dipekerjakan.
* **Kode Klasifikasi Baku Lapangan Usaha Indonesia (KBLI)**: Menentukan bidang usaha Anda (misalnya KBLI 47112 untuk warung sembako, KBLI 56303 untuk kedai kopi/kafe, atau KBLI 10794 untuk industri kerupuk/keripik).

---

<h2 id="langkah-pendaftaran">4. 7 Langkah Praktis Mendaftar NIB di Portal oss.go.id</h2>

Berikut adalah alur resmi pendaftaran NIB perseorangan UMK melalui portal nasional **oss.go.id**:

### Langkah 1: Buka Portal Resmi OSS
Akses peramban di laptop atau smartphone Anda menuju alamat resmi **https://oss.go.id**. Pastikan URL berakhiran `.go.id` untuk menghindari situs tiruan atau calo berbayar.

### Langkah 2: Buat Akun Baru (Daftar Hak Akses)
Klik tombol **Daftar** di pojok kanan atas. Pilih skala usaha **Usaha Mikro dan Kecil (UMK)**, lalu pilih jenis pelaku usaha **Orang Perseorangan**. Masukkan NIK, tanggal lahir, alamat email aktif, dan nomor WhatsApp. Masukkan kode captcha lalu klik **Daftar**.

### Langkah 3: Verifikasi Email & Aktivasi Password
Buka kotak masuk email Anda dari OSS BKPM. Klik tautan verifikasi yang dikirimkan, lalu buat password akun OSS Anda yang aman (minimal 8 karakter kombinasi huruf besar, huruf kecil, dan angka).

### Langkah 4: Masuk ke Dashboard OSS
Kembali ke halaman utama oss.go.id, klik tombol **Masuk**. Masukkan email/username dan password yang baru saja Anda buat. Anda akan diarahkan ke halaman dashboard utama sistem perizinan berusaha.

### Langkah 5: Pilih Menu Perizinan Berusaha & Tambah Bidang Usaha
Klik menu **Perizinan Berusaha**, lalu pilih **Permohonan Baru**. Isi data profil usaha Anda:
* Pilih jenis kegiatan: **Utama**.
* Masukkan kode KBLI 5 digit yang paling sesuai dengan aktivitas bisnis Anda.
* Rinci kegiatan usaha yang dilakukan sehari-hari.

### Langkah 6: Lengkapi Data Lokasi Usaha & Perkiraan Modal
Masukkan alamat lengkap tempat usaha Anda. Jika usaha dikerjakan di rumah tinggal, centang opsi lokasi di rumah. Masukkan estimasi modal usaha (untuk skala UMK modal di bawah Rp1 miliar). Sistem akan secara otomatis mengklasifikasikan tingkat risiko usaha Anda (Mayoritas UMK masuk kategori **Risiko Rendah**).

### Langkah 7: Validasi Pernyataan Mandiri & Cetak Dokumen NIB
Periksa kembali ringkasan draf izin usaha Anda. Centang kotak persetujuan pernyataan mandiri (kesiapan mematuhi standar lingkungan, tata ruang, dan keselamatan kerja). Klik tombol **Terbitkan NIB**. Berkas PDF NIB resmi yang dilengkapi barcode QR Code digital akan langsung terbit dan dapat Anda unduh serta cetak seketika!

---

<h2 id="kesalahan-fatal">5. 5 Kesalahan Fatal Saat Mendaftar NIB yang Bikin Pengajuan Ditolak</h2>

Meskipun sistemnya otomatis, ribuan pemilik usaha gagal mendapatkan NIB atau mengalami kendala perizinan lanjutan akibat 5 kekeliruan umum ini:

1. **Salah Memilih 5 Digit Kode KBLI**: Misalnya Anda memproduksi keripik singkong di dapur (industri makanan olahan / KBLI 10794), tetapi Anda malah memilih KBLI perdagangan eceran (KBLI 47214). Akibatnya, saat ingin mengurus izin edar P-IRT ke Dinas Kesehatan, sistem menolak karena KBLI Anda tercatat sebagai toko retail, bukan produsen!
2. **Data NIK KTP Tidak Sinkron dengan Dukcapil**: Pastikan NIK Anda tidak bermasalah atau memiliki data ganda di kecamatan sebelum mendaftar.
3. **Mengisi Modal Usaha Melebihi Batas UMK**: Jika Anda salah memasukkan angka modal (misal menambahkan nol berlebih menjadi Rp5 miliar), sistem akan menganggap Anda korporasi skala besar yang mewajibkan Amdal rumit dan izin lokasi berbayar.
4. **Email atau Nomor HP Menggunakan Milik Pihak Ketiga**: Jangan pernah menggunakan email atau nomor HP orang lain atau jasa calo. Jika kelak Anda butuh login ulang atau reset password OSS, Anda akan kehilangan akses legalitas bisnis Anda sendiri.
5. **Melewatkan Pernyataan Mandiri K3 dan Tata Ruang**: Abaikan membaca formulir pernyataan mandiri dapat memicu penangguhan status NIB saat ada verifikasi lapangan berkala oleh satgas perizinan daerah.

---

<h2 id="setelah-nib-terbit">6. Langkah Wajib Selanjutnya Setelah NIB Terbit</h2>

Mendapatkan lembar PDF NIB adalah tonggak awal legalitas usaha Anda. Selanjutnya, lakukan langkah optimalisasi berikut:
* **Cetak dan Pasang di Tempat Usaha**: Pasang salinan NIB berbingkai di dinding toko, meja kasir, atau cantumkan nomor NIB pada bio etalase toko online Anda untuk mendongkrak kepercayaan pembeli.
* **Gunakan untuk Mengurus Sertifikasi Halal Gratis (SEHATI)**: Nomor NIB adalah syarat utama pendaftaran sertifikasi halal skema self-declare di portal BPJPH SiHalal.
* **Buka Rekening Bank Khusus Usaha**: Pisahkan rekening pribadi dan kas toko menggunakan lampiran NIB ke bank terdekat.
* **Terapkan Pencatatan Kasir & Keuangan Digital**: Usaha yang legal memerlukan tata kelola transaksi yang rapi. Gunakan aplikasi kasir dan pembukuan terintegrasi seperti COOCA untuk mengontrol arus kas dan stok kulakan setiap hari.

---

<h2 id="faq-nib">7. Pertanyaan yang Sering Diajukan (FAQ NIB UMKM)</h2>

### Apakah pembuatan NIB di OSS dipungut biaya retribusi?
Tidak ada biaya sama sekali (100% gratis). Pemerintah tidak memungut biaya apa pun untuk pendaftaran dan penerbitan NIB bagi pelaku Usaha Mikro dan Kecil (UMK).

### Berapa lama masa berlaku dokumen NIB?
NIB berlaku selamanya seumur hidup usaha Anda, selama usaha tersebut masih aktif beroperasi dan tidak melakukan perubahan badan hukum.

### Bisakah satu orang memiliki lebih dari satu jenis usaha dalam satu NIB?
Bisa. Satu NIB perseorangan dapat memuat beberapa kode KBLI yang berbeda. Misalnya Anda memiliki warung sembako sekaligus membuka jasa laundry kiloan.

### Apakah usaha yang beroperasi di rumah kontrakan bisa mendaftar NIB?
Bisa. Anda cukup mencantumkan alamat tempat tinggal sewa saat ini sebagai lokasi domisili kegiatan usaha pada formulir OSS.

### Apakah setelah punya NIB otomatis langsung wajib bayar pajak besar?
Untuk UMKM perorangan dengan omzet bruto di bawah Rp500 juta dalam satu tahun pajak, pemerintah memberikan pembebasan pajak PPh Final (omzet s.d. Rp500 juta bebas pajak sesuai UU HPP). Anda cukup melaporkan SPT Tahunan secara nihil.
MARKDOWN;

        $post->content = Str::markdown($content);
        $post->save();
        $this->info("Updated Pillar NIB: /blog/{$slug}");
    }

    private function updateStokPillar(string $slug): void
    {
        $post = Post::firstOrNew(['slug' => $slug]);

        $post->title = 'Cara Stok Opname Toko & Warung: Metode 30 Menit | COOCA';
        $post->seo_title = 'Cara Stok Opname Toko & Warung: Metode 30 Menit | COOCA';
        $post->meta_description = 'Panduan praktis cara stok opname toko dan warung tanpa perlu tutup seharian. Dilengkapi metode 30 menit, cara hitung selisih barang, dan template Excel gratis.';
        $post->category = 'Manajemen Stok';
        $post->topic_cluster = 'stok';
        $post->author_name = 'Tim Operasional COOCA';
        $post->is_published = true;
        if (!$post->published_at) {
            $post->published_at = Carbon::now()->subDays(20);
        }
        $post->updated_at = Carbon::now();

        $content = <<<'MARKDOWN'
<p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    <span>Diperbarui per 29 Oktober 2026 &bull; Panduan Audit Inventori Praktis UMKM Retail</span>
</p>

Bagi sebagian besar pemilik toko kelontong, minimarket, apotek, maupun kios aksesoris, kata "stok opname" sering kali menjadi momok yang melelahkan. Bayangan harus menutup pintu toko seharian penuh, menghitung ribuan bungkus barang sampai larut malam, hingga frustrasi menemukan uang modal tekor karena stok barang hilang misterius, membuat kegiatan audit ini kerap ditunda berbulan-bulan.

Padahal, membiarkan inventori toko tanpa kontrol persediaan berkala adalah penyebab nomor satu kebangkrutan tersembunyi bagi bisnis retail. Ketika stok fisik di rak berbeda jauh dengan catatan pembukuan, Anda tidak hanya kehilangan uang modal, melainkan juga berisiko mengecewakan pembeli karena produk yang dicari ternyata sudah habis tak bersisa.

Kabar baiknya: stok opname tidak harus memakan waktu berhari-hari. Dengan menerapkan metode audit inventori modern dan pembagian siklus berkala, Anda dapat menyelesaikan stok opname dalam waktu **30 menit saja** tanpa perlu menutup jam buka toko Anda.

---

## Daftar Isi
1. [Apa Itu Stok Opname dan Mengapa Toko Sering Mengalami Selisih?](#pengertian-stok-opname)
2. [3 Alasan Utama Stok Fisik Berbeda dengan Catatan Buku](#penyebab-selisih)
3. [Metode 30 Menit: Cycle Counting untuk Toko yang Tetap Buka](#metode-30-menit)
4. [Tahapan Langkah demi Langkah Melakukan Stok Opname Cepat](#tahapan-stok-opname)
5. [Cara Menghitung Selisih Stok (Plus & Minus) Tanpa Drama](#hitung-selisih)
6. [Alat Bantu: Download Template Excel & Software Stok Real-Time](#alat-bantu)
7. [Pertanyaan yang Sering Diajukan (FAQ Stok Opname)](#faq-stok-opname)

---

<h2 id="pengertian-stok-opname">1. Apa Itu Stok Opname dan Mengapa Toko Sering Mengalami Selisih?</h2>

Stok opname (stock taking) adalah aktivitas penghitungan fisik seluruh barang persediaan yang ada di etalase toko dan gudang penyimpanan, untuk kemudian dicocokkan dengan catatan saldo buku persediaan atau sistem kasir.

Tujuan utama stok opname adalah:
* Memastikan nilai aset persediaan barang dagangan di neraca keuangan akurat.
* Mendeteksi sedini mungkin barang yang rusak, bocor, atau mendekati tanggal kedaluwarsa (expired date).
* Mengidentifikasi kebocoran kasir, salah hitung kembalian barang, atau potensi pencurian (shrinkage).
* Mengetahui kapan waktu paling tepat untuk melakukan pembelian ulang (*reorder point*) ke agen distributor.

---

<h2 id="penyebab-selisih">2. 3 Alasan Utama Stok Fisik Berbeda dengan Catatan Buku</h2>

Saat melakukan stok opname, hampir tidak pernah ditemukan kondisi di mana stok fisik cocok 100% dengan catatan di awal. Tiga penyebab utamanya adalah:

1. **Kesalahan Konversi Satuan (Dus vs Renceng vs Pcs)**: Toko kulakan 1 dus minyak goreng berisi 12 pouch. Karyawan kasir menjual eceran 2 pouch, tetapi lupa mencatat konversi sehingga catatan sistem masih membaca 1 dus utuh.
2. **Barang Rusak atau Kedaluwarsa yang Tidak Dieliminasi**: Paket mi instan yang remuk atau susu kotak yang kempes sering kali disingkirkan dari rak pajang tetapi tidak pernah dicatat sebagai *spoilage* (penghapusan barang rusak).
3. **Pencatatan Transaksi yang Terlewat Saat Jam Ramai**: Ketika antrean pembeli membludak, kasir sering melayani transaksi terburu-buru tanpa menekan tombol scan kasir, atau kasbon tetangga yang baru dicatat di sobekan kertas rokok yang kemudian tercecer.

---

<h2 id="metode-30-menit">3. Metode 30 Menit: Cycle Counting untuk Toko yang Tetap Buka</h2>

Pendekatan lama (Wall-to-Wall Counting) mewajibkan toko menghitung seluruh isi gudang sekaligus dalam satu waktu. Bagi toko kecil dengan karyawan terbatas, cara ini sangat tidak realistis dan mengorbankan omzet harian.

Sebagai gantinya, gunakan metode **Cycle Counting (Penghitungan Siklus Bertahap)**:
* **Bagi Toko Menjadi 6 Zona Kategori**: Misalnya Senin zona Minuman & Susu, Selasa zona Mi Instan & Bumbu, Rabu zona Sabun & Toiletries, Kamis zona Makanan Ringan/Snack, Jumat zona Rokok & Pulsa, dan Sabtu zona Bahan Pokok/Beras.
* **Luangkan Waktu 30 Menit Sebelum Buka atau Setelah Tutup Kasir**: Fokus hanya menghitung 1 rak kategori hari itu. Karena hanya satu kategori, proses penghitungan hanya butuh 20 hingga 30 menit!
* **Rotasi Setiap Pekan**: Dalam satu bulan, seluruh toko Anda sudah terhitung tuntas tanpa pernah merasa kelelahan atau mengorbankan jam operasional jualan.

---

<h2 id="tahapan-stok-opname">4. Tahapan Langkah demi Langkah Melakukan Stok Opname Cepat</h2>

Ikuti 4 langkah taktis berikut agar proses opname berjalan terstruktur:

### Tahap 1: Kerapian Rak (Facing & Fronting)
Sebelum mulai menghitung, rapikan produk di rak. Tarik barang ke depan (*fronting*), kumpulkan produk sejenis dalam satu baris, dan pisahkan barang yang kemasannya rusak atau berdebu ke keranjang khusus retur.

### Tahap 2: Pembagian Peran Penghitung & Pencatat
Jika memiliki 2 staf, bagi tugas: satu orang bertugas memegang scanner/menghitung fisik barang di rak, orang kedua mencatat angka pada lembar checklist. Jika dikerjakan sendiri, gunakan barcode scanner kamera HP untuk mempercepat input data.

### Tahap 3: Penghitungan Fisik secara Buta (Blind Count)
Pemberi tugas sebaiknya tidak menampilkan saldo stok sistem pada lembar hitung fisik. Hal ini dilakukan agar pemeriksa benar-benar menghitung jumlah riil di lapangan, bukan sekadar menyamakan angka dengan buku.

### Tahap 4: Rekonsiliasi & Investigasi Selisih
Bandingkan hasil hitung fisik dengan saldo buku kasir. Jika terdapat selisih lebih dari 3-5 unit pada barang bernilai tinggi (misalnya rokok, susu kaleng, atau kosmetik), lakukan penghitungan ulang seketika untuk mengonfirmasi.

---

<h2 id="hitung-selisih">5. Cara Menghitung Selisih Stok (Plus & Minus) Tanpa Drama</h2>

Rumus dasar penghitungan selisih persediaan sangat sederhana:

$$\text{Selisih Stok} = \text{Stok Fisik Aktual} - \text{Stok Saldo Sistem}$$

Terdapat dua kondisi hasil rekonsiliasi:
1. **Selisih Negatif (Stok Fisik < Saldo Buku)**: Terjadi kehilangan barang, barang rusak belum dibukukan, atau kasir lupa menginput transaksi penjualan. Nilai kerugian harus dibukukan sebagai Beban Kerusakan/Kehilangan Stok.
2. **Selisih Positif (Stok Fisik > Saldo Buku)**: Barang masuk dari distributor belum diinput ke penerimaan barang (*purchasing*), atau kasir salah memilih produk yang mirip saat transaksi kasir sebelumnya.

---

<h2 id="alat-bantu">6. Alat Bantu: Download Template Excel & Software Stok Real-Time</h2>

Untuk memudahkan proses stok opname warung Anda, kami telah menyediakan template spreadsheet siap pakai yang dapat diunduh secara gratis:
* Unduh: [**Template Stok Opname Excel Gratis**](/template/stok-opname-excel) untuk pencatatan manual di laptop atau Google Sheets.
* Jika toko Anda memiliki lebih dari 100 produk, beralihlah ke [**Aplikasi Stok Barang & Gudang COOCA**](/omnichannel-erp/inventory). Dengan aplikasi stok real-time, setiap penjualan di kasir otomatis memotong persediaan, peringatan stok menipis berbunyi otomatis di HP Anda, dan opname cukup dilakukan dengan scan barcode kamera smartphone.

---

<h2 id="faq-stok-opname">7. Pertanyaan yang Sering Diajukan (FAQ Stok Opname)</h2>

### Seberapa sering toko kelontong atau minimarket harus melakukan stok opname?
Untuk barang bernilai tinggi dan fast-moving (seperti rokok, susu formula, minyak goreng), lakukan opname mingguan. Untuk kategori umum lainnya, metode cycle counting mingguan atau bulanan sudah sangat memadai.

### Bagaimana jika ada pelanggan yang berbelanja saat stok opname sedang berlangsung?
Inilah keunggulan metode Cycle Counting. Anda hanya mengunci 1 rak yang sedang dihitung selama 15 menit. Jika ada pembeli mengambil barang dari rak tersebut, cukup kurangkan angka penjualan dari hasil hitung lembar fisik.

### Apakah selisih barang yang hilang wajib dipotong dari gaji karyawan kasir?
Tentukan batas toleransi kehilangan (*shrinkage tolerance*), misalnya maksimal 0,2% dari total omzet. Jika selisih melebihi batas akibat kelalaian terbukti, skema ganti rugi proporsional dapat disepakati bersama secara transparan.

### Bagaimana cara mencatat barang yang kedaluwarsa saat stok opname?
Barang kedaluwarsa harus dikeluarkan dari rak penjualan dan dibukukan ke pos Biaya Barang Kedaluwarsa agar nilai aset toko Anda tetap mencerminkan modal yang benar-benar bisa dijual.

### Bisakah stok opname dilakukan langsung dari smartphone Android?
Bisa. Menggunakan sistem kasir dan inventori COOCA, staf Anda cukup membawa HP berkeliling rak, mengarahkan kamera ke barcode produk, dan memasukkan jumlah fisik yang langsung tersinkron ke dashboard pemilik toko.
MARKDOWN;

        $post->content = Str::markdown($content);
        $post->save();
        $this->info("Updated Pillar Stok Opname: /blog/{$slug}");
    }

    private function updatePembukuanPillar(string $slug): void
    {
        $post = Post::firstOrNew(['slug' => $slug]);

        $post->title = 'Cara Pembukuan Warung Sederhana & Bebas Tekor | COOCA';
        $post->seo_title = 'Cara Pembukuan Warung Sederhana & Bebas Tekor | COOCA';
        $post->meta_description = 'Panduan lengkap cara pembukuan warung dan toko kelontong dari nol. Dilengkapi perbandingan buku tulis vs aplikasi, 7 kebiasaan anti tekor, dan template Excel.';
        $post->category = 'Pembukuan & Keuangan';
        $post->topic_cluster = 'keuangan';
        $post->author_name = 'Tim Keuangan UMKM COOCA';
        $post->is_published = true;
        if (!$post->published_at) {
            $post->published_at = Carbon::now()->subDays(15);
        }
        $post->updated_at = Carbon::now();

        $content = <<<'MARKDOWN'
<p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    <span>Diperbarui per 29 Oktober 2026 &bull; Panduan Manajemen Kas &amp; Pembukuan Warung Kelontong</span>
</p>

Pernahkah Anda mengalami situasi di mana toko atau warung kelontong Anda terlihat sangat ramai setiap hari, pembeli bolak-balik berbelanja, laci kasir penuh uang tunai, tetapi saat tiba waktu kulakan barang ke agen distributor, saldo rekening toko justru kosong melompong?

Fenomena ini adalah masalah klasik yang dialami oleh lebih dari 80% pemilik usaha mikro di Indonesia. Usahanya tidak sepi, produknya laku keras, tetapi uang keuntungan bersih seakan menguap tanpa jejak. Penyebab utamanya bukan karena margin laba yang terlalu kecil, melainkan **ketiadaan sistem pembukuan warung yang disiplin dan konsisten**.

Buku kas bukan hanya monopoli perusahaan korporat besar berakuntan mahal. Pemilik warung sembako, kedai kopi kecil, maupun konter pulsa pun dapat menerapkan pembukuan sederhana yang rapi, cepat, dan membuat arus kas usaha selalu sehat tanpa pusing rumus rumit.

---

## Daftar Isi
1. [Kenapa Warung Ramai Tapi Uangnya Selalu Tekor?](#kenapa-uang-tekor)
2. [Buku Tulis vs Spreadsheet Excel vs Aplikasi Kasir: Mana yang Terbaik?](#perbandingan-alat)
3. [7 Kebiasaan Emas Pemilik Warung Agar Kas Tidak Tekor](#kebiasaan-owner)
4. [Rencana Aksi 14 Hari Merapikan Keuangan Warung Langkah demi Langkah](#rencana-14-hari)
5. [Struktur Buku Kas Sederhana: Format Arus Kas, Pengeluaran & Kasbon](#struktur-buku-kas)
6. [Tautan Alat Bantu: Download Template Excel & Aplikasi Finansial Otomatis](#alat-bantu-keuangan)
7. [Pertanyaan yang Sering Diajukan (FAQ Pembukuan Warung)](#faq-pembukuan)

---

<h2 id="kenapa-uang-tekor">1. Kenapa Warung Ramai Tapi Uangnya Selalu Tekor?</h2>

Kebocoran uang di usaha warung bersumber dari tiga kekeliruan fatal yang sering dianggap sepele:

* **Pencampuran Dompet Toko dengan Dompet Dapur Pribadi**: Mengambil uang dari laci kasir untuk membeli bensin motor keluarga, membayar SPP anak, atau membeli lauk makan siang tanpa pernah dicatat sebagai prive atau biaya pribadi.
* **Bon Kasbon Hutang Pelanggan yang Tercecer**: Memberikan utang belanja sembako kepada tetangga hanya dengan catatan di secarik kertas kalender atau ingatan kepala. Saat pelanggan tidak membayar tepat waktu, warung kehilangan modal kerja untuk belanja kulakan.
* **Menganggap Omzet Kotor sebagai Keuntungan Bersih**: Saat menerima uang penjualan Rp1.000.000 dalam sehari, pemilik toko langsung merasa memiliki keuntungan sebesar itu, padahal Rp850.000 di antaranya adalah modal pokok barang (HPP) yang harus diputar kembali ke supplier.

---

<h2 id="perbandingan-alat">2. Buku Tulis vs Spreadsheet Excel vs Aplikasi Kasir: Mana yang Terbaik?</h2>

Setiap pelaku UMK memiliki preferensi alat pencatatan yang berbeda. Mari kita bedah perbandingannya:

### 1. Buku Tulis Manual (Kertas Folio)
* **Kelebihan**: Murah, tidak memerlukan listrik atau gadget, mudah dicoret-coret siapa saja.
* **Kekurangan**: Rawan sobek, basah terkena tumpahan minyak/air, rentan hilang, tidak bisa menghitung total laba otomatis, dan tidak bisa memantau sisa stok barang secara real-time.

### 2. Spreadsheet Excel / Google Sheets
* **Kelebihan**: Menggunakan formula otomatis untuk penjumlahan dan saldo akhir, bisa diakses dari HP atau laptop.
* **Kekurangan**: Membutuhkan ketelitian rumus, rawan formula error jika salah hapus kolom, dan tidak otomatis terhubung ke pencatatan transaksi kasir harian.

### 3. Aplikasi Kasir & Pembukuan Terintegrasi (COOCA)
* **Kelebihan**: Setiap transaksi penjualan di kasir otomatis memotong stok barang, langsung membentuk jurnal kas masuk, tagihan kasbon terkirim otomatis ke WhatsApp pelanggan, dan laporan laba bersih harian tersaji instan dalam genggaman HP Anda.
* **Kekurangan**: Memerlukan perangkat smartphone Android atau tablet yang terisi daya baterai.

---

<h2 id="kebiasaan-owner">3. 7 Kebiasaan Emas Pemilik Warung Agar Kas Tidak Tekor</h2>

Agar arus kas usaha Anda selalu surplus, terapkan 7 disiplin keuangan berikut:

1. **Pisahkan Rekening Bank Toko dan Rekening Pribadi**: Buka satu rekening khusus yang hanya digunakan untuk mutasi setoran kasir dan transfer pembelian barang ke supplier.
2. **Gaji Diri Sendiri Secara Tetap**: Tentukan besaran nominal gaji pribadi Anda per bulan (misal Rp2.500.000). Jangan pernah mengambil uang di luar jatah gaji bulanan tersebut.
3. **Catat Kasbon Pelanggan Seketika**: Jangan menunda pencatatan utang barang. Segera input nama, nominal, dan kirimkan rekapan nota digital ke WhatsApp pelanggan saat itu juga.
4. **Hitung Fisik Uang Kas Setiap Tutup Kasir**: Cocokkan uang tunai di laci dengan total rekapan penjualan hari itu sebelum meninggalkan warung.
5. **Simpan Struk Kulakan dari Agen Distributor**: Arsipkan faktur pembelian beras, minyak goreng, dan rokok untuk memverifikasi kenaikan harga modal pokok.
6. **Alokasikan Dana Cadangan Biaya Operasional**: Sisihkan minimal 5-10% dari laba kotor untuk cadangan bayar listrik, sewa kios, atau pemeliharaan kulkas etalase.
7. **Evaluasi Laporan Laba Bersih Setiap Pekan**: Luangkan 15 menit setiap hari Minggu malam untuk mengecek produk apa yang paling laris dan berapa sisa kasbon yang belum tertagih.

---

<h2 id="rencana-14-hari">4. Rencana Aksi 14 Hari Merapikan Keuangan Warung Langkah demi Langkah</h2>

Memperbaiki tata kelola keuangan yang berantakan tidak bisa dilakukan dalam semalam. Ikuti jadwal aksi 14 hari ini:

* **Hari 1-3 (Audit Modal & Utang Piutang)**: Kumpulkan semua sobekan kertas bon kasbon pelanggan. Buat daftar nama, nomor HP, dan nominal utang. Hitung juga apakah toko Anda memiliki utang tempo ke supplier.
* **Hari 4-6 (Pemisahan Kas Fisik)**: Siapkan dua dompet/kotak uang terpisah di meja kasir: Kotak A (Uang Operasional Toko) dan Kotak B (Uang Pribadi Rumah Tangga).
* **Hari 7-10 (Penerapan Format Kas Harian)**: Mulai mencatat setiap rupiah uang masuk dan keluar pada satu lembar buku kas atau aplikasi kasir secara konsisten tanpa ada yang terlewat.
* **Hari 11-13 (Kerapian Stok Kulakan)**: Cocokkan harga jual di etalase dengan harga beli terbaru dari distributor agar tidak ada produk yang dijual rugi.
* **Hari 14 (Tutup Buku Perdana)**: Hitung total omzet kotor, potong HPP modal barang, bayar operasional listrik/plastik, dan nikmati kepuasan melihat angka laba bersih usaha yang riil.

---

<h2 id="struktur-buku-kas">5. Struktur Buku Kas Sederhana: Format Arus Kas, Pengeluaran & Kasbon</h2>

Format buku kas warung yang ideal setidaknya memuat 5 kolom utama:
* **Tanggal & Waktu**: Waktu terjadinya transaksi.
* **Uraian Transaksi**: Keterangan jelas (contoh: Penjualan Beras 5kg, Beli Plastik Kresek, atau Bayar Listrik Token).
* **Kas Masuk (Debit)**: Nominal uang yang masuk ke laci kasir.
* **Kas Keluar (Kredit)**: Pengeluaran tunai untuk belanja barang atau beban operasional.
* **Saldo Akhir Kas**: Total uang kas yang wajib ada di laci saat itu.

---

<h2 id="alat-bantu-keuangan">6. Tautan Alat Bantu: Download Template Excel & Aplikasi Finansial Otomatis</h2>

Untuk mempercepat implementasi pembukuan di warung Anda:
* Unduh: [**Template Pembukuan Warung Excel Gratis**](/template-pembukuan-gratis) untuk pencatatan mandiri tanpa biaya.
* Ingin otomatis tanpa repot catat manual di kertas? Pelajari fitur lengkap [**Aplikasi Pembukuan & Laporan Keuangan UMOCA / COOCA**](/omnichannel-erp/finance). Setiap struk kasir otomatis menjadi jurnal akuntansi rapi, neraca laba rugi terbit otomatis, dan pantau keuangan toko dari mana saja lewat smartphone.

---

<h2 id="faq-pembukuan">7. Pertanyaan yang Sering Diajukan (FAQ Pembukuan Warung)</h2>

### Apakah pemilik warung kecil wajib mengerti istilah debit, kredit, dan neraca akuntansi?
Tidak perlu. Sistem pembukuan UMK modern seperti COOCA menggunakan bahasa sehari-hari: Uang Masuk, Uang Keluar, dan Laba Bersih, sehingga siapapun dapat mengelolanya tanpa latar belakang pendidikan akuntansi.

### Bagaimana jika ada pelanggan yang membayar sebagian dari total kasbon hutangnya?
Catat sebagai pembayaran cicilan kasbon. Saldo piutang pelanggan akan otomatis terpotong dan sisa hutang yang belum dibayar akan tercantum jelas di nota berikutnya.

### Mengapa uang di laci kasir saya selalu tidak cocok dengan buku catatan di akhir hari?
Penyebab tersering adalah transaksi pengeluaran kecil (seperti bayar parkir sales, beli galon air minum, atau kembalian permen) yang lupa dicatat oleh staf kasir.

### Berapa persen keuntungan bersih ideal untuk toko kelontong atau warung sembako?
Rata-rata margin laba kotor warung sembako berada di kisaran 10% hingga 15% untuk barang pokok (beras, minyak, gula) dan 20% hingga 35% untuk barang pelengkap (snack, bumbu, es krim, minuman dingin).

### Apakah data keuangan di aplikasi kasir online aman dari intipan orang lain?
Sangat aman. Sistem COOCA menggunakan enkripsi cloud perbankan multi-tenant di mana data penjualan dan keuntungan hanya bisa dibuka oleh pemilik akun melalui PIN dan kata sandi pribadi.
MARKDOWN;

        $post->content = Str::markdown($content);
        $post->save();
        $this->info("Updated Pillar Pembukuan Warung: /blog/{$slug}");
    }

    private function updateDerivativeSlugs(array $slugPairs, string $topicCluster, string $pillarSlug, string $pillarAnchor): void
    {
        foreach ($slugPairs as $oldSlug => $newSlug) {
            $post = Post::where('slug', $oldSlug)->orWhere('slug', $newSlug)->first();
            if (!$post) {
                continue;
            }

            $post->slug = $newSlug;
            $post->topic_cluster = $topicCluster;

            // Ensure article has link to pillar if not already present
            if (!str_contains((string) $post->content, "/blog/{$pillarSlug}")) {
                $linkHtml = "\n\n<p class=\"p-4 rounded-xl bg-blue-50 dark:bg-white/5 border border-blue-200 dark:border-white/10 text-sm my-6\"><strong>Panduan Utama:</strong> Pelajari langkah lengkap dan resmi di artikel pilar kami tentang <a href=\"/blog/{$pillarSlug}\" class=\"text-[#007AFF] font-bold underline\">{$pillarAnchor}</a>.</p>\n";
                $post->content = $post->content . $linkHtml;
            }

            $post->save();
            $this->info("Updated derivative post: /blog/{$oldSlug} -> /blog/{$newSlug}");
        }
    }
}
