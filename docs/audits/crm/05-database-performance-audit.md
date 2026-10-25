# Database and Performance Audit - crm

> Modul: CRM & Loyalty | Tanggal: 2026-10-25

## 1. Analisis Skema Database & Relasi Model

| Tabel | Primary Key | Foreign Keys / Scopes | Keterangan Indeks & Optimasi |
| :--- | :--- | :--- | :--- |
| `customers` | UUID (`id`) | `business_id` | Menggunakan compound index `(business_id, phone)` dan `(business_id, code)` untuk pencarian instan member kasir. |
| `customer_point_histories` | UUID (`id`) | `customer_id`, `business_id` | Terindeks pada `(customer_id, created_at)` untuk pagination cepat riwayat poin member. |
| `customer_credit_transactions` | UUID (`id`) | `customer_id`, `business_id` | Terindeks pada `(customer_id, transaction_date)` untuk rekap piutang pelanggan. |
| `vouchers` | UUID (`id`) | `business_id` | Unique index pada `(business_id, code)` memastikan validasi kode voucher instan per tenant. |
| `cash_transactions` | UUID (`id`) | `business_id`, `cash_account_id` | Menampung mutasi kas masuk pelunasan piutang pelanggan. |
| `audit_logs` | UUID (`id`) | `business_id`, `(auditable_type, auditable_id)` | Polimorfik index untuk penelusuran audit trail kasbon dan voucher. |

## 2. Analisis Query & Penanganan N+1
- Pada `CrmWebController::members`:
  - Menggunakan query pagination `paginate(20)` dengan pencarian terindeks (`LIKE %search%` pada kolom nama, telepon, dan kode).
  - Mengurangi beban memori dengan melakukan filter langsung di level database query builder.
- Pada `CrmWebController::pointHistories`:
  - Mengambil riwayat poin terbaru dibatasi `limit(50)` untuk menjaga responsivitas modal AJAX tanpa membebani bandwidth jaringan.
- Pada `CrmWebController::vouchers`:
  - Mengambil data voucher tenant aktif diurutkan berdasarkan `created_at desc` dengan paginasi efisien.

## 3. Integritas Transaksional (ACID)
- Seluruh mutasi keuangan pada `LoyaltyService::recordCustomerCreditPayment` dibungkus dalam `DB::transaction`.
- Jika terjadi kegagalan sistem saat menulis mutasi `cash_transactions`, pengurangan saldo piutang pelanggan otomatis di-rollback tanpa menyisakan *inconsistent state*.
