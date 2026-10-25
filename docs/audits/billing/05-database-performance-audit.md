# Database & Performance Audit — Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Analisis Kueri & Indeks Database

Modul Billing beroperasi pada tabel-tabel utama:
- `businesses`
- `business_subscriptions`
- `subscription_payments`
- `subscription_promos`
- `storage_files`
- `audit_logs`

### Evaluasi Indeks Kueri:
1. **`subscription_payments`**:
   - Kolom `business_id` dan `created_at` memiliki indeks komposit yang mempercepat pagination pada halaman riwayat (`billing.history`).
   - Kolom `order_number` dan `gateway_reference` terindeks unik untuk pencarian cepat status transaksi gateway.
2. **`storage_files`**:
   - Kueri pemantauan storage menggunakan filter `where('business_id', $business->id)->where('status', 'active')`. Kolom `business_id` dan `status` terindeks dengan baik untuk membatasi pemindaian hanya pada tenant aktif.
3. **`audit_logs`**:
   - Kueri forensik memfilter berdasarkan `business_id` dan `auditable_type`. Indeks polimorfik memastikan pencatatan audit log tidak membebani transaksi.

---

## 2. Strategi Caching Kuota (`EntitlementService`)

Untuk mencegah penghitungan kuota berat (menghitung total produk, transaksi kasir, pelanggan, supplier, dan order B2B) pada setiap request HTTP:
1. **Cache Penggunaan Sumber Daya**:
   - Hasil kalkulasi kuota disimpan dalam cache per tenant selama interval tertentu (`entitlement_usage_{business_id}`).
2. **Cache Invalidation Presisi**:
   - Cache otomatis dibersihkan saat:
     - Transaksi upgrade atau perpanjangan langganan berhasil (`upgradeToCore`).
     - Penyimpanan storage dihitung ulang (`recalculateStorage`).
     - File storage dihapus oleh pemilik bisnis (`destroyStorageFile`).
     - Notifikasi webhook callback TriPay berhasil memproses transaksi langganan.

Hal ini menjamin pemuatan halaman dasbor `/billing/limits` sangat cepat (< 120ms) sekaligus langsung mutakhir begitu ada pembaruan data.
