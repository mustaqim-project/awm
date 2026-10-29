<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Seo\SitemapService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SeoCreateHppBepPillarsCommand extends Command
{
    protected $signature = 'seo:create-hpp-bep-pillars {--apply : Apply the pillar creations and child backlinks directly}';

    protected $description = 'Create HPP and BEP pillar articles (>=1800 words) and link back from derivative articles (Prompt 7)';

    public function handle(SitemapService $sitemapService): int
    {
        $apply = (bool) $this->option('apply');

        $this->info('=== PROMPT 7: CREATING HPP & BEP PILLAR ARTICLES ===');

        if (!$apply) {
            $this->warn('Run with --apply to create the pillar articles and update child backlinks.');
            return 0;
        }

        // 1. Create/Update HPP Pillar
        $this->createHppPillar();

        // 2. Create/Update BEP Pillar
        $this->createBepPillar();

        // 3. Update Child HPP Articles with backlinks to /blog/cara-menghitung-hpp
        $hppChildren = [
            'hpp-bakso-per-porsi',
            'hpp-usaha-frozen-food',
            'cara-hitung-hpp-konveksi',
            'biaya-overhead-usaha-kecil',
            'hpp-satu-porsi-nasi-goreng-cara-hitung-real-dalam-5-menit',
            'hpp-roti-kue-biaya-sering-tidak',
            'hpp-catering-per-porsi-vs-laba',
            'hpp-minuman-caf-vs-boba-mana-lebih-cepat-balik-modal',
            'cara-menghitung-hpp-kopi',
        ];
        $this->linkChildrenToPillar($hppChildren, '/blog/cara-menghitung-hpp', 'cara menghitung HPP');

        // 4. Update Child BEP Articles with backlinks to /blog/cara-menghitung-bep
        $bepChildren = [
            'bep-multi-produk',
            'bep-warung-kopi-berapa-cangkir-per-hari-untuk-balik-modal',
            'hitung-bep-cafe-kecil-15-meja',
        ];
        $this->linkChildrenToPillar($bepChildren, '/blog/cara-menghitung-bep', 'cara menghitung BEP');

        // 5. Regenerate sitemap_blog.xml
        $xml = $sitemapService->generateBlogXml();
        File::put(public_path('sitemap_blog.xml'), $xml);
        $this->info('Regenerated public/sitemap_blog.xml with new pillars.');

        $this->info('Prompt 7 completed successfully!');
        return 0;
    }

    private function createHppPillar(): void
    {
        $slug = 'cara-menghitung-hpp';
        $post = Post::firstOrNew(['slug' => $slug]);

        $post->title = 'Cara Menghitung HPP: Rumus, Contoh & Kalkulator Gratis';
        $post->seo_title = 'Cara Menghitung HPP: Rumus, Contoh & Kalkulator Gratis';
        $post->meta_description = 'Panduan lengkap cara menghitung HPP produk dagang dan manufaktur. Dilengkapi rumus HPP, contoh hitungan kuliner & retail dalam Rupiah, serta kalkulator gratis.';
        $post->category = 'HPP & Biaya Produksi';
        $post->topic_cluster = 'hpp';
        $post->author_name = 'Tim Finansial COOCA';
        $post->is_published = true;
        if (!$post->published_at) {
            $post->published_at = Carbon::now()->subDays(5);
        }
        $post->updated_at = Carbon::now();

        $content = <<<'MARKDOWN'
<div class="p-5 rounded-2xl bg-gradient-to-r from-blue-500/10 via-cyan-500/10 to-transparent border border-blue-500/20 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="space-y-1">
        <span class="text-xs uppercase font-bold tracking-wider text-[#007AFF] dark:text-[#00C4D8] block">Alat Hitung Praktis</span>
        <div class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Hitung HPP Otomatis Tanpa Excel</div>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">Gunakan simulator online gratis untuk menghitung HPP resep makanan, produk dagang, dan manufaktur.</p>
    </div>
    <a href="/kalkulator/hpp" class="px-5 py-2.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold inline-flex items-center gap-2 shrink-0 shadow-md">
        <span>Buka Kalkulator HPP</span>
        <i data-lucide="calculator" class="w-4 h-4"></i>
    </a>
</div>

<p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    <span>Diperbarui per 29 Oktober 2026 &bull; Standar Akuntansi Keuangan EMKM Indonesia</span>
</p>

Menentukan harga jual barang atau makanan tidak boleh didasarkan pada perkiraan kasual atau sekadar mencontek harga kompetitor sebelah toko. Banyak pelaku usaha mikro, kecil, dan menengah (UMKM) mengeluhkan omzet penjualan bulanan mereka tembus puluhan juta rupiah, namun saat tutup buku kas di akhir bulan, uang kas tidak bersisa atau bahkan tombok. Masalah mendasarnya hampir selalu sama: **kesalahan dalam cara menghitung HPP (Harga Pokok Penjualan)**.

Harga Pokok Penjualan (HPP), atau dalam istilah akuntansi internasional dikenal sebagai *Cost of Goods Sold (COGS)*, adalah total seluruh biaya langsung yang dikeluarkan untuk memproduksi atau memperoleh barang dan jasa yang siap dijual kepada konsumen.

Jika HPP yang Anda hitung terlalu rendah karena ada komponen biaya yang terlewat, Anda sejatinya sedang "mensubsidi" pembeli dan menjual barang dalam keadaan rugi tanpa Anda sadari. Sebaliknya, jika HPP terlalu tinggi karena alokasi biaya sembarangan, harga jual Anda menjadi tidak kompetitif di pasar.

Panduan mendalam ini menyajikan cara menghitung HPP dari nol, rumus baku HPP untuk bisnis dagang dan produksi/kuliner, contoh simulasi riil dalam mata uang Rupiah, kesalahan umum yang sering menjebak pemilik usaha, hingga cara menetapkan harga jual yang menguntungkan.

---

## Daftar Isi
1. [Pengertian HPP dan Fungsinya bagi Bisnis UMKM](#pengertian-hpp)
2. [Rumus HPP Bisnis Dagang vs Bisnis Produksi/Manufaktur](#rumus-hpp)
3. [Komponen Biaya dalam HPP: Biaya Langsung vs Biaya Tidak Langsung](#komponen-biaya)
4. [Contoh Perhitungan HPP Riil dalam Rupiah](#contoh-perhitungan)
   - [Contoh 1: HPP Usaha Kuliner (Bakso Sapi per Porsi)](#contoh-kuliner)
   - [Contoh 2: HPP Minuman Kekinian (Kopi Susu Gula Aren per Cup)](#contoh-minuman)
   - [Contoh 3: HPP Toko Retail Kelontong (Minyak Goreng Pouch)](#contoh-retail)
   - [Contoh 4: HPP Manufaktur Konveksi (Kaos Polos Cotton Combed)](#contoh-manufaktur)
5. [5 Kesalahan Umum Saat Menghitung HPP yang Bikin Usaha Rugi](#kesalahan-umum)
6. [Cara Menentukan Harga Jual Ideal dari Nilai HPP (Markup vs Margin)](#menentukan-harga-jual)
7. [Pelajari Artikel HPP Sektoral Spesifik](#artikel-sektoral)
8. [Pertanyaan yang Sering Diajukan (FAQ HPP)](#faq-hpp)

---

<h2 id="pengertian-hpp">1. Pengertian HPP dan Fungsinya bagi Bisnis UMKM</h2>

Harga Pokok Penjualan (HPP) mencerminkan modal murni yang melekat pada setiap unit produk yang laku terjual. Mengetahui HPP yang presisi memberikan manfaat vital:
* **Mengetahui Titik Kritis Keuntungan Bersih (Net Profit)**: Laba kotor didapat dari Penjualan dikurangi HPP. Tanpa angka HPP yang benar, laporan laba rugi usaha Anda adalah ilusi.
* **Dasar Penentuan Harga Jual (Pricing Strategy)**: Memberikan kepastian batas bawah diskon promosi agar toko tidak pernah menjual di bawah harga modal modal.
* **Alat Pengendalian Pemborosan Bahan Baku (Waste Control)**: Jika HPP per porsi makanan melonjak dari bulan lalu, Anda bisa segera mengaudit apakah ada kenaikan harga cabai/daging dari supplier atau porsi takaran juru masak yang berlebihan.
* **Mempermudah Valuasi Stok Persediaan di Akhir Periode**: Untuk pelaporan SPT Pajak UMKM dan pengajuan pinjaman modal kerja ke perbankan.

---

<h2 id="rumus-hpp">2. Rumus HPP Bisnis Dagang vs Bisnis Produksi/Manufaktur</h2>

Metode penghitungan HPP dibedakan menjadi dua jenis model bisnis utama:

### A. Rumus HPP untuk Usaha Dagang (Retail, Toko Kelontong, Minimarket)
Usaha dagang membeli barang jadi dari distributor/agen dan menjualnya kembali tanpa mengubah bentuk produk.

$$\text{HPP Usaha Dagang} = \text{Persediaan Awal} + \text{Pembelian Bersih} - \text{Persediaan Akhir}$$

Di mana:
* **Pembelian Bersih** = (Pembelian Kotor + Ongkos Kirim Pembelian) - (Retur Pembelian + Potongan Pembelian).

### B. Rumus HPP untuk Usaha Produksi & Kuliner (F&B, Bakery, Konveksi, Pabrik)
Usaha manufaktur dan kuliner mengolah bahan mentah menjadi barang jadi melalui proses produksi.

$$\text{HPP Usaha Manufaktur} = \text{Biaya Bahan Baku Langsung} + \text{Biaya Tenaga Kerja Langsung} + \text{Biaya Overhead Pabrik} + \text{Penyesuaian Barang Jadi}$$

Untuk pelaku kuliner skala UMKM (F&B), rumus praktis per porsi adalah:

$$\text{HPP per Porsi} = \text{Bahan Baku Resep} + \text{Bahan Pembantu/Bumbu} + \text{Kemasan/Packaging} + \text{Alokasi Gas/Listrik/Tenaga}$$

---

<h2 id="komponen-biaya">3. Komponen Biaya dalam HPP: Biaya Langsung vs Biaya Tidak Langsung</h2>

Agar perhitungan akurat, Anda wajib memisahkan biaya ke dalam 3 kategori:
1. **Biaya Bahan Baku Langsung (Direct Materials)**: Bahan utama yang wujudnya terlihat pada produk akhir (misalnya daging sapi untuk bakso, biji kopi untuk espresso, kain katun untuk konveksi baju).
2. **Biaya Tenaga Kerja Langsung (Direct Labor)**: Upah yang dibayarkan khusus kepada orang yang memproduksi barang secara satuan atau borongan (misal ongkos jahit Rp15.000 per potong kaos). Gaji bulanan manajer atau kasir toko tidak dimasukkan ke sini, melainkan ke beban operasional.
3. **Biaya Overhead Pabrik/Dapur (Overhead Costs)**: Biaya pendukung produksi seperti gas elpiji untuk memasak, plastik standing pouch, stiker label kemasan, sendok garpu plastik, serta penyusutan mesin blender atau oven. Untuk memahami cara mengalokasikannya, baca panduan khusus kami tentang [Biaya Overhead Usaha Kecil](/blog/biaya-overhead-usaha-kecil).

---

<h2 id="contoh-perhitungan">4. Contoh Perhitungan HPP Riil dalam Rupiah</h2>

Mari kita bedah simulasi perhitungan di berbagai sektor bisnis nyata:

<h3 id="contoh-kuliner">Contoh 1: HPP Usaha Kuliner (Bakso Sapi per Porsi)</h3>
Pak Joko membuat 1 batch adonan bakso yang menghasilkan 100 porsi (setiap porsi berisi 5 butir bakso, kuah, mie kuning, seledri, dan bawang goreng):
* Daging sapi segar (5 kg @ Rp120.000) = Rp600.000
* Tepung sagu aren & bumbu bawang/merica = Rp75.000
* Tulang sumsum sapi untuk kuah kaldu = Rp50.000
* Mie kuning & bihun (100 porsi) = Rp60.000
* Bawang goreng, seledri, saus & sambal = Rp40.000
* Gas elpiji 3kg (alokasi 1 tabung untuk merebus) = Rp22.000
* Mangkok kertas take-away & kantong kresek (100 set @ Rp1.200) = Rp120.000

$$\text{Total Biaya Batch} = \text{Rp}600.000 + 75.000 + 50.000 + 60.000 + 40.000 + 22.000 + 120.000 = \text{Rp}967.000$$

$$\text{HPP per Porsi} = \frac{\text{Rp}967.000}{100 \text{ porsi}} = \text{Rp}9.670 \text{ per porsi}$$

Jika Pak Joko menjual seharga Rp18.000 per porsi, laba kotor per porsi adalah Rp18.000 - Rp9.670 = **Rp8.330 (Margin 46,3%)**. Rincian lengkap varian mie ayam dapat dipelajari di artikel [HPP Bakso per Porsi](/blog/hpp-bakso-per-porsi).

<h3 id="contoh-minuman">Contoh 2: HPP Minuman Kekinian (Kopi Susu Gula Aren per Cup)</h3>
Untuk kedai kopi modern dengan ukuran gelas 16 oz:
* Biji kopi espresso blend (18 gram @ Rp180.000/kg) = Rp3.240
* Susu segar fresh milk (120 ml @ Rp19.000/liter) = Rp2.280
* Sirup gula aren murni (25 ml @ Rp35.000/liter) = Rp875
* Es batu higienis kristal = Rp300
* Cup plastik tebal 16 oz + lid sealer print logo + sedotan steril = Rp1.150

$$\text{Total HPP per Cup} = \text{Rp}3.240 + 2.280 + 875 + 300 + 1.150 = \text{Rp}7.845 \text{ per cup}$$

Dengan harga jual Rp18.000, margin keuntungan kotor mencapai lebih dari 56%. Ulasan komparasi biaya bahan kafe versus minuman boba dapat dibaca di artikel [HPP Minuman Cafe vs Boba](/blog/hpp-minuman-caf-vs-boba-mana-lebih-cepat-balik-modal) dan panduan teknis [Cara Menghitung HPP Kopi](/blog/cara-menghitung-hpp-kopi).

<h3 id="contoh-retail">Contoh 3: HPP Toko Retail Kelontong (Minyak Goreng Pouch)</h3>
Toko Berkah membeli 1 karton minyak goreng isi 12 pouch 2 liter dari distributor:
* Harga beli 1 karton dari distributor = Rp408.000
* Ongkos bongkar muat & parkir = Rp4.000
* Potongan diskon faktur agen = -Rp12.000
* Total Pembelian Bersih = Rp400.000

$$\text{HPP per Pouch} = \frac{\text{Rp}400.000}{12 \text{ pouch}} = \text{Rp}33.333 \text{ per pouch}$$

Jika dijual dengan harga pasar Rp36.000 per pouch, toko memperoleh margin laba kotor sebesar Rp2.667 (7,4%) per pouch.

<h3 id="contoh-manufaktur">Contoh 4: HPP Manufaktur Konveksi (Kaos Polos Cotton Combed)</h3>
Untuk produksi 100 potong kaos oblong lengan pendek:
* Kain Cotton Combed 30s (25 kg @ Rp115.000) = Rp2.875.000
* Kain rib leher spandeks = Rp125.000
* Ongkos potong (cutting) per pcs = Rp2.000 x 100 = Rp200.000
* Ongkos jahit & obras per pcs = Rp8.000 x 100 = Rp800.000
* Label satin & woven size per pcs = Rp750 x 100 = Rp75.000
* Plastik packing OPP tebal per pcs = Rp500 x 100 = Rp50.000
* Estimasi benang jahit & listrik mesin = Rp80.000

$$\text{Total Biaya Produksi} = \text{Rp}4.205.000$$

$$\text{HPP per Kaos} = \frac{\text{Rp}4.205.000}{100} = \text{Rp}42.050 \text{ per potong}$$

Pelajari detail perhitungan persentase limbah sisa kain (*waste*) pada artikel [Cara Hitung HPP Konveksi](/blog/cara-hitung-hpp-konveksi).

---

<h2 id="kesalahan-umum">5. 5 Kesalahan Umum Saat Menghitung HPP yang Bikin Usaha Rugi</h2>

Banyak pemilik usaha bangkrut perlahan akibat jebakan-jebakan berikut:

1. **Mengabaikan Biaya Kemasan & Label**: Kemasan kotak kardus pizza, standing pouch ziplock, stiker logo, dan sendok kayu sering tidak dihitung. Padahal di era pesan antar online, biaya kemasan bisa mencapai 15% dari total harga modal!
2. **Lupa Menghitung Bahan Terbuang (Yield & Shrinkage)**: Membeli daging ayam utuh 1 kg seharga Rp38.000 tidak berarti Anda mendapatkan 1 kg daging murni. Setelah dipotong tulang, lemak, dan kulit, daging bersih yang bisa dimasak mungkin hanya 700 gram. Jika Anda membaginya dengan 1 kg, HPP Anda meleset jauh!
3. **Menggratiskan Upah Tenaga Sendiri**: Pemilik usaha yang memasak atau menjahit sendiri sering tidak memasukkan upah ke HPP karena merasa *"ini kan usaha sendiri"*. Akibatnya, saat bisnis mulai ramai dan harus merekrut karyawan, harga jual langsung tekor karena tidak ada margin untuk membayar gaji.
4. **Tidak Memperbarui HPP Saat Harga Bahan Baku Naik**: Kenaikan harga telur ayam atau tepung terigu dari agen tidak langsung direspons dengan penyesuaian HPP.
5. **Memasukkan Biaya Sewa Toko ke HPP Unit Makanan**: Biaya sewa ruko tahunan adalah biaya tetap (*fixed cost*), bukan biaya variabel bahan baku. Memasukkannya sembarangan ke HPP per porsi akan mengacaukan perhitungan saat volume penjualan naik-turun.

---

<h2 id="menentukan-harga-jual">6. Cara Menentukan Harga Jual Ideal dari Nilai HPP (Markup vs Margin)</h2>

Setelah mendapatkan nilai HPP yang akurat, Anda dapat menentukan harga jual dengan dua formula:

### Metode 1: Markup Pricing (Penambahan Persentase dari Modal)
Menambahkan persentase keuntungan di atas nilai HPP:

$$\text{Harga Jual} = \text{HPP} + (\text{HPP} \times \text{Markup } \%)$$

*Contoh*: HPP porsi nasi goreng adalah Rp10.000. Anda menginginkan markup laba 50%.
Maka Harga Jual = Rp10.000 + (Rp10.000 x 50%) = **Rp15.000**.

### Metode 2: Margin Pricing (Persentase Laba dari Harga Jual)
Menentukan harga jual agar keuntungan bersih mencapai persentase tertentu dari omzet:

$$\text{Harga Jual} = \frac{\text{HPP}}{1 - \text{Target Margin}}$$

*Contoh*: HPP produk frozen food adalah Rp24.000. Anda menargetkan margin laba kotor 40% (0,4):
Maka Harga Jual = $\frac{\text{Rp}24.000}{1 - 0,4} = \frac{\text{Rp}24.000}{0,6} =$ **Rp40.000**.
Dengan harga jual Rp40.000 dan HPP Rp24.000, laba kotor Anda adalah Rp16.000 (persis 40% dari harga jual).

---

<h2 id="artikel-sektoral">7. Pelajari Artikel HPP Sektoral Spesifik</h2>

Untuk panduan teknis yang disesuaikan dengan jenis produk usaha Anda, silakan pelajari artikel turunan berikut:
* [HPP Satu Porsi Nasi Goreng Realistis](/blog/hpp-satu-porsi-nasi-goreng-cara-hitung-real-dalam-5-menit)
* [HPP Usaha Frozen Food & Biaya Cold Storage](/blog/hpp-usaha-frozen-food)
* [HPP Usaha Catering Prasmanan & Nasi Box](/blog/hpp-catering-per-porsi-vs-laba)
* [HPP Usaha Roti & Bakery Rumahan](/blog/hpp-roti-kue-biaya-sering-tidak)
* [Biaya Overhead Usaha Kecil: Listrik & Gas](/blog/biaya-overhead-usaha-kecil)

---

<h2 id="faq-hpp">8. Pertanyaan yang Sering Diajukan (FAQ HPP)</h2>

### Apa perbedaan utama antara HPP dan Biaya Operasional (OPEX)?
HPP adalah biaya langsung yang timbul hanya jika produk dibuat atau dibeli (bersifat variabel). Biaya operasional adalah biaya yang tetap harus dibayar baik ada penjualan maupun tidak, seperti sewa ruko, gaji kasir bulanan, biaya wifi, dan tagihan listrik penerangan toko.

### Apakah diskon promo toko mengurangi nilai HPP?
Tidak. Diskon promosi mengurangi pendapatan kotor (omzet penjualan), sedangkan HPP fisik produk tetap sama. Pemberian diskon akan memperkecil margin laba kotor Anda.

### Kapan waktu yang tepat bagi pemilik usaha untuk memperbarui perhitungan HPP?
Perbarui HPP setiap kali ada kenaikan harga bahan baku utama dari supplier lebih dari 5%, atau minimal setiap 3 bulan sekali saat evaluasi operasional berkala.

### Bagaimana cara menghitung HPP jika satu menu menggunakan bahan bumbu yang sangat sedikit (seperti 1/2 sendok teh garam)?
Gunakan metode alokasi biaya bumbu per batch atau persentase taksiran bumbu (*seasoning allowance*), misalnya menambahkan Rp500 hingga Rp1.000 per porsi untuk mencakup seluruh garam, merica, penyedap rasa, dan minyak goreng penumis.

### Apakah ada aplikasi yang bisa menghitung HPP otomatis saat transaksi kasir berjalan?
Ada. Menggunakan sistem ERP & POS COOCA, Anda cukup memasukkan resep Bill of Materials (BOM) sekali di awal. Setiap kali kasir menjual 1 porsi menu, sistem otomatis memotong stok bahan baku dan mencatat HPP riil sesuai harga modal kulakan terbaru secara otomatis.

<div class="faq-schema-json" style="display:none;">
[
  {
    "@type": "Question",
    "name": "Apa perbedaan utama antara HPP dan Biaya Operasional (OPEX)?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "HPP adalah biaya langsung yang timbul hanya jika produk dibuat atau dibeli (bersifat variabel). Biaya operasional adalah biaya yang tetap harus dibayar baik ada penjualan maupun tidak, seperti sewa ruko, gaji kasir bulanan, dan beban listrik."
    }
  },
  {
    "@type": "Question",
    "name": "Apakah diskon promo toko mengurangi nilai HPP?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Tidak. Diskon promosi mengurangi pendapatan kotor (omzet penjualan), sedangkan HPP fisik produk tetap sama. Pemberian diskon akan memperkecil margin laba kotor Anda."
    }
  },
  {
    "@type": "Question",
    "name": "Kapan waktu yang tepat bagi pemilik usaha untuk memperbarui perhitungan HPP?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Perbarui HPP setiap kali ada kenaikan harga bahan baku utama dari supplier lebih dari 5%, atau minimal setiap 3 bulan sekali saat evaluasi operasional berkala."
    }
  },
  {
    "@type": "Question",
    "name": "Bagaimana cara menghitung HPP jika satu menu menggunakan bahan bumbu yang sangat sedikit?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Gunakan metode alokasi biaya bumbu per batch atau persentase taksiran bumbu (seasoning allowance), misalnya menambahkan Rp500 hingga Rp1.000 per porsi untuk mencakup seluruh garam, merica, dan bumbu penumis."
    }
  },
  {
    "@type": "Question",
    "name": "Apakah ada aplikasi yang bisa menghitung HPP otomatis saat transaksi kasir berjalan?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Ada. Sistem ERP & POS COOCA otomatis memotong stok bahan baku dan mencatat HPP riil per menu sesuai formula resep Bill of Materials (BOM) setiap kali kasir mencetak struk transaksi."
    }
  }
]
</div>
MARKDOWN;

        $post->content = Str::markdown($content);
        $post->save();
        $this->info("Created/Updated Pillar HPP: /blog/{$slug}");
    }

    private function createBepPillar(): void
    {
        $slug = 'cara-menghitung-bep';
        $post = Post::firstOrNew(['slug' => $slug]);

        $post->title = 'Cara Menghitung BEP: Rumus, Contoh & Kalkulator Gratis';
        $post->seo_title = 'Cara Menghitung BEP: Rumus, Contoh & Kalkulator Gratis';
        $post->meta_description = 'Panduan lengkap cara menghitung BEP (Break Even Point) usaha dalam unit dan rupiah. Lengkap dengan rumus titik impas, contoh hitungan, dan kalkulator gratis.';
        $post->category = 'Analisa Bisnis & Keuangan';
        $post->topic_cluster = 'bep';
        $post->author_name = 'Tim Finansial COOCA';
        $post->is_published = true;
        if (!$post->published_at) {
            $post->published_at = Carbon::now()->subDays(4);
        }
        $post->updated_at = Carbon::now();

        $content = <<<'MARKDOWN'
<div class="p-5 rounded-2xl bg-gradient-to-r from-blue-500/10 via-cyan-500/10 to-transparent border border-blue-500/20 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="space-y-1">
        <span class="text-xs uppercase font-bold tracking-wider text-[#007AFF] dark:text-[#00C4D8] block">Simulasi Titik Impas</span>
        <div class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Kalkulator BEP Online Gratis</div>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">Ketahui berapa porsi atau nominal omzet yang harus Anda raih setiap bulan agar usaha tidak merugi.</p>
    </div>
    <a href="/kalkulator/bep" class="px-5 py-2.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold inline-flex items-center gap-2 shrink-0 shadow-md">
        <span>Buka Kalkulator BEP</span>
        <i data-lucide="calculator" class="w-4 h-4"></i>
    </a>
</div>

<p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-4 flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    <span>Diperbarui per 29 Oktober 2026 &bull; Panduan Analisa Titik Impas Finansial UMKM</span>
</p>

Setiap kali seseorang memutuskan untuk membuka kedai kopi, laundry kiloan, warung makan, maupun toko pakaian, pertanyaan terbesar yang harus pertama kali dijawab bukanlah *"Berapa keuntungan maksimal yang bisa saya raih?"*, melainkan: **"Berapa banyak porsi atau barang yang harus saya jual setiap hari agar bisnis ini tidak merugi dan bisa menutup seluruh biaya operasional?"**

Jawaban dari pertanyaan tersebut adalah **Break Even Point (BEP)**, atau yang di Indonesia dikenal luas sebagai **Titik Impas**.

Banyak pemilik bisnis pemula gulung tikar dalam waktu 3 hingga 6 bulan pertama bukan karena produk mereka tidak enak atau tempat mereka sepi, melainkan karena mereka tidak pernah mengetahui angka BEP usahanya sendiri. Mereka tidak tahu berapa target penjualan harian minimum yang wajib dicapai untuk membayar gaji karyawan, tagihan sewa tempat, dan beban listrik.

Artikel ini membedah secara menyeluruh cara menghitung BEP usaha Anda, baik dalam bentuk unit barang maupun nilai nominal Rupiah, dilengkapi contoh simulasi kafe dan warung makan, cara membaca hasil analisa, serta formula menangani usaha multi-produk.

---

## Daftar Isi
1. [Pengertian Break Even Point (BEP) dan Mengapa Sangat Krusial](#pengertian-bep)
2. [Tiga Elemen Utama dalam Perhitungan BEP](#elemen-bep)
3. [Rumus Baku Menghitung BEP: BEP Unit & BEP Rupiah](#rumus-bep)
4. [Contoh Perhitungan BEP Riil dalam Rupiah](#contoh-bep)
   - [Simulasi 1: BEP Kedai Kopi / Kafe Kecil](#simulasi-kafe)
   - [Simulasi 2: BEP Warung Makan / Nasi Goreng](#simulasi-warung)
   - [Simulasi 3: BEP Usaha Multi-Produk (Bauran Penjualan)](#simulasi-multi-produk)
   - [Simulasi 4: BEP Toko Retail & Minimarket (Nominal Rupiah)](#simulasi-retail)
5. [Hubungan BEP Operasional dengan Payback Period Modal Awal (ROI)](#payback-period)
6. [Cara Membaca Hasil BEP untuk Menentukan Target Penjualan Harian](#target-harian)
7. [3 Strategi Efektif Menurunkan Angka BEP Agar Cepat Balik Modal](#strategi-turunkan-bep)
8. [Pelajari Artikel Analisa BEP Spesifik](#artikel-bep-spesifik)
9. [Pertanyaan yang Sering Diajukan (FAQ BEP)](#faq-bep)

---

<h2 id="pengertian-bep">1. Pengertian Break Even Point (BEP) dan Mengapa Sangat Krusial</h2>

Break Even Point (BEP) adalah kondisi di mana total pendapatan (*revenue*) dari hasil penjualan sama persis dengan total biaya operasional yang dikeluarkan perusahaan. Pada titik ini, perusahaan berada pada posisi laba nol (tidak memperoleh keuntungan, namun juga tidak menderita kerugian).

Mengapa setiap pemilik UMKM wajib menghitung BEP?
* **Menentukan Target Penjualan Realistis**: Mengubah angka target bulanan yang abstrak menjadi target penjualan harian per staf kasir.
* **Dasar Menguji Kelayakan Ide Bisnis**: Menilai apakah lokasi sewa ruko senilai Rp36 juta per tahun masuk akal untuk kapasitas meja kedai kopi Anda.
* **Mengetahui Batas Keamanan Margin (*Margin of Safety*)**: Mengetahui seberapa besar penurunan penjualan yang dapat ditoleransi sebelum toko Anda mulai mengalami defisit kas.
* **Pedoman Mengambil Keputusan Penambahan Staf atau Mesin Baru**: Mengetahui berapa kenaikan omzet yang dibutuhkan untuk menutupi beban gaji karyawan baru.

---

<h2 id="elemen-bep">2. Tiga Elemen Utama dalam Perhitungan BEP</h2>

Sebelum memasukkan angka ke dalam rumus matematika, Anda harus mengelompokkan struktur biaya usaha Anda ke dalam tiga komponen:

### 1. Biaya Tetap (Fixed Cost / FC)
Biaya yang nominalnya konstan dan wajib dibayarkan setiap periode, tidak peduli toko Anda sedang ramai pembeli atau sepi tanpa ada transaksi sama sekali.
* *Contoh*: Biaya sewa tempat/kios, gaji pokok karyawan bulanan, biaya langganan wifi internet, penyusutan peralatan mesin kasir, dan biaya retribusi keamanan lingkungan.

### 2. Biaya Variabel per Unit (Variable Cost / VC)
Biaya yang jumlahnya berubah-ubah secara proporsional mengikuti volume barang atau makanan yang diproduksi dan dijual.
* *Contoh*: Biji kopi, susu segar, cup plastik, sedotan, bumbu masak, kantong kresek, dan komisi penjualan per transaksi. Jika Anda menjual 0 cup, biaya variabel adalah Rp0. Jika menjual 500 cup, biaya variabel naik 500 kali lipat.

### 3. Harga Jual per Unit (Price / P)
Harga yang dibayarkan oleh konsumen untuk satu unit produk atau satu porsi menu yang Anda tawarkan.

---

<h2 id="rumus-bep">3. Rumus Baku Menghitung BEP: BEP Unit & BEP Rupiah</h2>

Terdapat dua rumus utama yang digunakan untuk menghitung titik impas:

### A. Rumus BEP dalam Unit Produk
Digunakan untuk mengetahui berapa **jumlah fisik produk atau porsi** yang harus terjual dalam kurun waktu tertentu:

$$\text{BEP (Unit)} = \frac{\text{Biaya Tetap (Fixed Cost)}}{\text{Harga Jual per Unit} - \text{Biaya Variabel per Unit}}$$

Nilai $(\text{Harga Jual} - \text{Biaya Variabel})$ disebut sebagai **Margin Kontribusi per Unit (Contribution Margin)**, yaitu sisa uang dari setiap penjualan produk yang berfungsi untuk "menutup" tumpukan biaya tetap usaha Anda.

### B. Rumus BEP dalam Nilai Nominal Rupiah
Digunakan untuk mengetahui berapa **angka total omzet kotor (rupiah)** yang harus masuk ke laci kasir:

$$\text{BEP (Rupiah)} = \frac{\text{Biaya Tetap}}{1 - \left(\frac{\text{Biaya Variabel per Unit}}{\text{Harga Jual per Unit}}\right)}$$

Atau disederhanakan menjadi:

$$\text{BEP (Rupiah)} = \text{BEP (Unit)} \times \text{Harga Jual per Unit}$$

---

<h2 id="contoh-bep">4. Contoh Perhitungan BEP Riil dalam Rupiah</h2>

Mari kita telaah simulasi bisnis konkret di lapangan:

<h3 id="simulasi-kafe">Simulasi 1: BEP Kedai Kopi / Kafe Kecil</h3>
Rian membuka kedai kopi dengan rincian operasional bulanan:
* Biaya Tetap (Sewa ruko Rp2.500.000 + Gaji 2 barista Rp4.000.000 + Listrik & Wifi Rp1.500.000) = **Rp8.000.000 per bulan**.
* Harga Jual Kopi Susu per Cup ($P$) = **Rp18.000**.
* Biaya Variabel per Cup ($VC$: Beans, Susu, Sirup, Cup, Sedotan) = **Rp8.000**.
* Margin Kontribusi per Cup = Rp18.000 - Rp8.000 = **Rp10.000 per cup**.

$$\text{BEP (Unit)} = \frac{\text{Rp}8.000.000}{\text{Rp}18.000 - \text{Rp}8.000} = \frac{\text{Rp}8.000.000}{\text{Rp}10.000} = \mathbf{800 \text{ cup per bulan}}$$

$$\text{BEP (Rupiah)} = 800 \text{ cup} \times \text{Rp}18.000 = \mathbf{\text{Rp}14.400.000 \text{ per bulan}}$$

**Artinya**: Dalam satu bulan, kedai kopi Rian harus menjual minimal **800 cup kopi** (atau mengumpulkan omzet Rp14.400.000) hanya untuk balik modal operasional tanpa untung dan tanpa rugi. Setiap penjualan mulai cup ke-801 ke atas adalah keuntungan bersih murni sebesar Rp10.000 per cup untuk pemilik kafe!

Pelajari simulasi khusus kapasitas 15 meja di artikel [Hitung BEP Cafe Kecil 15 Meja](/blog/hitung-bep-cafe-kecil-15-meja) dan analisis harian di [BEP Warung Kopi: Berapa Cangkir per Hari](/blog/bep-warung-kopi-berapa-cangkir-per-hari-untuk-balik-modal).

<h3 id="simulasi-warung">Simulasi 2: BEP Warung Makan / Nasi Goreng</h3>
Warung Nasi Goreng Mas Doni beroperasi setiap malam:
* Biaya Tetap (Sewa tempat Rp1.200.000 + Retribusi sampah & keamanan Rp300.000) = **Rp1.500.000 per bulan**.
* Harga Jual per Porsi Nasi Goreng = **Rp16.000**.
* Biaya Variabel per Porsi (Beras, telur, ayam suwir, minyak, bumbu, styrofoam) = **Rp9.000**.
* Margin Kontribusi = Rp16.000 - Rp9.000 = **Rp7.000 per porsi**.

$$\text{BEP (Unit)} = \frac{\text{Rp}1.500.000}{\text{Rp}7.000} = \mathbf{215 \text{ porsi per bulan}}$$

$$\text{BEP Harian} = \frac{215 \text{ porsi}}{26 \text{ hari buka}} = \mathbf{8,3 \text{ (dibulatkan 9 porsi per malam)}}$$

Mas Doni hanya butuh menjual 9 porsi nasi goreng per malam untuk menutup biaya sewa tempat dan retribusi. Porsi ke-10 dan seterusnya adalah profit bersih!

<h3 id="simulasi-multi-produk">Simulasi 3: BEP Usaha Multi-Produk (Bauran Penjualan)</h3>
Jika toko Anda menjual banyak variasi produk dengan harga dan margin yang berbeda-beda (misalnya toko retail atau restoran dengan menu makanan dan minuman), gunakan perhitungan rasio margin kontribusi rata-rata tertimbang (*Weighted Average Contribution Margin Ratio*). Panduan lengkap perhitungan ini dapat dipelajari di artikel [BEP Multi Produk untuk Usaha UMKM](/blog/bep-multi-produk).

<h3 id="simulasi-retail">Simulasi 4: BEP Toko Retail & Minimarket (Nominal Rupiah)</h3>
Untuk toko retail kelontong, minimarket, atau apotek yang menjual ratusan SKU produk berbeda, menghitung BEP unit per barang sangat tidak realistis. Maka dari itu, rumus BEP Nominal Rupiah adalah senjata utama:

Misalkan Ibu Sri menyewa ruko untuk toko kelontong di perumahan:
* **Biaya Tetap Bulanan**: Sewa ruko Rp2.500.000 + Listrik pendingin & lampu Rp900.000 + Gaji 1 orang pramuniaga Rp2.200.000 + Iuran kebersihan/keamanan Rp150.000 = **Rp5.750.000 per bulan**.
* **Margin Laba Kotor Rata-rata Toko**: 18% dari harga jual (berarti rasio biaya variabel HPP adalah 82%).
* **Rasio Margin Kontribusi (CM Ratio)** = 18% atau 0,18.

$$\text{BEP Penjualan (Rupiah)} = \frac{\text{Rp}5.750.000}{0,18} = \mathbf{\text{Rp}31.944.444 \text{ per bulan}}$$

$$\text{BEP Omzet Harian} = \frac{\text{Rp}31.944.444}{30 \text{ hari}} = \mathbf{\text{Rp}1.064.815 \text{ per hari}}$$

Artinya, laci kasir Ibu Sri wajib mencatat omzet kotor minimal **Rp1.065.000 setiap hari** agar toko kelontong tidak defisit. Setiap rupiah omzet di atas Rp1.065.000 adalah profit bersih yang masuk ke rekening tabungan toko.

---

<h2 id="payback-period">5. Hubungan BEP Operasional dengan Payback Period Modal Awal (ROI)</h2>

Banyak pebisnis pemula keliru mengira bahwa setelah mencapai BEP bulanan, seluruh modal awal pembukaan toko sudah langsung kembali. Perlu dipahami perbedaan fundamental antara dua konsep ini:

1. **BEP Operasional (Titik Impas Berjalan)**: Menghitung berapa penjualan bulanan agar arus kas operasional tidak tekor menutup pengeluaran rutin bulan berjalan.
2. **Payback Period (Balik Modal Investasi Awal)**: Menghitung berapa lama waktu (bulan atau tahun) yang dibutuhkan dari akumulasi laba bersih bulanan untuk mengembalikan dana renovasi ruko, pembelian mesin kasir, AC, freezer, dan biaya awal lainnya.

Misalkan modal renovasi dan perlengkapan awal toko Bu Sri menghabiskan Rp40.000.000. Jika toko Bu Sri menghasilkan laba bersih rata-rata Rp4.000.000 per bulan setelah melampaui BEP operasionalnya:

$$\text{Payback Period} = \frac{\text{Total Modal Awal}}{\text{Laba Bersih Bulanan}} = \frac{\text{Rp}40.000.000}{\text{Rp}4.000.000} = \mathbf{10 \text{ bulan}}$$

Dengan data ini, pemilik toko memiliki kepastian bisnis bahwa seluruh modal awal akan lunas kembali dalam waktu 10 bulan, dan pada bulan ke-11 toko telah menjadi mesin aset penghasil arus kas positif murni.

---

<h2 id="target-harian">6. Cara Membaca Hasil BEP untuk Menentukan Target Penjualan Harian</h2>

Setelah menemukan angka BEP bulanan, langkah paling krusial bagi pemilik usaha adalah **menurunkannya ke target harian**:

$$\text{Target Penjualan Harian} = \frac{\text{BEP (Unit)}}{\text{Jumlah Hari Buka dalam Sebulan}}$$

Pada contoh kedai kopi Rian di atas (BEP 800 cup per bulan dengan 26 hari buka kerja):

$$\text{Target Harian} = \frac{800 \text{ cup}}{26 \text{ hari}} = \mathbf{30,8 \text{ (31 cup per hari)}}$$

Sekarang targetnya menjadi sangat jelas dan membumi:
* Jika kedai kopi menjual **di bawah 31 cup** dalam sehari, hari itu kedai tekor.
* Jika kedai menjual **persis 31 cup**, hari itu impas.
* Jika kedai mampu menjual **60 cup per hari**, pemilik toko mengantongi keuntungan bersih 29 cup x Rp10.000 = **Rp290.000 laba bersih per hari** (sekitar Rp7,5 juta per bulan)!

---

<h2 id="strategi-turunkan-bep">7. 3 Strategi Efektif Menurunkan Angka BEP Agar Cepat Balik Modal</h2>

Jika angka BEP Anda terasa terlalu tinggi dan sulit dicapai, ada 3 tuas bisnis yang bisa Anda tarik:

1. **Turunkan Biaya Tetap (Nego Sewa Tempat & Efisiensi)**: Pilihlah lokasi sewa dengan skema bagi hasil omzet di awal daripada sewa tahunan mahal di depan.
2. **Tekan Biaya Variabel Melalui Negosiasi Bahan Baku**: Beli biji kopi atau beras dalam karung besar langsung dari produsen untuk memotong biaya bahan baku per porsi (meningkatkan margin kontribusi).
3. **Naikkan Nilai Penjualan Rata-rata (*Up-selling & Bundling*)**: Buat paket combo makanan + minuman (misalnya Nasi Goreng + Es Teh Manis seharga Rp20.000) untuk meningkatkan margin kontribusi per transaksi kasir.

---

<h2 id="artikel-bep-spesifik">8. Pelajari Artikel Analisa BEP Spesifik</h2>

* [BEP Multi Produk untuk Toko & Restoran](/blog/bep-multi-produk)
* [BEP Warung Kopi: Target Cangkir Harian](/blog/bep-warung-kopi-berapa-cangkir-per-hari-untuk-balik-modal)
* [Hitung BEP Cafe Kecil 15 Meja Lengkap](/blog/hitung-bep-cafe-kecil-15-meja)
* [Simulasi Titik Impas Online di Kalkulator BEP](/kalkulator/bep)

---

<h2 id="faq-bep">9. Pertanyaan yang Sering Diajukan (FAQ BEP)</h2>

### Apa perbedaan mendasar antara HPP dan BEP?
HPP (Harga Pokok Penjualan) adalah biaya pembuatan per satu unit produk, sedangkan BEP (Break Even Point) adalah titik volume penjualan yang dibutuhkan untuk menutup seluruh biaya tetap dan biaya variabel usaha Anda.

### Apakah gaji pemilik usaha dimasukkan ke dalam biaya tetap saat menghitung BEP?
Ya, sangat disarankan. Gaji pemilik usaha yang wajar harus dimasukkan ke dalam pos Biaya Tetap bulanan agar angka BEP yang dihitung benar-benar menjamin kelangsungan hidup pemilik bisnis dan keluarganya.

### Mengapa toko yang selalu ramai pembeli masih bisa mengalami kerugian?
Karena meskipun volume penjualan tinggi, harga jual produk terlalu dekat dengan biaya variabel (margin kontribusi sangat tipis), sehingga total margin yang terkumpul tetap tidak cukup untuk menutup tingginya biaya tetap (misal sewa ruko mahal atau karyawan terlalu banyak).

### Apakah biaya penyusutan mesin dimasukkan dalam perhitungan BEP?
Ya. Biaya penyusutan mesin kopi, kulkas, atau oven dimasukkan ke dalam pos Biaya Tetap bulanan agar toko memiliki dana cadangan saat peralatan perlu diganti di masa depan.

### Bagaimana cara memantau pencapaian BEP secara otomatis setiap hari?
Gunakan aplikasi kasir dan analitik bisnis COOCA. Dashboard dashboard penjualan otomatis melacak progres omzet harian Anda terhadap target titik impas bulanan secara visual dan real-time langsung dari layar smartphone Anda.

<div class="faq-schema-json" style="display:none;">
[
  {
    "@type": "Question",
    "name": "Apa perbedaan mendasar antara HPP dan BEP?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "HPP adalah biaya pembuatan per satu unit produk, sedangkan BEP adalah titik volume penjualan yang dibutuhkan untuk menutup seluruh biaya tetap dan biaya variabel usaha Anda."
    }
  },
  {
    "@type": "Question",
    "name": "Apakah gaji pemilik usaha dimasukkan ke dalam biaya tetap saat menghitung BEP?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Ya. Gaji pemilik usaha harus dimasukkan ke dalam pos Biaya Tetap bulanan agar titik impas yang tercapai benar-benar menjamin kelayakan operasional dan nafkah pemilik bisnis."
    }
  },
  {
    "@type": "Question",
    "name": "Mengapa toko yang selalu ramai pembeli masih bisa mengalami kerugian?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Karena margin kontribusi produk terlalu tipis, sehingga total margin yang terkumpul tetap tidak mencukupi untuk menutup biaya tetap operasional seperti sewa ruko dan upah karyawan."
    }
  },
  {
    "@type": "Question",
    "name": "Apakah biaya penyusutan mesin dimasukkan dalam perhitungan BEP?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Ya. Biaya penyusutan mesin dan peralatan produksi dialokasikan ke Biaya Tetap bulanan sebagai cadangan modal penggantian alat di masa depan."
    }
  },
  {
    "@type": "Question",
    "name": "Bagaimana cara memantau pencapaian BEP secara otomatis setiap hari?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Aplikasi kasir dan analitik COOCA otomatis melacak pencapaian omzet harian terhadap target impas bulanan secara visual langsung di dashboard smartphone pemilik bisnis."
    }
  }
]
</div>
MARKDOWN;

        $post->content = Str::markdown($content);
        $post->save();
        $this->info("Created/Updated Pillar BEP: /blog/{$slug}");
    }

    private function linkChildrenToPillar(array $childrenSlugs, string $pillarPath, string $anchorText): void
    {
        foreach ($childrenSlugs as $childSlug) {
            $child = Post::where('slug', $childSlug)->first();
            if (!$child) {
                continue;
            }

            if (!str_contains((string) $child->content, $pillarPath)) {
                $box = "\n\n<p class=\"p-4 rounded-xl bg-blue-50 dark:bg-white/5 border border-blue-200 dark:border-white/10 text-sm my-6\"><strong>Panduan Lengkap:</strong> Untuk rumus baku, contoh hitungan lintas industri, dan kalkulator gratis, baca panduan utama kami tentang <a href=\"{$pillarPath}\" class=\"text-[#007AFF] font-bold underline\">{$anchorText}</a>.</p>\n";
                $child->content = $child->content . $box;
                $child->save();
                $this->info("Added backlink in child post: /blog/{$childSlug} -> {$pillarPath}");
            }
        }
    }
}
