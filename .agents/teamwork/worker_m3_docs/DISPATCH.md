# DISPATCH  2026-11-08T19:17:07Z

## 2026-11-08T19:17:07Z
You are a technical documentation implementation subagent (teamwork_worker).

MANDATORY FIRST STEP:
Read the authoritative request file at:
`c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`

Read the project plan and documentation survey report at:
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_audit_docs\handoff.md`
- Relevant skill: `C:\Users\Ulfah Ramadhani\.gemini\config\skills\business-owner-doc-generator\SKILL.md`

Your working directory is:
`c:\laragon\www\cooca_core\.agents\teamwork\worker_m3_docs\`

Update your `progress.md` in your working directory as you complete each step.

EXCLUSIVE FILE OWNERSHIP (You may modify ONLY these files):
- `resources/views/app/docs/partials/sec_hrm.blade.php` (new file)
- `resources/views/app/docs/partials/sec_import.blade.php` (new file)
- `resources/views/app/docs/partials/sec_inventory.blade.php` (new file)
- `resources/views/app/docs/index.blade.php`

Tasks (Features F15, F16, F17, F18):
1. Author `sec_hrm.blade.php` (`resources/views/app/docs/partials/sec_hrm.blade.php`)
2. Author `sec_import.blade.php` (`resources/views/app/docs/partials/sec_import.blade.php`)
3. Author `sec_inventory.blade.php` (`resources/views/app/docs/partials/sec_inventory.blade.php`)
4. Integrate into `resources/views/app/docs/index.blade.php`
5. Verification via `php artisan view:cache`
