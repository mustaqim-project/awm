# Security & Fraud Audit - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## 1. Analisis Model Ancaman (Threat Modeling)
Modul Pengaturan Bisnis adalah target berharga bagi penyerang internal maupun eksternal karena menyimpan identitas legal perusahaan, rekening pencairan dana, otorisasi kasir, dan akses ke kecerdasan buatan (AI):
- **Threat 1: Financial Redirection (Pengalihan Rekening Bank):** Pengguna dengan akses terbatas mencoba mengubah rekening bank bisnis ke rekening pribadi penipu.
- **Threat 2: POS Supervisor PIN Tampering:** Staf kasir mengubah PIN supervisor untuk meloloskan transaksi void fiktif atau pencurian kas.
- **Threat 3: AI Data Exfiltration via MCP Tokens:** Pencurian token akses AI pihak ketiga untuk menyedot data pelanggan, inventori, dan laporan keuangan.
- **Threat 4: Insecure Direct Object References (IDOR):** Memanipulasi ID cabang (`location_id`) atau token MCP (`token_id`) milik bisnis lain.
- **Threat 5: Denial of Service via Token Flooding:** Pembuatan token AI tanpa batas untuk menghabiskan kuota atau memicu overhead pemrosesan.

## 2. Implementasi Controller-Level RBAC (`HasMiddleware`)
Sebelum audit, perlindungan otorisasi hanya bergantung pada middleware rute global. Kini kedua controller inti telah mengimplementasikan interface `HasMiddleware` secara deklaratif:

### `SettingWebController.php`
```php
public static function middleware(): array
{
    return [
        'auth',
        new Middleware('require.permission:settings.view', only: ['index']),
        new Middleware('require.permission:settings.edit', only: [
            'update',
            'storeBranch',
            'updateBranch',
            'destroyBranch',
        ]),
        new Middleware('require.role:owner', only: [
            'applyTemplate',
            'updateModules',
        ]),
        new Middleware('require.permission:users.manage', only: [
            'destroyMember',
        ]),
    ];
}
```

### `McpIntegrationWebController.php`
```php
public static function middleware(): array
{
    return [
        'auth',
        new Middleware('require.permission:settings.view', only: ['index', 'logs']),
        new Middleware('require.permission:settings.edit', only: [
            'storeToken',
            'updateToken',
            'revokeToken',
            'toggleMaster',
        ]),
    ];
}
```

## 3. Proteksi Parameter Finansial & PIN Supervisor
1. **Hashing PIN Supervisor:** Nilai `pos_supervisor_pin` tidak pernah disimpan dalam bentuk teks polos (*plaintext*). Sistem menggunakan algoritma `Bcrypt` via `Hash::make()`.
2. **Masking Jejak Audit:** Saat parameter sensitif diperbarui, nilai PIN di-masking menjadi `••••••` pada catatan `old_values` dan `new_values`.
3. **Perekaman Level Risiko Tinggi (*RISK_HIGH*):** Perubahan nomor rekening bank atau PIN supervisor kasir otomatis diberi tag `risk_level = AuditLog::RISK_HIGH` agar mudah dipantau di audit trail dashboard pemilik bisnis.

## 4. Keamanan Token Ekosistem AI MCP
1. **Penyimpanan Hash SHA-256:** Basis data `mcp_access_tokens` hanya menyimpan `token_hash` hasil fungsi satu arah `hash('sha256', $plainToken)`. Token polos hanya ditampilkan satu kali ke layar pengguna saat pertama kali dibuat.
2. **Cakupan Izin Granular (*Abilities Scoping*):** Setiap token dibatasi oleh hak akses spesifik (misal: hanya membaca produk `mcp:products:read` atau membaca keuangan `mcp:finance:read`), mencegah AI melakukan aksi di luar otorisasi yang diberikan.
3. **Master Kill Switch (Saklar Darurat):** Fitur `toggleMaster` memungkinkan penonaktifan instan seluruh token AI aktif milik tenant dalam satu transaksi database, dicatat langsung ke `audit_logs` dengan tingkat risiko tinggi.

## 5. Isolasi Multi-Tenant & Pencegahan IDOR
- Seluruh query database dibatasi oleh `Context::requireBusiness()`.
- Pada setiap handler rute parameter bound seperti `updateBranch(Request $request, Location $location)` dan `destroyBranch(Location $location)`, controller melakukan pengecekan tegas:
  ```php
  abort_unless($location->business_id === $business->id, 403);
  ```
- Percobaan memanipulasi UUID cabang atau token bisnis lain akan langsung menghasilkan respon `403 Forbidden` atau `404 Not Found`.

## 6. Pemetaan Kepatuhan OWASP Top 10
| Kategori OWASP | Mitigasi yang Diterapkan di Modul Pengaturan |
|---|---|
| **A01: Broken Access Control** | Controller-Level RBAC `HasMiddleware` + Isolasi Tenant `Context::requireBusiness()` + Pengecekan kepemilikan relasional pada cabang dan token MCP. |
| **A02: Cryptographic Failures** | Bcrypt hash untuk PIN supervisor, SHA-256 hash untuk MCP token, SSL/TLS untuk seluruh webhook dan endpoint MCP. |
| **A03: Injection** | Parameterized query via Eloquent ORM, zero unescaped tags `{!! !!}` pada template Blade, dan sanitasi input. |
| **A05: Security Misconfiguration** | Master AI Kill Switch untuk mitigasi darurat, pembatasan provider AI hanya pada vendor terverifikasi (Claude, ChatGPT, Gemini). |
| **A07: Identification and Authentication Failures** | Session auth + granular API token permissions + pencatatan waktu kadaluarsa token. |
| **A09: Security Logging and Monitoring Failures** | Perekaman otomatis ke `audit_logs` untuk seluruh mutasi profil, rekening, PIN, cabang, dan token AI. |
