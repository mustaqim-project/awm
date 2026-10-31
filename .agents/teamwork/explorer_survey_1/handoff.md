# Baseline Survey and Deep Audit Script Analysis Report

**Date**: 2026-10-31  
**Author**: Explorer 1 (`explorer_survey_1`)  
**Mission**: Survey & Deep Audit Script Analysis for Multi-Language Localization of `resources/views/app` and `resources/views/admin`

---

## 1. Observation

### 1.1 Deep Audit Tool Analysis (`scratch/deep_audit_v2.py`)
Direct source inspection of `scratch/deep_audit_v2.py` (283 lines) reveals the full detection mechanics and structural architecture:

1. **Indonesian Heuristic & Keyword Set (`ID_KEYWORDS`, lines 10–31)**:
   - Defined as a set of 113 high-frequency Indonesian stems covering business, workflow, actions, navigation, status, time, and UI verbs.
   - Normalization function `is_indonesian_text(text)` (lines 33–42):
     - Strips all non-alphabetic characters using `re.sub(r'[^a-zA-Z\s]', ' ', text.lower()).strip()`.
     - Splits into lowercase tokens.
     - Evaluates match ratio:
       - If token count is $\le 3$: triggered if $\ge 1$ word is in `ID_KEYWORDS`.
       - If token count is $> 3$: triggered if matches $\ge 1$ and match ratio $\ge 15\%$ (`len(matches) / len(words) >= 0.15`).

2. **Checked Patterns, Tags, Attributes, and Domains**:
   - **Domain 1 (Page Title & Meta Tags)**:
     - `@extends(..., ['title' => '...'])` (lines 55–67): extracts the string title value; flags if unlocalized and either Indonesian text or contains keywords `cooca, halaman, admin, beranda, login, daftar`.
     - `@section('title|description|og_title|og_description', '...')` (lines 70–83): extracts raw literal argument; flags if unlocalized and Indonesian.
     - Error View PHP Assignments (lines 86–99): checks assignments to `$title`, `$message`, `$homeLabel` (e.g. `$title = 'Halaman Tidak Ditemukan';`).
   - **Domain 4 & Domain 7 (Form & Action Attributes, lines 101–124)**:
     - Regex: `(?<!:)\b(placeholder|aria-label|title|alt)=(['"])(.*?)\2`
     - Evaluates attribute value for Indonesian content. Maps `placeholder` to Domain 4 (`Forms, Labels, & Hints`) and `aria-label`, `title`, `alt` to Domain 7 (`Buttons & Action Links`).
   - **Domain 5 (Dev / Test Alerts, lines 126–138)**:
     - Regex: `(Bypass|Pengujian|Sandbox|Testing)[^<>\n]*:`
   - **Domain 6 (Error Lists & Summary, lines 140–152)**:
     - Regex: `(Verifikasi Belum Berhasil|Terjadi kesalahan|Periksa kembali input)[^<>\n]*`
   - **Domain 9 (Tables, Badges, & Empty States, lines 154–166)**:
     - Regex: `(Belum ada [^<>\n]+|Tidak ada data[^<>\n]*|Data tidak ditemukan[^<>\n]*)`
   - **Domain 7 (Raw Button & Link Labels, lines 171–191)**:
     - Regex: `<(?:button|a)\b(?:"[^"]*"|\'[^\']*\'|\{\{.*?\}\}|\{!!.*?!!\}|[^>"\'])*>(.*?)</(?:button|a)>`
     - Strips inner tags via `TAG_PATTERN` (handles nested quotes and braces), strips `{{ ... }}`, `{!! ... !!}`, and Blade directives `@[a-zA-Z_]+(...)`.
     - Restricts detection to inner text length $\le 8$ words.
   - **Domain 3 (Headings, lines 193–213)**:
     - Regex: `<(h[1-6])\b...>(.*?)</h[1-6]>`
     - Strips nested tags, expressions, and directives; flags if inner text satisfies `is_indonesian_text`.
   - **Domain 8 (Modals & Quota Microcopy, lines 215–225)**:
     - Regex: `quotaTitle:\s*'([^']+)'|quotaDesc:\s*'([^']+)'`

3. **Ignored Elements, False-Positive Protections, and Edge Cases**:
   - **Alpine/Vue Dynamic Bindings**: Lookbehind `(?<!:)` strictly ignores `:placeholder`, `:title`, `:aria-label`, `:alt`.
   - **Blade Variables**: Explicitly skips attribute values starting with `{{` and ending with `}}` (e.g. `title="{{ $product->name }}"`), values starting with `$`, `route(`, or empty.
   - **Existing Localization Calls**: Skips attributes and strings containing `{{ __('...` or `@lang(...)` or `__('...`.
   - **Composite Block Links**: Button regex enforces $\le 8$ words so large link cards enclosing paragraphs and layout grids are not false-flagged as raw buttons.
   - **Deduplication**: Lines 228–235 deduplicate detected issues per file using a `seen` set on `(line, domain, text)`.

4. **Line and Count Reporting**:
   - Line numbers are calculated precisely from character offset: `content[:m.start()].count('\n') + 1`.
   - CLI invocation accepts `--out <path.json>` or outputs JSON to stdout.
   - JSON structure per directory:
     ```json
     {
       "<dir>": {
         "total_files": 185,
         "clean_files": 107,
         "files_with_issues": 78,
         "total_i18n_calls": 12267,
         "total_issues": 762,
         "files": [ ... ]
       }
     }
     ```

---

### 1.2 Baseline Execution Results

Commands executed:
- `python scratch/deep_audit_v2.py resources/views/app --out scratch/audit_app_baseline.json`
- `python scratch/deep_audit_v2.py resources/views/admin --out scratch/audit_admin_baseline.json`

#### Baseline Summary Table

| Metric | `resources/views/app` | `resources/views/admin` | Combined Total |
| :--- | :--- | :--- | :--- |
| **Total Blade Files** | 185 | 87 | **272** |
| **Clean Files (0 issues)** | 107 (57.8%) | 1 (1.1%) | **108 (39.7%)** |
| **Files With Issues** | 78 (42.2%) | 86 (98.9%) | **164 (60.3%)** |
| **Existing i18n Calls** | 12,267 | 0 | **12,267** |
| **Total Issues Detected** | **762** | **956** | **1,718** |

*Note: The only clean file in `resources/views/admin` is `resources/views/admin/smtp/index.blade.php`.*

---

### 1.3 Defect Breakdown by Domain and Issue Type

#### A. resources/views/app (762 issues across 78 files)

| Domain | Count | Top Issue Types in Domain |
| :--- | :--- | :--- |
| **7. Buttons & Action Links** | 476 | `raw_button_label` (308), `raw_attr_title` (119), `raw_attr_aria-label` (38), `raw_attr_alt` (11) |
| **3. Headers, H1, & Subtitles** | 107 | `raw_heading_h3` (46), `raw_heading_h2` (28), `raw_heading_h4` (18), `raw_heading_h1` (12), `raw_heading_h5` (3) |
| **4. Forms, Labels, & Hints** | 81 | `raw_attr_placeholder` (81) |
| **9. Tables, Badges, & Empty** | 53 | `empty_state_hardcoded` (53) |
| **6. Error Lists & Summary** | 24 | `validation_errors_raw` (24) |
| **1. Page Title & Meta Tags** | 17 | `extends_title_raw` (17) |
| **5. Dev / Test Alerts** | 4 | `dev_bypass_hardcoded` (4) |

#### B. resources/views/admin (956 issues across 86 files)

| Domain | Count | Top Issue Types in Domain |
| :--- | :--- | :--- |
| **7. Buttons & Action Links** | 553 | `raw_button_label` (414), `raw_attr_title` (119), `raw_attr_alt` (13), `raw_attr_aria-label` (7) |
| **3. Headers, H1, & Subtitles** | 130 | `raw_heading_h3` (75), `raw_heading_h4` (22), `raw_heading_h2` (20), `raw_heading_h1` (13) |
| **4. Forms, Labels, & Hints** | 120 | `raw_attr_placeholder` (120) |
| **9. Tables, Badges, & Empty** | 69 | `empty_state_hardcoded` (69) |
| **1. Page Title & Meta Tags** | 40 | `extends_title_raw` (36), `section_title_raw` (4) |
| **5. Dev / Test Alerts** | 27 | `dev_bypass_hardcoded` (27) |
| **6. Error Lists & Summary** | 17 | `validation_errors_raw` (17) |

---

### 1.4 Detailed Subdirectory Distribution

#### Subdirectory Breakdown: `resources/views/app` (Total 185 files)

| Subdirectory | Total Files | Files With Issues | Total Issues |
| :--- | :--- | :--- | :--- |
| `landing_page` | 2 | 2 | 96 |
| `docs` | 2 | 2 | 72 |
| `ai-studio` | 8 | 8 | 69 |
| `import` | 1 | 1 | 62 |
| `community` | 3 | 3 | 48 |
| `social_media` | 5 | 4 | 45 |
| `marketplace` | 6 | 5 | 35 |
| `inventory` | 4 | 3 | 33 |
| `labor-machines` | 1 | 1 | 32 |
| `pos` | 29 | 6 | 30 |
| `master-data` | 1 | 1 | 28 |
| `quotations` | 4 | 4 | 25 |
| `sales` | 5 | 4 | 25 |
| `sales-orders` | 4 | 4 | 20 |
| `settings` | 5 | 4 | 19 |
| `profile` | 2 | 2 | 18 |
| `ai` | 18 | 1 | 17 |
| `products` | 3 | 2 | 14 |
| `customers` | 1 | 1 | 11 |
| `suppliers` | 1 | 1 | 11 |
| `reports` | 1 | 1 | 10 |
| `finance` | 16 | 3 | 9 |
| `services` | 1 | 1 | 5 |
| `storefront` | 12 | 2 | 4 |
| `tax` | 1 | 1 | 4 |
| `purchase-orders` | 4 | 2 | 3 |
| `purchasing` | 6 | 1 | 3 |
| `billing` | 5 | 1 | 2 |
| `crm` | 2 | 1 | 2 |
| `mcp` | 1 | 1 | 2 |
| `profitability` | 1 | 1 | 2 |
| `warehouse` | 2 | 1 | 2 |
| `whatsapp` | 4 | 1 | 2 |
| `invoices` | 5 | 1 | 1 |
| `portal` | 1 | 1 | 1 |
| *(root)* | 2 | 0 | 0 |
| `analytics` | 1 | 0 | 0 |
| `approvals` | 3 | 0 | 0 |
| `feedback` | 3 | 0 | 0 |
| `hrm` | 4 | 0 | 0 |
| `materials` | 1 | 0 | 0 |
| `roles` | 1 | 0 | 0 |
| `security` | 2 | 0 | 0 |
| `simulator` | 1 | 0 | 0 |

#### Subdirectory Breakdown: `resources/views/admin` (Total 87 files)

| Subdirectory | Total Files | Files With Issues | Total Issues |
| :--- | :--- | :--- | :--- |
| `finance` | 18 | 18 | 84 |
| `settings` | 11 | 11 | 79 |
| `inbox` | 1 | 1 | 78 |
| `whatsapp` | 2 | 2 | 59 |
| `posts` | 3 | 3 | 55 |
| `b2b` | 11 | 11 | 54 |
| *(root)* (`dashboard.blade.php`) | 1 | 1 | 47 |
| `social_media` | 1 | 1 | 47 |
| `account-recoveries` | 2 | 2 | 42 |
| `ai_tokens` | 1 | 1 | 42 |
| `pricing-programs` | 2 | 2 | 40 |
| `subscriptions` | 2 | 2 | 34 |
| `events` | 4 | 4 | 29 |
| `settlements` | 2 | 2 | 29 |
| `payment_accounts` | 3 | 3 | 28 |
| `businesses` | 2 | 2 | 27 |
| `templates` | 3 | 3 | 26 |
| `billing-packages` | 1 | 1 | 18 |
| `promos` | 1 | 1 | 18 |
| `ai-studio` | 2 | 2 | 16 |
| `feedback` | 2 | 2 | 15 |
| `legal-pages` | 2 | 2 | 15 |
| `word_filters` | 1 | 1 | 15 |
| `roles` | 1 | 1 | 14 |
| `auth` | 3 | 3 | 12 |
| `error-logs` | 1 | 1 | 12 |
| `leads` | 1 | 1 | 8 |
| `profile` | 1 | 1 | 7 |
| `users` | 1 | 1 | 6 |
| `smtp` | 1 | 0 | 0 |

---

### 1.5 Syntax Check Mechanics and Verification Findings

1. **Compilation Engine**:
   - `php artisan view:cache` compiles all 1,180 Blade templates in the project into PHP files located in `storage/framework/views/`.
   - `php artisan view:clear` cleans up compiled views safely.
2. **Instant In-Memory Syntax Validation**:
   - Created and verified `scratch/fast_syntax_check.php` utilizing:
     ```php
     $blade = app('blade.compiler');
     $compiled = $blade->compileString(file_get_contents($path));
     token_get_all($compiled, TOKEN_PARSE);
     ```
   - Running `php scratch/fast_syntax_check.php` parses and verifies syntax for all 272 Blade files in `resources/views/app` and `resources/views/admin` in **2.1 seconds** with **0 syntax errors**.
3. **Critical Trap Discovered for Implementers (Nested Blade Directive Anti-Pattern)**:
   - When replacing strings inside existing Blade directives (e.g. `{{ $helper('key', 'default') }}` or `@extends('layouts.app', ['title' => '...'])`), **NEVER** write nested curly braces:
     - ❌ **BROKEN**: `{{ $helper('key', '{{ __('storefront.nav_home') }}') }}` (compiles to nested `<?php echo e(...<?php echo e(...); ?>...); ?>` and throws fatal `ParseError: unexpected identifier`).
     - ✅ **CORRECT**: `{{ $helper('key', __('storefront.nav_home')) }}` or `@extends('layouts.app', ['title' => __('admin.title')])`.

---

## 2. Logic Chain

1. **Premise 1**: The user requirement R1 dictates zero hardcoded Indonesian strings across `resources/views/app` (185 files) and `resources/views/admin` (87 files), verified by `scratch/deep_audit_v2.py`.
2. **Premise 2**: Direct audit script analysis proves `deep_audit_v2.py` checks 8 specific domains with strict regexes on page titles, attributes (`placeholder`, `title`, `aria-label`, `alt`), dev bypasses, validation summaries, empty states, buttons/links ($\le 8$ words), headings (`h1`–`h6`), and modal quota keys.
3. **Premise 3**: Running `deep_audit_v2.py` on the fresh codebase established the exact baseline:
   - `resources/views/app`: 762 defects across 78 files (107 files are already clean and contain 12,267 existing i18n calls).
   - `resources/views/admin`: 956 defects across 86 files (0 existing i18n calls; 1 file clean).
   - Total to resolve: 1,718 defects across 164 files.
4. **Premise 4**: Defect categorization reveals that `raw_button_label` (722), `raw_attr_title` (238), `raw_attr_placeholder` (201), `raw_heading_h3` (121), and `empty_state_hardcoded` (122) constitute over 81% of all defects.
5. **Premise 5**: Verification through `fast_syntax_check.php` proved that all 272 current view files pass PHP tokenization without parse errors.
6. **Conclusion**: Remediating these 1,718 issues across 164 files will systematically satisfy the Acceptance Criteria if conducted with strict adherence to PHP-in-Blade syntax rules and bidirectional key registration in `lang/id` and `lang/en`.

---

## 3. Caveats

1. **Unflagged Text Outside Audit Regex Scope**:
   `deep_audit_v2.py` specifically targets high-signal elements (buttons, headings, attributes, titles, alerts, empty states, validation messages). Raw text inside arbitrary `<p>`, `<span>`, or `<td>` that does not match empty state or alert patterns is not flagged by `deep_audit_v2.py`. However, to achieve true 100% Zero Hardcoded Text compliance under `multi-language-and-i18n`, any surrounding user-facing text noticed during remediation should also be localized.
2. **Translation Key Namespace Parity**:
   `lang/id/` and `lang/en/` already contain 66 translation domain files (e.g., `admin.php`, `pos.php`, `sales.php`, `finance.php`, `billing.php`). New keys extracted from `resources/views/admin` should predominantly be placed into `lang/id/admin.php` and `lang/en/admin.php` (or module-specific domain files where applicable) to prevent fragmenting key storage.
3. **Cache Clearing**:
   Any modification to views requires running `php artisan view:clear` to prevent stale compiled files from masking or causing errors.

---

## 4. Conclusion

1. **Exact Baseline Established**:
   - `resources/views/app`: 185 total files | 107 clean | 78 with issues | 762 total issues.
   - `resources/views/admin`: 87 total files | 1 clean | 86 with issues | 956 total issues.
   - Combined: 272 total files | 164 files needing remediation | **1,718 total issues**.
2. **Dominant Defect Categories**:
   - `raw_button_label`: 722 issues (308 in app, 414 in admin)
   - `raw_attr_title`: 238 issues (119 in app, 119 in admin)
   - `raw_attr_placeholder`: 201 issues (81 in app, 120 in admin)
   - `empty_state_hardcoded`: 122 issues (53 in app, 69 in admin)
   - `raw_heading_h3`: 121 issues (46 in app, 75 in admin)
3. **Execution Readiness**:
   - The validation harness (`scratch/deep_audit_v2.py`) and fast syntax checker (`scratch/fast_syntax_check.php`) are fully operational, tested, and validated.
   - A clean path forward for orchestrator batching is established:
     - Batch A: `resources/views/app` (78 files, 762 issues).
     - Batch B: `resources/views/admin` (86 files, 956 issues).

---

## 5. Verification Method

To independently verify these findings, run the following commands from `c:\laragon\www\cooca_core`:

1. **Verify Baseline Numbers**:
   ```powershell
   python scratch/deep_audit_v2.py resources/views/app --out scratch/test_app.json
   python scratch/deep_audit_v2.py resources/views/admin --out scratch/test_admin.json
   python scratch/survey_analysis.py
   ```
   *Expected output*: Exactly 762 issues in `resources/views/app` and 956 issues in `resources/views/admin`.

2. **Verify Fast Syntax Check**:
   ```powershell
   php scratch/fast_syntax_check.php
   ```
   *Expected output*: `Total checked: 272`, `Errors count: 0`.

3. **Verify Full Laravel Blade Compilation**:
   ```powershell
   php artisan view:clear
   php artisan view:cache
   ```
   *Expected output*: `INFO Blade templates cached successfully.`
