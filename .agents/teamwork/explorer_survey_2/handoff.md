# Admin Views Architecture & Inventory Survey Report

**Survey Subject:** All Blade templates under `resources/views/admin` (87 files, 40,409 lines)  
**Agent Role:** Explorer 2 (Admin Views Architecture & Inventory Survey)  
**Date:** 2026-10-31  
**Integrity Mode:** Development (Read-Only Investigation)  

---

## 1. Observation

### 1.1 Scope & Inventory Overview
An exhaustive scan of `c:\laragon\www\cooca_core\resources\views\admin` was conducted.
- **Total Blade templates:** 87 files
- **Total Lines of Code:** 40,409 lines
- **Total Submodules / Directories:** 30 distinct submodules (29 subdirectories + 1 root template)
- **Current Localization Status:**
  - Files with existing `__()`, `@lang()`, or `trans()` calls: **0 files (0%)**
  - Files with hardcoded text: **86 files (98.9%)**
  - Clean files (per `python scratch/deep_audit_v2.py resources/views/admin`): **1 file** (`admin/smtp/index.blade.php`, which is a 12-line redirect stub without text)
- **Hardcoded Issues Detected by `deep_audit_v2.py`:** **947 issues** across 86 files.

### 1.2 Breakdown of Detected Issues by Domain (`deep_audit_v2.py`)
- **Domain 7 (Buttons & Action Links):** 551 issues (414 raw button labels, 117 raw `title="..."`, 7 raw `aria-label="..."`, 13 raw `alt="..."`)
- **Domain 3 (Headers, H1-H6, & Subtitles):** 127 issues (12 `h1`, 20 `h2`, 73 `h3`, 22 `h4`)
- **Domain 4 (Forms, Labels, & Hints):** 117 issues (all raw `placeholder="..."` attributes)
- **Domain 9 (Tables, Badges, & Empty States):** 69 issues (raw empty state notices e.g. "Belum ada...")
- **Domain 1 (Page Title & Meta Tags):** 39 issues (35 raw `@extends(..., ['title' => '...'])`, 4 raw `@section('title', '...')`)
- **Domain 5 (Dev / Test Alerts):** 27 issues (e.g. "Bypass / Pengujian:")
- **Domain 6 (Error Lists & Summary):** 17 issues (e.g. "Verifikasi Belum Berhasil")

### 1.3 Submodule Architecture & Distribution Map
The 87 files are distributed across 30 submodules as follows (ordered by volume):

| No | Submodule | Files | Lines | Alpine.js | Modals | Tables | Forms | Primary Function |
|---|---|---|---|---|---|---|---|---|
| 1 | `settings` | 11 | 4,793 | 11 | 1 | 0 | 10 | Platform configs (SEO, SMTP, WhatsApp, Branding, Payment) |
| 2 | `inbox` | 1 | 3,818 | 1 | 1 | 0 | 1 | Unified customer omnichannel chat & AI support console |
| 3 | `finance` | 18 | 3,356 | 18 | 10 | 6 | 9 | Ledger, journals, AP/AR, cashflow, balance sheet |
| 4 | `whatsapp` | 2 | 3,287 | 2 | 1 | 2 | 3 | Meta Cloud API v26.0, broadcast campaigns & templates |
| 5 | `social_media` | 1 | 3,006 | 1 | 1 | 1 | 9 | Multi-platform publishing, TikTok/LinkedIn/YouTube integration |
| 6 | `b2b` | 11 | 2,237 | 11 | 11 | 4 | 11 | Sales orders, quotations, invoices, return slips |
| 7 | `ai_tokens` | 1 | 1,869 | 1 | 1 | 1 | 6 | Global AI token quota monitoring & API key rotation |
| 8 | `_root` (`dashboard`) | 1 | 1,636 | 1 | 1 | 1 | 2 | Primary superadmin dashboard & quick actions sheet |
| 9 | `posts` | 3 | 1,626 | 0 | 3 | 1 | 11 | CMS Blog articles, categories & media manager |
| 10 | `pricing-programs`| 2 | 1,570 | 1 | 1 | 2 | 10 | Early adopter pricing, promo campaigns & billing simulation |
| 11 | `account-recoveries`| 2 | 1,504 | 2 | 2 | 1 | 5 | Tenant owner account recovery & identity verification |
| 12 | `events` | 4 | 1,137 | 3 | 0 | 2 | 7 | Community events, webinars & ticketing |
| 13 | `subscriptions` | 2 | 1,041 | 2 | 2 | 1 | 4 | Tenant subscription orders & payment verification |
| 14 | `billing-packages`| 1 | 1,039 | 1 | 1 | 1 | 6 | Tier pricing packages & addon quota config |
| 15 | `payment_accounts`| 3 | 911 | 3 | 1 | 1 | 6 | Platform bank accounts, QRIS & e-wallets |
| 16 | `settlements` | 2 | 856 | 2 | 1 | 2 | 5 | Merchant revenue withdrawals & payout approvals |
| 17 | `ai-studio` | 2 | 821 | 2 | 2 | 1 | 5 | AI prompt studio & generative image sources |
| 18 | `templates` | 3 | 795 | 2 | 0 | 1 | 7 | Excel master templates CMS |
| 19 | `businesses` | 2 | 769 | 2 | 2 | 1 | 9 | Workspace & tenant master management |
| 20 | `roles` | 1 | 726 | 1 | 1 | 1 | 3 | RBAC roles, permissions matrix & guard assignment |
| 21 | `promos` | 1 | 660 | 1 | 1 | 1 | 6 | Discount vouchers, promo codes & redemption limits |
| 22 | `word_filters` | 1 | 540 | 1 | 1 | 1 | 7 | Content moderation keyword blacklist |
| 23 | `auth` | 3 | 500 | 2 | 0 | 0 | 3 | Admin login, forgot-password, reset-password |
| 24 | `error-logs` | 1 | 479 | 1 | 1 | 0 | 2 | System error logs & stack trace viewer |
| 25 | `legal-pages` | 2 | 423 | 1 | 0 | 0 | 2 | Privacy policy & terms of service editor |
| 26 | `feedback` | 2 | 399 | 0 | 0 | 1 | 2 | Feedback inbox & issue tracking |
| 27 | `leads` | 1 | 269 | 0 | 0 | 1 | 1 | CRM lead generation inquiries |
| 28 | `profile` | 1 | 197 | 1 | 0 | 0 | 2 | Superadmin personal profile & password change |
| 29 | `users` | 1 | 133 | 0 | 0 | 1 | 2 | Superadmin staff accounts list |
| 30 | `smtp` | 1 | 12 | 0 | 0 | 0 | 0 | Email configuration stub |
| **Total** | **30 Submodules** | **87** | **40,409** | **74** | **46** | **35** | **77** | |

### 1.4 State of Language Files & Key Conventions
1. **Existing Translation Dictionaries:**
   - `lang/id/admin.php` exists (895 lines, 878 translation keys).
   - `lang/en/admin.php` exists (895 lines, 878 translation keys).
   - Key parity between `lang/id/admin.php` and `lang/en/admin.php` is **100% (0 mismatched keys)**.
   - **Crucial Finding:** An exact string match scan revealed that **676 keys (77.0%)** in `lang/id/admin.php` already match raw Indonesian strings present in `resources/views/admin`! This proves that key extraction was previously initiated, but replacement into the Blade templates was never completed.
2. **Key Naming Pattern in `admin.php`:**
   - Keys use flat snake_case descriptors:
     - Navigation & Layout: `nav_*`, `mod_*`, `cluster_*` (used by `resources/views/layouts/admin.blade.php`).
     - Actions: `simpan_*`, `hapus_*`, `buka_*`, `tambah_*`, `ubah_*`, `kirim_*`, `unduh_*`.
     - Contextual placeholders: `contoh_*`, `tuliskan_*`.
     - Submodule-specific items: e.g. `cms_paket_harga_admin_console`, `verifikasi_pembayaran`, `filter_moderasi_kata_komunitas`.
   - Reusable actions and status badges can and should leverage `common.php`:
     - e.g. `__('common.save')`, `__('common.cancel')`, `__('common.delete')`, `__('common.edit')`, `__('common.search')`, `__('common.status_active')`, `__('common.status_inactive')`.
3. **Reference Pattern in `layouts/admin.blade.php`:**
   - `resources/views/layouts/admin.blade.php` is already 100% localized using `{{ __('admin.<key>') }}`.

---

## 2. Logic Chain

```
[Observation 1: 87 files, 40,409 lines, 0 i18n calls in admin views]
                                  │
                                  ▼
[Observation 2: 947 deep_audit_v2 issues across 86 files; 1 empty file]
                                  │
                                  ▼
[Observation 3: lang/id/admin.php & lang/en/admin.php contain 878 keys; 77% match admin views]
                                  │
                                  ▼
[Deduction: String extraction was previously drafted, but views were never updated]
                                  │
                                  ▼
[Observation 4: 74 files use Alpine.js, 38 JS confirms, 229 ternaries, 499 raw attributes]
                                  │
                                  ▼
[Deduction: Direct blind replacement will cause JS quote breaks and attribute parsing errors]
                                  │
                                  ▼
[Conclusion: Remediation requires a phased, safe execution pipeline with strict JS escaping]
```

### Step-by-Step Rationale:
1. **Zero Prior Replacement:** Even though `lang/id/admin.php` contains 878 keys, none of the 87 Blade templates in `resources/views/admin` contain `__('admin.')` calls. Remediating `resources/views/admin` requires updating all 86 non-empty templates.
2. **Quote Collision in Alpine.js / Inline JS:**
   - There are 38 inline `confirm('...')` and `alert('...')` calls (e.g. in `social_media/index.blade.php:651`, `whatsapp/index.blade.php:771`).
   - If an agent replaces `confirm('Pesan ini akan...')` with `confirm('{{ __('admin.confirm_msg') }}')`, and the English translation contains an apostrophe (`Don't`, `Can't`, `User's`), the JS interpreter will crash with a SyntaxError.
   - **Remedy:** All JS strings must use `@json(__('admin.key'))` or `addslashes`.
3. **Attribute Replacement Safety:**
   - There are 499 raw attributes (`placeholder="..."`, `title="..."`, `aria-label="..."`).
   - Using standard HTML attribute syntax `placeholder="{{ __('admin.placeholder_key') }}"` avoids Alpine expression evaluation conflicts, which occur when using `:placeholder="'{{ ... }}'"`.
4. **Mega-Files Risk:**
   - Five files account for over 35% of all lines:
     - `inbox/index.blade.php` (3,818 lines)
     - `social_media/index.blade.php` (3,006 lines)
     - `whatsapp/index.blade.php` (2,905 lines)
     - `ai_tokens/index.blade.php` (1,869 lines)
     - `dashboard.blade.php` (1,636 lines)
   - These 5 files contain dense Alpine state logic and nested modals. Modifying them in a single prompt risks context truncation or missed strings. They must be handled in dedicated, focused steps.

---

## 3. Complex UI Elements & Replacement Guidelines

The survey identified the following critical UI complexities across the 87 templates:

| Complex UI Element | Total Count | Risk Description | Safe Replacement Syntax |
|---|---|---|---|
| **Alpine.js `x-data` State** | 56 declarations across 74 files | Hardcoded strings inside JS objects (e.g., `modalTitle: 'Detail Tenant'`) | Declare translatable data via `@json(__('...'))` or bind dynamically |
| **JS `confirm()`, `alert()`, `prompt()`** | 38 occurrences | Unescaped single quotes in English strings (e.g., `don't`, `it's`) will break JS | Use `confirm(@json(__('admin.key')))` |
| **Blade Ternary Expressions** | 229 occurrences | `{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}` | `{{ $item->is_active ? __('common.status_active') : __('common.status_inactive') }}` |
| **Raw Attributes (`placeholder`, `title`, etc.)** | 499 occurrences | Double quotes inside attributes | `placeholder="{{ __('admin.key') }}"` or `:placeholder="@json(__('admin.key'))"` |
| **Modals & Dialogs** | 111 triggers/backdrops across 46 files | Multi-layered Alpine toggles (`x-show="createOpen"`, `@click.away`) | Keep all Alpine directives untouched; replace inner text nodes only |
| **Dynamic Interpolations** | ~180 occurrences | Strings with dynamic Blade variables like `Menampilkan {{ $count }} data` | Use parameter binding: `{{ __('admin.showing_count', ['count' => $count]) }}` |
| **Data Tables** | 35 files with `<table>` | Complex `<thead>` sorting links, tooltips, empty state spans | Replace `<th>` labels and empty state `<td>` spans with `{{ __('admin.key') }}` |

---

## 4. Proposed Logical Grouping & Remediation Milestones

To ensure maximum safety, prevent context overflow, and guarantee that each milestone can be independently verified with `python scratch/deep_audit_v2.py`, the 87 files are decomposed into **6 logical milestones**:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                      ADMIN VIEWS REMEDIATION ROADMAP (87 FILES)                        │
├───────────┬───────────────────────────────────────┬──────────┬───────────┬─────────────┤
│ Milestone │ Scope / Modules                       │ Files    │ Lines     │ Complexity  │
├───────────┼───────────────────────────────────────┼──────────┼───────────┼─────────────┤
│ Phase 1   │ Core Admin, Auth & Main Dashboard     │ 8 files  │ 3,204 l   │ Medium      │
│ Phase 2   │ Tenant Operations & Subscriptions     │ 10 files │ 6,583 l   │ Medium-High │
│ Phase 3   │ Finance, Banking & B2B Sales          │ 34 files │ 7,360 l   │ High        │
│ Phase 4   │ Marketing, Content, Community & CMS   │ 16 files │ 5,189 l   │ Medium      │
│ Phase 5   │ Communication Mega-Files (Channels)   │ 4 files  │ 10,111 l  │ Critical    │
│ Phase 6   │ System Configuration & AI Services    │ 15 files │ 7,962 l   │ High        │
├───────────┼───────────────────────────────────────┼──────────┼───────────┼─────────────┤
│ **TOTAL** │ **All 30 Submodules**                 │ **87**   │ **40,409**│             │
└───────────┴───────────────────────────────────────┴──────────┴───────────┴─────────────┘
```

### Detailed Milestone Breakdown:

#### Phase 1: Core Admin, Auth & Main Dashboard (8 files, 3,204 lines)
- `admin/dashboard.blade.php` (1,636 lines) - Superadmin primary KPI & webhook console
- `admin/roles/index.blade.php` (726 lines) - RBAC roles & permissions
- `admin/auth/login.blade.php` (177 lines)
- `admin/auth/reset-password.blade.php` (184 lines)
- `admin/auth/forgot-password.blade.php` (139 lines)
- `admin/profile/index.blade.php` (197 lines) - Admin profile settings
- `admin/users/index.blade.php` (133 lines) - Superadmin staff
- `admin/smtp/index.blade.php` (12 lines) - Stub

#### Phase 2: Tenant Operations & Subscriptions (10 files, 6,583 lines)
- `admin/pricing-programs/index.blade.php` (1,421 lines) & `simulate.blade.php` (149 lines)
- `admin/account-recoveries/index.blade.php` (888 lines) & `show.blade.php` (616 lines)
- `admin/subscriptions/index.blade.php` (729 lines) & `show.blade.php` (312 lines)
- `admin/billing-packages/index.blade.php` (1,039 lines)
- `admin/businesses/index.blade.php` (429 lines) & `show.blade.php` (340 lines)
- `admin/promos/index.blade.php` (660 lines)

#### Phase 3: Finance, Banking & B2B Sales (34 files, 7,360 lines)
- **`finance` (18 files, 3,356 lines):**
  - `index.blade.php` (634 lines)
  - 9 Modals (`modal-coa`, `modal-create-ap`, `modal-create-ar`, `modal-create-journal`, `modal-create-transaction`, `modal-lightbox`, `modal-pay-ap`, `modal-pay-ar`)
  - 8 Tabs (`tab-ap`, `tab-ar`, `tab-balance-sheet`, `tab-cashflow`, `tab-coa`, `tab-journals`, `tab-ledger`, `tab-sync`, `tab-trial-balance`)
- **`b2b` (11 files, 2,237 lines):**
  - `index.blade.php` (328 lines)
  - 6 Modals (`modal-create-invoice`, `modal-create-order`, `modal-create-quotation`, `modal-create-return`, `modal-edit-document`, `modal-record-payment`)
  - 4 Tabs (`tab-invoices`, `tab-orders`, `tab-quotations`, `tab-returns`)
- **`payment_accounts` (3 files, 911 lines):** `index.blade.php` (382 lines), `edit.blade.php` (287 lines), `create.blade.php` (242 lines)
- **`settlements` (2 files, 856 lines):** `index.blade.php` (572 lines), `show.blade.php` (284 lines)

#### Phase 4: Marketing, Content, Community & CMS (16 files, 5,189 lines)
- `admin/posts/index.blade.php` (811 lines), `create.blade.php` (410 lines), `edit.blade.php` (405 lines)
- `admin/events/index.blade.php` (296 lines), `create.blade.php` (263 lines), `edit.blade.php` (263 lines), `show.blade.php` (315 lines)
- `admin/templates/index.blade.php` (413 lines), `create.blade.php` (186 lines), `edit.blade.php` (196 lines)
- `admin/word_filters/index.blade.php` (540 lines)
- `admin/legal-pages/index.blade.php` (176 lines), `edit.blade.php` (247 lines)
- `admin/feedback/index.blade.php` (169 lines), `show.blade.php` (230 lines)
- `admin/leads/index.blade.php` (269 lines)

#### Phase 5: Communication Mega-Files (4 files, 10,111 lines)
- `admin/inbox/index.blade.php` (3,818 lines) - Mega-file: Live chat, AI agent, Webhooks
- `admin/whatsapp/index.blade.php` (2,905 lines) - Mega-file: Meta Cloud API, blast campaigns, templates
- `admin/whatsapp/blast_show.blade.php` (382 lines)
- `admin/social_media/index.blade.php` (3,006 lines) - Mega-file: Social media scheduler & OAuth channels

#### Phase 6: System Configuration & AI Services (15 files, 7,962 lines)
- **`settings` (11 files, 4,793 lines):**
  - `index.blade.php` (510 lines)
  - 10 Tabs: `tab-social` (943 lines), `tab-whatsapp` (612 lines), `tab-social-links` (555 lines), `tab-seo` (513 lines), `tab-branding` (365 lines), `tab-shipping` (267 lines), `tab-system` (266 lines), `tab-payment` (265 lines), `tab-smtp` (256 lines), `tab-marketplace` (241 lines)
- `admin/ai_tokens/index.blade.php` (1,869 lines) - API token pool & quota monitoring
- `admin/ai-studio/sources.blade.php` (483 lines) & `pricing.blade.php` (338 lines)
- `admin/error-logs/index.blade.php` (479 lines)

---

## 5. Caveats

1. **Non-destructive Invariance:** During this survey, absolutely NO view files, models, controllers, or translation dictionaries were altered.
2. **Translation Key Strategy:**
   - Existing keys in `lang/id/admin.php` and `lang/en/admin.php` already provide coverage for 676 common admin phrases.
   - However, because the total file count is 87 files with 40,409 lines, completing full localization will require adding an estimated ~800 to 1,000 additional keys to both `lang/id/admin.php` and `lang/en/admin.php`.
   - Reusable actions (Simpan, Batal, Ubah, Hapus, Cari, Status) should reuse `common.php` to avoid bloating `admin.php` with duplicate keys.
3. **Mega-Files Context Limits:** Modifying `inbox/index.blade.php` (3,818 lines), `whatsapp/index.blade.php` (2,905 lines), and `social_media/index.blade.php` (3,006 lines) will require chunked replacements or targeted tool calls to prevent tool payload limits.

---

## 6. Conclusion

- `resources/views/admin` is currently **0% localized** in view source code, with **86 files containing 947 hardcoded string issues** detected by `scratch/deep_audit_v2.py`.
- Fortunately, dictionary preparation is already 77% aligned: `lang/id/admin.php` and `lang/en/admin.php` already hold 878 keys with 100% ID-EN parity, ready to be leveraged during remediation.
- The 6-phase decomposition outlined in Section 4 provides a logically sound, risk-isolated execution path that balances file sizes, isolates mega-files, and prevents Alpine.js / JS quote breakages.

---

## 7. Verification Method

To independently reproduce and verify this survey:

1. **Run Inventory Scan:**
   ```powershell
   python .agents/teamwork/explorer_survey_2/survey_admin.py
   ```
   *Expected result:* 87 files, 40,409 lines, 30 submodules, 0 files with i18n calls.

2. **Run Deep Audit Script:**
   ```powershell
   python scratch/deep_audit_v2.py resources/views/admin
   ```
   *Expected result:* 86 files with issues, 1 clean file (`smtp/index.blade.php`), 947 total issues.

3. **Verify Language File Parity:**
   ```powershell
   python .agents/teamwork/explorer_survey_2/analyze_lang_admin.py
   ```
   *Expected result:* 878 keys in `lang/id/admin.php`, 878 keys in `lang/en/admin.php`, 0 key discrepancies.

4. **Verify Key Match Ratio:**
   ```powershell
   python .agents/teamwork/explorer_survey_2/match_keys.py
   ```
   *Expected result:* 676 keys (77.0%) match strings in `resources/views/admin`.

5. **Verify Complex UI Elements:**
   ```powershell
   python .agents/teamwork/explorer_survey_2/inspect_complex_ui.py
   ```
   *Expected result:* 56 `x-data` blocks, 38 JS confirms, 229 Blade ternaries, 499 raw translatable attributes, 111 modal triggers.
