# System Baseline & Component Inventory - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## 1. Ikhtisar Arsitektur Modul
Modul Pengaturan Bisnis (Settings Hub) COOCA dibangun dengan pendekatan berlapis (*Domain-Driven & Multi-Tenant Architecture*) di dalam ekosistem Laravel 11. Modul ini bertanggung jawab atas:
- Manajemen profil legal, nama usaha, alamat, nomor telepon, logo merek, dan identitas perusahaan.
- Parameter operasional kasir (POS), pajak penjualan (PPN/PB1), catatan kaki struk belanja, dan pembulatan HPP (*COGS rounding*).
- Konfigurasi rekening bank penerima pembayaran dan PIN supervisor kasir untuk proteksi void/refund.
- Master cabang (*locations*) multi-outlet, koordinat geofencing GPS, jam operasional, dan fulfillment online.
- Template aktivasi industri dan saklar modul bisnis yang dinamis.
- Manajemen integrasi ekosistem AI Model Context Protocol (MCP) yang menghubungkan COOCA ke asisten AI terpercaya (**Claude**, **ChatGPT**, **Gemini**).
- Manajemen integrasi WhatsApp Cloud API untuk pengiriman faktur & struk belanja digital.

## 2. Inventaris Controller & Handler
| File Path | Deskripsi & Peran | Guard / Middleware RBAC |
|---|---|---|
| `app/Http/Controllers/Web/SettingWebController.php` | Controller utama yang menangani antarmuka pengaturan umum, cabang outlet, rekening bank, parameter POS, dan pemilihan template bisnis. | `implements HasMiddleware` (`auth`, `require.permission:settings.view`, `require.permission:settings.edit`, `require.role:owner`, `require.permission:users.manage`) |
| `app/Http/Controllers/Web/Mcp/McpIntegrationWebController.php` | Controller kokpit integrasi AI Model Context Protocol (MCP), manajemen token akses, panduan konfigurasi Claude/ChatGPT/Gemini, dan Master Switch. | `implements HasMiddleware` (`auth`, `require.permission:settings.view`, `require.permission:settings.edit`) |

## 3. Inventaris Domain Services
| Service Class | Lokasi Berkas | Fungsi Utama |
|---|---|---|
| `EntitlementService` | `app/Domain/Billing/EntitlementService.php` | Validasi kuota pembuatan cabang outlet (`canCreateLocation`) dan kuota anggota tim sesuai tier paket langganan. |
| `McpToolRegistry` | `app/Domain/Mcp/McpToolRegistry.php` | Registry perkakas AI MCP terdaftar yang mengekspos domain bisnis ke agen AI secara aman. |
| `StorageTrackingService` | `app/Domain/Storage/StorageTrackingService.php` | Pengelolaan penyimpanan logo perusahaan pada disk publik dan pemantauan kuota penyimpanan tenant. |
| `TimezoneHelper` | `app/Support/TimezoneHelper.php` | Normalisasi zona waktu Indonesia (Asia/Jakarta, Asia/Makassar, Asia/Jayapura) dan validasi struktur JSON jam operasional toko. |

## 4. Inventaris Model & Entitas Database
| Model Eloquent | Tabel Database | Keterangan Relasi Multi-Tenant |
|---|---|---|
| `Business` | `businesses` | Entitas induk tenant, menyimpan profil usaha, rekening bank, logo, PIN POS, dan daftar modul aktif. |
| `Location` | `locations` | Master cabang outlet dan gudang toko fisik dengan titik koordinat latitude, longitude, dan radius geofence. |
| `McpAccessToken` | `mcp_access_tokens` | Token otentikasi AI MCP berformat hash SHA-256, cakupan izin (*abilities*), dan provider hint (claude, chatgpt, gemini). |
| `McpActivityLog` | `mcp_activity_logs` | Buku besar pemanggilan perkakas AI MCP mencatat nama tool, provider, durasi, dan status eksekusi. |
| `WhatsAppSession` | `whats_app_sessions` | Konfigurasi sesi WABA Cloud API untuk pengiriman otomatis struk POS dan template pesan WhatsApp. |
| `CommerceStoreSetting`| `commerce_store_settings` | Sinkronisasi lokasi cabang utama dengan titik asal pengiriman logistik Biteship. |
| `AuditLog` | `audit_logs` | Jejak audit keamanan mencatat siapa melakukan apa, waktu, IP address, nilai sebelum/sesudah, dan level risiko. |

## 5. Inventaris Rute Web (`routes/web.php`)
```
GET    /settings                           settings.index
PUT    /settings                           settings.update
POST   /settings/branches                  settings.branches.store
PUT    /settings/branches/{location}       settings.branches.update
DELETE /settings/branches/{location}       settings.branches.destroy
POST   /settings/apply-template            settings.apply-template
POST   /settings/modules                   settings.modules.update
DELETE /settings/members/{member}          settings.members.destroy

GET    /settings/integrations/mcp          settings.integrations.mcp.index
POST   /settings/integrations/mcp/tokens   settings.integrations.mcp.tokens.store
PUT    /settings/integrations/mcp/tokens/{token} settings.integrations.mcp.tokens.update
DELETE /settings/integrations/mcp/tokens/{token} settings.integrations.mcp.tokens.revoke
POST   /settings/integrations/mcp/toggle   settings.integrations.mcp.toggle
GET    /settings/integrations/mcp/logs     settings.integrations.mcp.logs
```

## 6. Inventaris Berkas Tampilan (Blade Views)
| Berkas Blade | Lokasi | Peran Antarmuka |
|---|---|---|
| `index.blade.php` | `resources/views/app/settings/index.blade.php` | Halaman utama Pengaturan Bisnis berbasis tab Bento: Profil Umum, Cabang & Outlet, Keuangan & POS, Modul & Fitur. |
| `mcp.blade.php` | `resources/views/app/settings/integrations/mcp.blade.php` | Cockpit komando integrasi AI MCP, Master Kill Switch, manajemen token, dan panduan konfigurasi Claude, ChatGPT, Gemini. |
| `whatsapp.blade.php` | `resources/views/app/settings/integrations/whatsapp.blade.php` | Pengaturan WhatsApp Cloud API Gateway, format struk belanja POS, dan pratinjau pesan. |
| `index.blade.php` (Integrations) | `resources/views/app/settings/integrations/index.blade.php` | Hub daftar integrasi pihak ketiga (WABA, MCP AI, Marketplace, Payment Gateway). |
| `index.blade.php` (Members) | `resources/views/app/settings/members/index.blade.php` | Manajemen daftar anggota staf, peran (Owner/Manager/Staff), dan penetapan hak akses. |
