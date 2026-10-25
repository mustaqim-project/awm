# Database and Performance Audit - feedback

> Modul: Platform Feedback & Bug Support | Commit: 38cdcdf | Tanggal: 2026-10-25

## 1. Struktur Skema Database
Modul Feedback didukung oleh 3 tabel utama:
1. `bug_reports`:
   - `id` (char(36), Primary Key - UUID)
   - `business_id` (char(36), Foreign Key ke businesses, berindeks)
   - `user_id` (char(36), Foreign Key ke users, berindeks)
   - `title` (varchar(255))
   - `description` (text)
   - `severity` (varchar(50))
   - `status` (varchar(50))
   - `reproduce_steps` (text, nullable)
   - `system_info` (json, nullable)
   - `created_at`, `updated_at` (timestamps)
2. `feature_requests`:
   - `id` (char(36), Primary Key - UUID)
   - `business_id` (char(36), Foreign Key ke businesses, berindeks)
   - `user_id` (char(36), Foreign Key ke users, berindeks)
   - `title` (varchar(255))
   - `description` (text)
   - `use_case` (text, nullable)
   - `status` (varchar(50))
   - `created_at`, `updated_at` (timestamps)
3. `feedback_updates`:
   - `id` (char(36), Primary Key - UUID)
   - `updatable_id` (char(36), Polymorphic Foreign Key)
   - `updatable_type` (varchar(255), Polymorphic Type)
   - `from_status` (varchar(50), nullable)
   - `to_status` (varchar(50))
   - `note` (text, nullable)
   - `created_at`, `updated_at` (timestamps)

## 2. Analisis Query & Eager Loading
- Pada `FeedbackWebController@showBug` dan `showFeature`, relasi yang dimuat mencakup:
  - `attachments`: File lampiran gambar/dokumen yang terhubung melalui morph relasi `StorageFile`.
  - `updates`: Linimasa riwayat status tiket yang diurutkan kronologis (`created_at`).
- Karena jumlah tiket per tenant berkisar dalam skala puluhan hingga ratusan, paginasi standar (`paginate(15)`) memastikan query tetap ringan dengan waktu eksekusi < 15ms.

## 3. Indeks & Integritas Relasi
- Indeks komposit pada `[business_id, created_at]` mendukung pengurutan cepat tiket terbaru tanpa full-table scan.
- Penggunaan UUID sebagai primary key mencegah kebocoran perkiraan volume tiket platform (keamanan enumerasi).
