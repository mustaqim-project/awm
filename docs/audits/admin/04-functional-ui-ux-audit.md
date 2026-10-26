# Functional & UI/UX Audit - Modul Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Commit: `a17c5ae` | Standar Desain: Apple macOS HIG & Responsive Anti-Slop Guidelines

---

## 1. Cita Rasa Visual & Filosofi Desain Apple macOS HIG

Antarmuka modul Admin dibangun dengan filosofi desain **Apple macOS Human Interface Guidelines (HIG)** yang menghadirkan pengalaman konsol backoffice profesional, tenang, dan terstruktur rapi.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  COOCA Admin Console       [ 🔍 Cari tenant, tiket, user... ]   👤 Superadmin ⏻ │
├───────────────┬─────────────────────────────────────────────────────────────┤
│ SOURCE LIST   │ BENTO KPI STATS                                             │
│ ───────────   │ ┌──────────────┐ ┌──────────────┐ ┌───────────────────────┐ │
│ 🏠 Dashboard  │ │ Total Tenant │ │ Menunggu Rev │ │ Omzet MRR Langganan   │ │
│ 🏢 Bisnis     │ │ 1.420 Aktif  │ │ 3 Tiket      │ │ Rp 182.500.000        │ │
│ 👥 Pengguna   │ └──────────────┘ └──────────────┘ └───────────────────────┘ │
│ 🪪 Pemulihan  │                                                             │
│ 💳 Pembayaran │ DATA DESK / SEGMENTED CONTROLS & FILTER TABLE               │
│ ⚙️ Pengaturan │ [ Semua ] [ Aktif ] [ Suspend ]   [ 📥 Ekspor Laporan CSV ] │
│               │ ┌─────────────────────────────────────────────────────────┐ │
│               │ │ Nama Bisnis     │ Pemilik       │ Paket     │ Status    │ │
│               │ ├─────────────────┼───────────────┼───────────┼───────────┤ │
│               │ │ Kopi Senja      │ Hendra G.     │ Prestige  │ ● Aktif   │ │
│               │ └─────────────────┴───────────────┴───────────┴───────────┘ │
└───────────────┴─────────────────────────────────────────────────────────────┘
```

### Elemen Utama Desain:
1. **macOS Source List Sidebar:**
   - Navigasi sidebar terorganisir ke dalam grouping fungsional yang intuitif: *Utama*, *Manajemen Platform*, *Operasional Bisnis*, *Komunikasi & Bantuan*, dan *Konfigurasi Sistem*.
   - Seluruh ikon menggunakan tema warna biru konsisten (`#007AFF` / `#0A84FF`) dengan glyph Lucide Icons berukuran presisi 18x18px.
2. **Topbar Ergonomis:**
   - Fungsi pencarian global terintegrasi dalam kapsul tengah (*Search Capsule*) dengan tombol pintas keyboard `⌘K` / `Ctrl+K`.
   - Tombol logout dan identitas admin dipusatkan pada topbar kanan dengan modal konfirmasi *No-Panic*.
3. **Bento Grid Squircle Surfaces:**
   - Setiap kartu metrik dan meja kerja menggunakan sudut lengkung *Apple Squircle* (`rounded-[20px]` hingga `rounded-[24px]`).
   - Lapisan kaca *frosted glass* (`backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80`) dengan border subtil `border-black/[0.06] dark:border-white/[0.08]`.

---

## 2. Ergonomi Layar Sentuh & Anti-Zoom Mobile

Meskipun modul admin utamanya diakses melalui layar desktop laptop/PC, seluruh antarmuka telah dioptimasi penuh agar dapat dioperasikan secara darurat melalui perangkat mobile (iPhone / Android) saat admin sedang bertugas di luar kantor:

| Atribut Ergonomi | Standar Minimum | Implementasi Modul Admin | Status |
| :--- | :---: | :--- | :---: |
| **Input Anti-Zoom** | `font-size >= 16px` | Kelas `text-[16px] sm:text-xs` pada seluruh field form dan search bar mencegah auto-zoom di iOS Safari. | **PASS** |
| **Tap Target Height** | Minimal `44px` | Tombol aksi primer, tab segmented, dan kontrol dropdown memiliki `min-h-[44px]` atau `min-h-[48px]`. | **PASS** |
| **Thumb Zone Reach** | Mudah dijangkau jempol | Kontrol filter dan tombol persetujuan cepat diletakkan di bagian bawah yang mudah diakses jempol tangan satu. | **PASS** |
| **Scrollbar Estetik** | Halus tanpa overflow | Kelas utilitas kustom `.sidebar-scroll` dengan thumb tipis semi-transparan `4px`. | **PASS** |

---

## 3. Pencegahan Kesalahan Manusia (*No-Panic Ergonomics*)

Tindakan berisiko tinggi yang dapat memengaruhi keberlangsungan tenant dilengkapi dengan dialog pengaman berlapis:
1. **Impersonasi Tenant:** Membuka jendela pratinjau yang menjelaskan konsekuensi operasional serta menyediakan banner merah mencolok yang selalu tampil untuk mengakhiri sesi.
2. **Persetujuan / Penolakan Pemulihan Akun:** Modal konfirmasi mewajibkan pengisian catatan alasan verifikasi sebelum tombol eksekusi aktif, mencegah salah klik yang tidak disengaja.
3. **Hapus Pengguna / Nonaktifkan Bisnis:** Aksi penghapusan menggunakan *soft deletes* (`deleted_at`), memastikan data historis transaksi keuangan dan jurnal tidak terhapus permanen dari basis data.

---

## 4. Penanganan Mode Gelap (*Dark Mode*) & Zero FOUC

Di dalam tag `<head>` layout [`resources/views/layouts/admin.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/admin.blade.php), tersemat skrip *Instant Theme Bootstrap*:

```javascript
(function() {
    try {
        var theme = localStorage.getItem('cooca-admin-theme') || 'light';
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (theme === 'dark' || (theme === 'system' && prefersDark)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    } catch (e) {}
})();
```

Skrip ini dieksekusi secara sinkron sebelum proses perenderan DOM dimulai, mengeliminasi fenomena *Flash of Unstyled Content* (FOUC) saat memuat halaman dalam mode gelap.
