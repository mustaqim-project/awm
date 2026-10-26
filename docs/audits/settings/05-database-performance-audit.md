# Database & Performance Audit - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## 1. Skema Basis Data & Indeks Performa
Modul Pengaturan Bisnis mengandalkan relasi multi-tenant yang terisolasi dengan kunci utama UUID dan foreign key `business_id`:

### Tabel `businesses`
- **Primary Key:** `id` (char(36) UUID).
- **Indeks Kritis:**
  - `slug` (unique index): Pencarian cepat profil bisnis untuk halaman katalog online.
  - `status`: Filter bisnis aktif/nonaktif.
- **Karakteristik:** Menggunakan trait `SoftDeletes` dan `Auditable` untuk pencatatan otomatis riwayat perubahan.

### Tabel `locations`
- **Primary Key:** `id` (char(36) UUID).
- **Indeks Kritis:**
  - `business_id` (foreign key index): Isolasi query multi-tenant.
  - `parent_id` (foreign key index): Relasi hierarkis outlet toko ke gudang sentral.
  - `slug`: Unik per tenant.
  - `(latitude, longitude)`: Optimasi query perhitungan jarak geofencing presensi staf dan biaya kirim kurir instan.
  - `is_primary`, `is_active`: Filter cepat cabang aktif dan outlet utama.

### Tabel `mcp_access_tokens`
- **Primary Key:** `id` (char(36) UUID).
- **Indeks Kritis:**
  - `business_id` (foreign key index): Menghindari full table scan saat evaluasi Master Kill Switch.
  - `token_hash` (unique index): Pencarian instan otentikasi Bearer token API MCP dengan kompleksitas $O(1)$.
  - `(business_id, is_active)`: Indeks komposit untuk agregasi jumlah token aktif di kokpit.

### Tabel `mcp_activity_logs`
- **Primary Key:** `id` (bigint auto-increment).
- **Indeks Kritis:**
  - `business_id` (foreign key index): Isolasi log per tenant.
  - `created_at`: Paginasi cepat log aktivitas terkini dan agregasi pemanggilan bulanan.
  - `(business_id, created_at)`: Indeks komposit untuk grafik throughput perkakas AI.

## 2. Optimasi Query & Eager Loading
Pada controller `McpIntegrationWebController@index`:
```php
$tokens = McpAccessToken::where('business_id', $business->id)
    ->with(['user'])
    ->latest()
    ->get();
```
- Penggunaan `with(['user'])` mengeliminasi potensi **N+1 Query Problem** saat menampilkan nama pembuat token pada tabel riwayat.
- Paginasi `McpActivityLog::where('business_id', $business->id)->latest()->paginate(15)` menjamin konsumsi memori tetap konstan terlepas dari akumulasi ratusan ribu log AI di masa depan.

## 3. Transaksi Atomik & Integritas Referensial
1. **Penetapan Cabang Utama (*Primary Branch Switch*):**
   Saat pengguna menetapkan cabang A sebagai *primary*, sistem mengeksekusi operasi atomik untuk mereset seluruh cabang lain menjadi `is_primary = false` sebelum memperbarui cabang A, menjamin tidak pernah terjadi duplikasi cabang utama.
2. **Sinkronisasi Biteship Logistics:**
   Data alamat, titik koordinat, dan `biteship_area_id` cabang utama disinkronkan ke tabel `commerce_store_settings` secara konsisten dalam proses yang sama.
3. **Master Kill Switch AI:**
   Metode `toggleMaster` mengeksekusi *bulk update* satu baris query:
   ```php
   McpAccessToken::where('business_id', $business->id)->update(['is_active' => false]);
   ```
   menjamin seluruh token mati dalam hitungan milidetik tanpa *race condition*.
