Audit and remediate all remaining Blade view files in `resources/views/app` and `resources/views/admin` under `c:\laragon\www\cooca_core\resources` to enforce full Indonesian and English multi-language localization, eliminating hardcoded strings across all lines of code.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. View Template Localization Audit and Remediation
Audit all lines of code across all files in `resources/views/app` (185 files) and `resources/views/admin` (87 files). Replace all user-facing hardcoded text with proper localization calls so both Indonesian and English are supported.

### R2. Translation Key and File Management
Register all newly extracted translation keys and values into the project's translation files following existing project conventions, including modular PHP files in `lang/id/*.php` and `lang/en/*.php` and JSON files where applicable. Ensure complete bilingual parity (Indonesian and English) for all added keys.

### R3. Syntax and Functional Preservation
Preserve all existing Blade syntax, Alpine/JS code, HTML markup, and backend logic without introducing syntax errors or breaking existing template rendering.

## Acceptance Criteria

### Audit Verification
- [ ] Running `python scratch/deep_audit_v2.py resources/views/app` reports 0 hardcoded text issues.
- [ ] Running `python scratch/deep_audit_v2.py resources/views/admin` reports 0 hardcoded text issues.
- [ ] All modified Blade templates pass PHP/Blade syntax checks without errors.
- [ ] All referenced translation keys in modified views resolve in both Indonesian and English language files without missing translations.
