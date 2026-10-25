# Risk Register — Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Matriks Risiko & Status Mitigasi

| ID | Kategori | Deskripsi Risiko | Severity Awal | Mitigasi yang Diterapkan | Severity Akhir | Status |
|---|---|---|:---:|---|:---:|:---:|
| **RSK-01** | Keamanan / RBAC | Ketiadaan `HasMiddleware` pada controller billing; hanya bertumpu pada rute middleware. | **CRITICAL** | Implementasi `HasMiddleware` pada `BillingAndLimitWebController` dan `SubscriptionCheckoutWebController` dengan guard `billing.view` dan `billing.manage`. | LOW | **MITIGATED** |
| **RSK-02** | Fraud / Integritas | Ketiadaan jejak audit saat order langganan dibuat, storage dihitung ulang, atau file dihapus. | **HIGH** | Pencatatan otomatis ke `AuditLog::create` dengan level risiko terkalibrasi dan metadata perubahan. | LOW | **MITIGATED** |
| **RSK-03** | Multi-Tenancy / IDOR | Potensi IDOR pada parameter invoice dan penghapusan storage lintas tenant. | **HIGH** | Pengecekan ketat `business_id === $business->id` di seluruh endpoint sensitif. Diverifikasi oleh feature test IDOR. | NEGLIGIBLE | **MITIGATED** |
| **RSK-04** | UI / Ergonomi | Polusi visual metrik meja kasir dine-in pada bisnis non-F&B (ritel, jasa). | **HIGH** | Penambahan dynamic context-aware filter `@if($business->hasDineInFeature())` pada seluruh kartu dan matriks. | NEGLIGIBLE | **MITIGATED** |
| **RSK-05** | Performa / Gateway | Risiko race condition saat webhook callback TriPay tiba bersamaan dengan polling status pengguna. | **MEDIUM** | Penggunaan database transaction dan status guard idempotensi di domain service. | LOW | **MITIGATED** |
| **RSK-06** | UI / Modal | Modal storage manager dan kuota terpotong oleh overflow kontainer induk. | **MEDIUM** | Implementasi `<template x-teleport="body">` dengan `z-[220]`. | NEGLIGIBLE | **MITIGATED** |
| **RSK-07** | Kuota Disk | File yatim (*orphaned files*) memakan kuota disk tanpa tercatat di database. | **LOW** | Mekanisme `recalculateStorage` yang memindai disk fisik dan menyelaraskan status DB. | LOW | **MITIGATED** |
