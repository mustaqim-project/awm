# Test Infrastructure Architecture & Quality Gate Specification

## 1. Overview & Strategy
This document establishes the architecture, test runners, execution strategy, and acceptance gates for the Cooca Multi-Language Localization Remediation project.

The verification system enforces **Zero Hardcoded Text**, **100% Bilingual Parity**, **Zero Blade Syntax Regressions**, and **Flawless View Compilation** across the entire application.

### Key Quality Pillars:
1. **Deterministic Heuristic Audit**: Automated pattern scanning across 8 distinct UI domains using the official audit engine (`scratch/deep_audit_v2.py`).
2. **Instant In-Memory Syntax Validation**: Tokenization-based Blade syntax checking (`scratch/fast_syntax_check.php`) validating all 272 view files in under 3 seconds.
3. **Strict Bidirectional Translation Parity**: Mathematical key symmetry across 66 domain language files in `lang/id/*.php` and `lang/en/*.php`.
4. **Full Production Cache Compilation**: End-to-end framework verification via `php artisan view:clear && php artisan view:cache` ensuring zero runtime rendering failures across 1,180 Blade templates.

---

## 2. Directory & Component Layout

```
cooca_core/
├── lang/
│   ├── id/*.php                  # 66 Indonesian modular translation files
│   └── en/*.php                  # 66 English modular translation files
├── resources/views/
│   ├── app/                      # 185 App Blade templates (134,824 lines of code)
│   └── admin/                    # 87 Admin Blade templates (40,409 lines of code)
├── scratch/
│   ├── verify_localization_suite.py  # Master E2E test runner harness
│   ├── deep_audit_v2.py              # 8-domain regex audit scanner
│   ├── fast_syntax_check.php         # Tokenization Blade syntax checker
│   └── e2e_baseline_test_report.json # Automated test execution report
├── TEST_INFRA.md                 # Test architecture & runner specification (this document)
└── TEST_READY.md                 # Milestone readiness publication & quality criteria
```

---

## 3. Tier Breakdown & Acceptance Thresholds

### Tier 1: Deep Audit of `resources/views/app`
- **Scope**: 185 Blade templates across 44 submodules in `resources/views/app`.
- **Command**: `python scratch/deep_audit_v2.py resources/views/app`
- **Target Threshold**: **0 issues** (Pass requires 100% of files clean).
- **Domains Audited**:
  - `Domain 1`: Page titles (`@extends`, `@section('title')`, `$title = '...'`)
  - `Domain 3`: Headings (`<h1>` through `<h6>`)
  - `Domain 4`: Form attributes (`placeholder="..."`)
  - `Domain 5`: Developer bypass / test notices (`Bypass:`, `Pengujian:`)
  - `Domain 6`: Validation error summaries (`Terjadi kesalahan`, `Periksa kembali`)
  - `Domain 7`: Buttons and action links (`<button>`, `<a>`, `aria-label`, `title`, `alt`)
  - `Domain 8`: Modal quota titles and descriptions
  - `Domain 9`: Empty states, tables, badges (`Belum ada data`, `Data tidak ditemukan`)

### Tier 2: Deep Audit of `resources/views/admin`
- **Scope**: 87 Blade templates across 30 submodules in `resources/views/admin`.
- **Command**: `python scratch/deep_audit_v2.py resources/views/admin`
- **Target Threshold**: **0 issues** (Pass requires 100% of files clean).
- **Domains Audited**: Identical 8 UI domains as Tier 1.

### Tier 3: Blade Syntax Validation (272 View Files)
- **Scope**: All 272 view files in `resources/views/app` and `resources/views/admin`.
- **Command**: `php scratch/fast_syntax_check.php`
- **Mechanism**:
  - Compiles Blade source into raw PHP via Laravel's `BladeCompiler::compileString()`.
  - Tokenizes compiled PHP using `token_get_all($compiled, TOKEN_PARSE)` to catch parse errors, broken expressions, or nested Blade directives (e.g. `{{ $fn('{{ ... }}') }}`).
- **Target Threshold**: `Total checked: 272`, `Errors count: 0`.

### Tier 4: Language File Parity & Syntax Verification
- **Scope**: 132 files (66 files in `lang/id/*.php` and 66 files in `lang/en/*.php`).
- **Target Thresholds**:
  - **Syntax**: 132/132 files pass `php -l` and `token_get_all(..., TOKEN_PARSE)` with **0 syntax errors**.
  - **File Symmetry**: 66 ID files = 66 EN files (0 missing files in either direction).
  - **Key Parity**: **100.00% parity** across all keys in all 66 file pairs:
    - Missing keys in EN: **0**
    - Missing keys in ID: **0**
  - **Array Integrity**: 100% of files return valid PHP arrays.
  - **Key Hygiene**: Detect duplicate key declarations within the same scope.

### Tier 5: Full Laravel View Cache Verification
- **Scope**: All 1,180 Blade templates across the entire Laravel application.
- **Commands**:
  ```powershell
  php artisan view:clear
  php artisan view:cache
  ```
- **Target Threshold**: Exit code 0, output `Blade templates cached successfully`.

---

## 4. Test Runner CLI Specification

The automated test runner is located at `scratch/verify_localization_suite.py`. It is a self-contained, high-performance Python script that orchestrates and validates all 5 tiers.

### CLI Syntax
```powershell
python scratch/verify_localization_suite.py [OPTIONS]
```

### Options & Flags
| Flag | Short | Description |
| :--- | :--- | :--- |
| `--full` | | Execute all 5 verification tiers in sequence (default). |
| `--tier <tiers>` | `-t` | Run specific tier(s), e.g. `--tier 1`, `--tier 1,2`, `--tier 3,4,5`, or `--tier all`. |
| `--verbose` | `-v` | Display complete itemized error listings, dirty filenames, and duplicate key lines. |
| `--json <path>` | | Export machine-readable structured test results to a JSON file. |
| `--quiet` | `-q` | Suppress detailed output, showing only the final summary table and exit code. |
| `--help` | `-h` | Display usage instructions and available options. |

### Exit Code Protocol
- **`0`**: ALL requested tiers passed with zero defects.
- **`1`**: One or more tiers failed (defects detected).
- **`2`**: CLI argument or configuration error.

---

## 5. Developer & Implementer Quick Reference

### Recommended Development Workflow
1. **Before Editing**:
   Run Tier 3 & 4 to establish your clean baseline:
   ```powershell
   python scratch/verify_localization_suite.py --tier 3,4
   ```
2. **While Remediating Views**:
   Check the specific view domain you are modifying:
   ```powershell
   # For app views:
   python scratch/verify_localization_suite.py --tier 1
   # For admin views:
   python scratch/verify_localization_suite.py --tier 2
   # Syntax check on modified templates:
   python scratch/verify_localization_suite.py --tier 3
   ```
3. **After Adding Translation Keys**:
   Verify bilingual symmetry and PHP syntax:
   ```powershell
   python scratch/verify_localization_suite.py --tier 4
   ```
4. **Pre-Commit / Pre-Handoff Quality Gate**:
   Run the full verification suite:
   ```powershell
   python scratch/verify_localization_suite.py --full
   ```

---

## 6. Baseline Verification Audit (Current Workspace State)

| Tier | Component | Tested Target | Baseline Result | Expected Gate | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Tier 1** | App Views Audit | `resources/views/app` (185 files) | 78 defects (26 files) | 0 defects | **FAIL** (Baseline detected) |
| **Tier 2** | Admin Views Audit | `resources/views/admin` (87 files) | 218 defects (27 files) | 0 defects | **FAIL** (Baseline detected) |
| **Tier 3** | Blade Syntax | 272 Blade templates | 0 errors | 0 errors | **PASS** |
| **Tier 4** | Lang Parity & Syntax | 132 lang files (17,149 keys) | 100.00% parity, 0 syntax err | 100% parity | **PASS** |
| **Tier 5** | Laravel View Cache | 1,180 templates | Cached successfully | Exit 0 | **PASS** |

*Overall Baseline Status*: **FAIL (Exit Code 1)** — Accurately gates on 296 remaining unlocalized strings across app and admin view files.
