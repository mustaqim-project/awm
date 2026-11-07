## 2026-11-07T11:40:42Z
You are the UI & View Survey Explorer for COOCA.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\explorer_ui_survey
Read ORIGINAL_REQUEST.md at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md

Investigate the presentation layer (Blade views) for Community, CRM, and Customers modules:
1. Identify all Blade view files in:
   - resources/views/app/community/
   - resources/views/app/crm/
   - resources/views/app/customers/
2. Examine the UI structure and layout of each view:
   - Check for double headers (redundant topbar title vs internal h1 / module-header)
   - Check for visual overlaps, broken layouts, or horizontal overflow on mobile (375px) vs desktop (1280px)
   - Check action button hierarchy (primary action distinguished from secondary/tertiary, button cramping, tap target sizes >= 44px)
   - Check table action rows, modal forms, filter bars, and card layouts
   - Check presence and quality of empty states across all listings/tables
3. Formulate concrete UI simplification and responsive optimization recommendations for each file.

Write your comprehensive findings to:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_ui_survey\survey_report.md
And create your handoff in:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_ui_survey\handoff.md

Report back when complete.
