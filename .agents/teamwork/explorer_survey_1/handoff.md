# Comprehensive Survey Report: Invoices & Labor-Machines Blade Views

**Agent**: `explorer_survey_1`  
**Date**: 2026-11-09  
**Scope**:  
1. `resources\views\app\invoices` (and subdirectories/components)  
2. `resources\views\app\labor-machines` (and subdirectories/components)  
**Guidelines**: `ui-simplify-layout` & `ORIGINAL_REQUEST.md`

---

## 1. Observation

A full read-only survey of all Blade templates in the target directories identified **6 Blade view files** totaling **4,062 lines of code**:

| File Path | Lines | Byte Size | Primary Purpose |
|---|---|---|---|
| `resources\views\app\invoices\index.blade.php` | 450 | 34,448 B | Main listing of invoices, KPI summary, filters, dense table & mobile card list, delete modal |
| `resources\views\app\invoices\create.blade.php` | 498 | 39,000 B | Invoice creation form, PO pulling, dynamic item rows, Alpine margin/tax calculation, save action |
| `resources\views\app\invoices\edit.blade.php` | 440 | 35,057 B | Invoice edit form for draft/unpaid invoices, live financial recalculation, save action |
| `resources\views\app\invoices\show.blade.php` | 915 | 66,700 B | Detail invoice document view, party cards, items table, profit analysis, payment receipts history, 5 action modals (Payment, Release, Void, Delete Draft, Remind) |
| `resources\views\app\invoices\print.blade.php` | 491 | 27,105 B | Dedicated A4 print/PDF template, floating action toolbar, official bank transfer box, signature blocks |
| `resources\views\app\labor-machines\index.blade.php` | 1,268 | 95,200 B | Monolithic management of labor rates and machine costs, 4 KPI cards, sub-navigation, search & segmented tabs, 2 dense tables, 2 mobile lists, 4 form modals, 1 delete dialog |

---

### File-by-File Detailed Observations

#### A. `resources\views\app\invoices\index.blade.php`
1. **Component Hierarchy**:
   - `x-module-header` (lines 26–45): Contains module title, subtitle, Export CSV link, and Create New button.
   - `x-module-tabs` (line 47): Navigation tabs for the Sales module.
   - Flash Messages (lines 52–67): Success and error message banners.
   - KPI Summary (lines 72–116): 4 cards in `grid grid-cols-2 lg:grid-cols-4` (Total Invoiced, Total Paid, Unpaid Receivables, Real Gross Profit).
   - Filter Toolbar (lines 121–154): Search input + Status select dropdown (`onchange="this.form.submit()"`).
   - Desktop/Tablet Table (lines 161–305): 8 columns with sortable headers (`coocaTableSort()`) and sticky action column (`sticky right-0`).
   - Mobile Grouped Inset List (lines 310–386): Inset list items for viewports `< 640px`.
   - Apple Alert Dialog (lines 391–441): Centered native-style delete confirmation modal via `x-teleport="body"`.
2. **Direct Observations of Defects & Anomalies**:
   - **Severe Mobile Button Crowding & Sub-Standard Touch Targets**:
     Lines 345–373 in the mobile list layout:
     ```blade
     <div class="flex items-center gap-1.5 shrink-0">
         <a href="{{ route('invoices.pdf', $inv->id) }}" target="_blank"
            class="h-8 px-2 rounded-[8px] text-[12px] font-medium ...">
             <i data-lucide="printer" class="w-3.5 h-3.5 ..."></i>
             <span>PDF</span>
         </a>
         @if(!in_array($inv->status, ['paid', 'void'], true) && (float) $inv->paid_amount <= 0 && (\App\Support\Context::hasPermission('invoices.edit') || \App\Support\Context::hasPermission('invoices.create')))
             <a href="{{ route('invoices.edit', $inv->id) }}"
                class="h-8 px-2 rounded-[8px] text-[12px] font-medium ...">
                 <i data-lucide="edit-3" class="w-3.5 h-3.5 ..."></i>
                 <span>{{ __('common.edit') }}</span>
             </a>
         @endif
         <a href="{{ route('invoices.show', $inv->id) }}"
            class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium ...">
             <span>{{ __('sales.invoices.btn_detail') }}</span>
             <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
         </a>
         @if($inv->status === 'draft' && \App\Support\Context::hasPermission('invoices.delete'))
             <button type="button" @click="openDelete(...)"
                     class="h-8 w-8 rounded-[8px] ...">
                 <i data-lucide="trash-2" class="w-4 h-4"></i>
             </button>
         @endif
     </div>
     ```
     Each mobile card forces up to 4 inline action buttons side-by-side (`[PDF] [Edit] [Detail >] [Trash]`).
     All 4 buttons have a fixed height of `h-8` (32px), with the delete button being `32x32px`. This violates the Apple HIG minimum touch target of 44×44px. On a 375px mobile screen, these buttons consume ~200px width, compressing invoice numbers, customer names, and amounts into ~140px, causing aggressive truncation.
   - **Table Overflow Dropdown Clipping**:
     Lines 161–162 wrap the desktop table with `overflow-hidden` and `overflow-x-auto`. The action column dropdown (lines 249–279) uses `absolute right-0 mt-1.5 w-48 ... z-30` without portal teleportation (`x-teleport`). When the table has only 1 or 2 rows, opening the dropdown extends beyond the bottom of the table and is clipped by the table scroll container.
   - **Ungated Actions & Raw Context Calls**:
     - Line 258: `route('invoices.pdf', $inv->id)` in the desktop dropdown has no permission check.
     - Line 346: `route('invoices.pdf', $inv->id)` in the mobile list has no permission check.
     - Line 360: Detail link has no explicit permission check.
     - Lines 30, 38, 263, 270: Uses `\App\Support\Context::hasPermission(...)` instead of idiomatic Laravel `@can` directives.

---

#### B. `resources\views\app\invoices\create.blade.php`
1. **Component Hierarchy**:
   - Header Toolbar (lines 184–208): Breadcrumbs, title, Cancel button, and Save button.
   - Client Info Panel (lines 213–314): Customer select, PO pull option, sender/recipient names, dates, terms, warehouse location, attachment info.
   - Items Section (lines 319–419): Items table with live product selection, unit, quantity, selling price, COGS snapshot (`unit_hpp`), row subtotal, and add/remove item controls.
   - Calculation & Margin (lines 422–479): Estimated gross profit card (`#34C759`), notes, warranty terms, and totals breakdown (subtotal, discount, tax, shipping, grand total).
   - Bottom Actions (lines 482–495): Secondary Cancel link + Primary Save button.
2. **Direct Observations of Defects & Anomalies**:
   - **Duplicate & Competing Solid Primary Buttons**:
     - Line 203: `<button type="submit" class="... text-white bg-[#007AFF] ...">{{ __('sales.invoices.btn_save_invoice') }}</button>` in the top header.
     - Line 488: `<button type="submit" class="... text-white bg-[#007AFF] ...">{{ __('sales.invoices.btn_save_invoice') }}</button>` at the bottom of the form.
     Both buttons are styled as primary solid blue buttons, violating `ui-simplify-layout` ("Satu primary per layar/kartu/modal. Dua tombol solid yang bersaing membuat pengguna ragu").
   - **Authorization Bypass on Top Submit Button**:
     - Line 203 (top Save button) has **zero** permission gating.
     - Line 487 (bottom Save button) is wrapped with `@if(\App\Support\Context::hasPermission('invoices.create'))`.
     A user lacking `invoices.create` sees the top button active and clickable, while the bottom button is hidden.
   - **Sub-Standard Mobile Touch Targets**:
     - Line 326: "Add Item" button is `h-8` (32px).
     - Line 354: Mobile item remove button is `h-6 px-2` (24px height).
     - Lines 378, 386, 391, 396: Grid of inputs uses `w-[48%]` with `ml-[4%]`, leading to cramped inputs on screens `< 380px`.
   - **Semantic Icon Inconsistency**:
     - Line 204: Uses `data-lucide="send"` for saving an invoice.
     - Line 490: Uses paper plane SVG for saving an invoice.
     `ui-simplify-layout` mandates `check` or `save` for saving actions, reserving `send` for actual email/message dispatch.

---

#### C. `resources\views\app\invoices\edit.blade.php`
1. **Component Hierarchy**:
   - Matches `create.blade.php` with populated values from `$invoice` model: Header toolbar, client info, items table, profit/totals calculation, and bottom actions.
2. **Direct Observations of Defects & Anomalies**:
   - **Identical Duplicate Primary Buttons & Top Authorization Bypass**:
     - Line 169: Top submit button has NO permission check.
     - Line 431: Bottom submit button has `@if(\App\Support\Context::hasPermission('invoices.edit') || \App\Support\Context::hasPermission('invoices.create'))`.
     - Both top and bottom buttons are solid blue primary buttons.
   - **Inconsistent Button Heights Between Create and Edit**:
     - In `create.blade.php` (line 484), buttons use `min-h-[44px] h-11`.
     - In `edit.blade.php` (lines 428 & 432), bottom buttons use `h-9` (36px), violating 44px touch target rules on touch devices.
   - **Mobile Item Card Ergonomics**:
     - Add item button `h-8` (32px), remove item button `h-6` (24px).

---

#### D. `resources\views\app\invoices\show.blade.php`
1. **Component Hierarchy**:
   - Header Toolbar (lines 89–231): `<x-module-header>` with status badge, Back button, "Rilis Draf" button, "Catat Pembayaran" button, WhatsApp link, "Cetak A4" button, and `⋯` Actions dropdown.
   - Main Document Card (lines 238–503): Commercial invoice card with PO reference, dates, party information (seller & customer inset Bento cards), warehouse badge, items table, real gross profit analysis card, financial calculation breakdown, and payment receipts history table.
   - Action Modals (lines 508–912):
     1. Record Payment Modal (`showPaymentModal`, lines 508–612)
     2. Release Confirmation Dialog (`confirmReleaseModalOpen`, lines 618–667)
     3. Void Confirmation Dialog (`confirmVoidModalOpen`, lines 673–722)
     4. Delete Draft Dialog (`confirmDeleteModalOpen`, lines 728–777)
     5. Term Reminder Modal (`showReminderModal`, lines 782–912)
2. **Direct Observations of Defects & Anomalies**:
   - **Header Action Crowding on Mobile**:
     Lines 107–146 define actions in `flex items-center gap-2 flex-wrap sm:flex-nowrap justify-start sm:justify-end`.
     When multiple actions are eligible, on mobile they wrap into 2–3 rows of buttons, displacing title and badge and causing vertical clutter.
   - **Ungated Print & Communication Actions**:
     - Line 142: Quick "Cetak A4" button (`route('invoices.print', $invoice->id)`) has NO permission check.
     - Line 191: "Cetak A4" link in the overflow dropdown has NO permission check.
     - Line 134: "Buka WhatsApp" link has NO permission check.
   - **Modal Sizing Anomaly**:
     - Record Payment modal (line 529) uses `sm:max-w-[95vw] lg:max-w-3xl` instead of the 2-column Bento canvas prescribed by `ui-simplify-layout`.
     - Reminder modal (line 802) uses `max-w-lg`.
   - **Raw Context Calls**:
     Lines 57–63 declare PHP variables checking `\App\Support\Context::hasPermission(...)` rather than using Blade directives `@can`.

---

#### E. `resources\views\app\invoices\print.blade.php`
1. **Component Hierarchy**:
   - Floating Action Toolbar (lines 111–154): Back button, System Print button, and Direct PDF Download button.
   - Print Sheet (lines 157–407): A4 portrait paper container with company header, buyer details, itemized table, bank transfer payment box, financial totals, and dual signature blocks.
   - Embedded Scripts (lines 409–488): `downloadPDF()` using `html2pdf.js` and URL search param auto-executors (`?print=1` or `?download=1`).
2. **Direct Observations of Defects & Anomalies**:
   - **CRITICAL JAVASCRIPT SYNTAX ERROR**:
     Lines 472–477 contain an unclosed `else` block and an unclosed `function downloadPDF()`:
     ```javascript
                 } else {
                     element.style.padding = prevPadding;
             function downloadPDF() {
                 window.location.href = "{{ route('invoices.pdf', $invoice->id) }}";
             }

             window.addEventListener('load', () => {
                 const params = new URLSearchParams(window.location.search);
     ```
     Verification with `node -e` confirmed:
     `JS ERROR: Unexpected token ')'`
     Because of this syntax error:
     1. The browser's JavaScript engine throws a syntax error on script load.
     2. `downloadPDF()` fails to compile.
     3. `window.addEventListener('load')` is never registered, breaking auto-print and auto-download query params (`?print=1`, `?download=1`).
   - **External CDN Dependency in Production View**:
     Line 15: `<script src="https://cdn.tailwindcss.com"></script>` is loaded over CDN. In offline or private networks, printable invoices lose styling completely.
   - **Unprotected Print View**:
     The view contains no authorization checks around actions.

---

#### F. `resources\views\app\labor-machines\index.blade.php`
1. **Component Hierarchy**:
   - Header (lines 112–152): Page header with title, subtitle, breadcrumb, and 2 creation buttons: "Tambah Tarif Upah" and "Tambah Mesin Baru".
   - Sub-Navigation Tabs (lines 157–195): Segmented pill bar linking to Calculator, Labor-Machines, and Profitability.
   - Flash Messages (lines 201–221): Success/error alerts.
   - KPI Summary (lines 226–272): 4 cards in `grid grid-cols-2 lg:grid-cols-4`.
   - Controls Toolbar (lines 277–315): Search input + Segmented filter controls ("Semua", "Tenaga Kerja", "Mesin").
   - Direct Labor Section (lines 320–515):
     - Header with "Tambah Posisi" button.
     - Desktop dense table (lines 351–454).
     - Mobile grouped inset list (lines 457–514).
   - Machines Section (lines 520–726):
     - Header with "Tambah Mesin" button.
     - Desktop dense table (lines 551–664).
     - Mobile grouped inset list (lines 667–725).
   - Modals & Dialogs (lines 731–1265):
     - Add Labor Rate modal (`showLaborModal`, lines 731–849)
     - Edit Labor Rate modal (`showEditLaborModal`, lines 853–974)
     - Add Machine modal (`showMachineModal`, lines 979–1093)
     - Edit Machine modal (`showEditMachineModal`, lines 1098–1213)
     - Delete Confirmation dialog (`deleteModalOpen`, lines 1218–1259)
2. **Direct Observations of Defects & Anomalies**:
   - **CRITICAL PERMISSION MISMATCH (Route RBAC vs View Authorization)**:
     - In `routes/owner.php` (lines 373–383):
       ```php
       Route::middleware(['module:labor_machines', 'require.permission:labor_machines.view'])->group(function (): void {
           Route::get('/labor-machines', [LaborMachineWebController::class, 'index'])->name('labor-machines.index');
       });
       Route::middleware(['module:labor_machines', 'require.permission:labor_machines.manage'])->group(function (): void {
           Route::post('/labor-rates', [LaborMachineWebController::class, 'storeLabor'])->name('labor-rates.store');
           Route::put('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'updateLabor'])->name('labor-rates.update');
           Route::delete('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'destroyLabor'])->name('labor-rates.destroy');
           Route::post('/machines', [LaborMachineWebController::class, 'storeMachine'])->name('machines.store');
           Route::put('/machines/{machine}', [LaborMachineWebController::class, 'updateMachine'])->name('machines.update');
           Route::delete('/machines/{machine}', [LaborMachineWebController::class, 'destroyMachine'])->name('machines.destroy');
       });
       ```
     - In `resources/views/app/labor-machines/index.blade.php`:
       Lines 138, 337, 410, 483, 538, 619, 694 check:
       `@if (\App\Support\Context::hasPermission('costing.manage'))`!
     - **Severe Operational Impact**:
       The view checks `costing.manage`, but the routes strictly require `labor_machines.manage`. A user assigned the seeded role permission `labor_machines.manage` will see all action buttons completely hidden in the UI. Conversely, a user with only `costing.manage` will see the action buttons, but submitting forms results in a 403 Forbidden error.
   - **Absence of Controller-Level RBAC in `LaborMachineWebController`**:
     Inspecting `app\Http\Controllers\Web\LaborMachineWebController.php` reveals that it does NOT implement `HasMiddleware` and defines zero controller-level permission declarations, violating R2.
   - **Button Proliferation & Lack of Contextual Filtering**:
     - Header has: "Tambah Tarif Upah" and "Tambah Mesin Baru" (lines 141–149).
     - Section 1 has: "Tambah Posisi" (line 339).
     - Section 2 has: "Tambah Mesin" (line 540).
     Four separate "Add" buttons appear on one screen. When filtered to "Tenaga Kerja" via the segmented control, the header still displays "Tambah Mesin Baru"; when filtered to "Mesin", it still displays "Tambah Tarif Upah".
   - **Sub-Standard Mobile Touch Targets & Dangerous Proximity**:
     Lines 495, 500, 706, 712 in the mobile inset lists:
     - Edit button: `h-8 px-2.5` (32px height).
     - Delete button: `h-8 w-8` (32×32px).
     - Gap between Edit and Delete: `gap-1.5` (6px).
     Both buttons are far below the 44px minimum target and are placed 6px apart, causing frequent accidental taps of the destructive delete button on mobile devices.
   - **Narrow Form Modals (`sm:max-w-md`)**:
     All 4 modals (Add Labor, Edit Labor, Add Machine, Edit Machine) use `class="... sm:max-w-md ..."` (448px max width). Form parameters (Hari/Bulan, Jam/Hari, Utilisasi %, Daya kW, Listrik/kWh, Rawat/Jam) are compressed into 3-column micro-grids with tiny inputs.
   - **Hardcoded URL String Action Routes**:
     - Line 888: `:action="'/labor-rates/' + editLabor.id"`
     - Line 1133: `:action="'/machines/' + editMachine.id"`
     Uses string concatenation instead of named routes or parameterized JS templates.
   - **Extensive Hardcoded Indonesian Text (i18n Non-Compliance)**:
     Over 40 text nodes in `labor-machines/index.blade.php` are hardcoded Indonesian strings without `__()` helpers (e.g., titles, breadcrumbs, table headers, parameter headings, delete confirmation prompts).

---

## 2. Logic Chain

1. **Premise 1 (Script Compilation)**: Any syntax error in a `<script>` tag prevents the script block from compiling and halts all event listener executions within that block.
   - *Observation*: `resources\views\app\invoices\print.blade.php` (lines 472–477) has mismatched braces and an unclosed function, throwing `Unexpected token ')'`.
   - *Inference*: Client-side PDF generation and automated URL parameter handling (`?print=1`, `?download=1`) are non-functional.

2. **Premise 2 (Authorization Synchronization)**: UI action affordances must match backend enforcement; otherwise, users face either hidden features (false negatives) or 403 Forbidden errors (false positives).
   - *Observation*: `routes/owner.php` enforces `labor_machines.manage` on all state-mutating routes, but `labor-machines/index.blade.php` guards buttons with `costing.manage`.
   - *Inference*: Role assignments based on `RbacSeeder` for `labor_machines.manage` break on this view. Synchronizing the view guards to `@can('labor_machines.manage')` is strictly required.

3. **Premise 3 (Controller-Level RBAC Requirement R2)**: Security controls must reside at the controller level (`HasMiddleware`), ensuring that authorization cannot be bypassed if route groups change.
   - *Observation*: `InvoiceWebController` implements `HasMiddleware`, but `LaborMachineWebController` does not implement `HasMiddleware` and has no controller guards.
   - *Inference*: `LaborMachineWebController` must be refactored to implement `HasMiddleware` with explicit permissions (`labor_machines.view`, `labor_machines.manage`).

4. **Premise 4 (Visual Clarity and Primary Action Hierarchy)**: Interfaces that present multiple identical primary solid buttons cause visual competition and decision friction.
   - *Observation*: `invoices/create.blade.php` and `invoices/edit.blade.php` provide identical solid `#007AFF` Save buttons at both the top toolbar and bottom footer. Furthermore, the top button lacks authorization checks.
   - *Inference*: The header should either host a single authoritative primary action or use a sticky bottom action bar, removing redundant solid buttons and ensuring all submission buttons are gated with `@can`.

5. **Premise 5 (Mobile Touch Ergonomics & Touch Target Sizing)**: Touch targets below 44×44px or spaced less than 8px apart violate Apple HIG and `ui-simplify-layout` and lead to mis-clicks.
   - *Observation*: `invoices/index.blade.php` places 4 buttons (`h-8`, 32px) side-by-side in each mobile card. `labor-machines/index.blade.php` places an Edit button (32px) 6px away from a Delete button (32×32px).
   - *Inference*: On mobile cards, secondary actions must be consolidated into an overflow menu (`⋯`) or the whole card should tap through to detail, leaving only 1 primary action or well-spaced targets (`min-h-[44px]`).

6. **Premise 6 (Modal Form Dimensioning)**: Data entry modals constrained to `max-w-md` force parameter grids into cramped columns that impair usability.
   - *Observation*: All 4 labor/machine modals use `sm:max-w-md`.
   - *Inference*: Modals should expand to standard full-size Bento canvas (`max-w-4xl` / `max-w-5xl` on desktop, adaptive bottom sheets with safe-area padding on mobile).

---

## 3. Caveats

1. **Read-Only Scope**: In strict accordance with the explorer archetype, no source files were modified during this survey.
2. **Headless Environment**: Visual inspection was conducted via code analysis and static parsers. Real browser rendering across various viewport widths (320px, 375px, 768px, 1280px) should be verified once implementers apply layout updates.
3. **Database Seed State**: Permissions observed in `RbacSeeder.php` reflect the repository's current definition. If new granular permissions (e.g., `invoices.print`) are desired, they must be registered in the seeder.

---

## 4. Conclusion

Both modules exhibit strong visual foundations aligned with Apple HIG aesthetic guidelines, but contain **critical syntax, authorization, and mobile layout defects** that prevent production readiness:

1. **Critical Defect**: `resources\views\app\invoices\print.blade.php` has a JavaScript syntax error breaking PDF and print triggers.
2. **Authorization Mismatch**: `resources\views\app\labor-machines\index.blade.php` guards write actions with `costing.manage` instead of `labor_machines.manage`, leading to authorization failures.
3. **Missing Controller RBAC**: `LaborMachineWebController` lacks `HasMiddleware` and controller-level guards.
4. **Security Leak in Views**: Top submission buttons in `invoices/create` and `invoices/edit` lack permission checks, and print links in `invoices/index` and `invoices/show` are completely ungated.
5. **UI Clutter & Ergonomic Violations**:
   - Competing primary solid buttons in invoice creation and editing views.
   - 4 competing "Add" buttons across header and section headers in labor-machines.
   - Squeezed 32px touch targets and dangerous button proximity on mobile cards.
   - Narrow form modals (`sm:max-w-md`) cramping multi-parameter forms.
   - Extensive hardcoded Indonesian strings in `labor-machines`.

---

## 5. Verification Method

### 1. Reproduce Syntax Error in `invoices/print.blade.php`
Execute:
```powershell
node -e "const fs = require('fs'); const content = fs.readFileSync('resources/views/app/invoices/print.blade.php', 'utf8'); const scripts = [...content.matchAll(/<script>([\s\S]*?)<\/script>/g)]; try { new Function(scripts[1][1]); console.log('Parsed OK'); } catch(e) { console.error('JS ERROR:', e.message); }"
```
*Expected Result*: Throws `JS ERROR: Unexpected token ')'` due to unclosed braces at lines 472–477.

### 2. Verify PHP Syntax of All 6 Views
Execute:
```powershell
php -l resources\views\app\invoices\index.blade.php
php -l resources\views\app\invoices\create.blade.php
php -l resources\views\app\invoices\edit.blade.php
php -l resources\views\app\invoices\show.blade.php
php -l resources\views\app\invoices\print.blade.php
php -l resources\views\app\labor-machines\index.blade.php
```
*Expected Result*: All 6 files pass `No syntax errors detected`.

### 3. Verify Permission Mismatch in Labor-Machines
Inspect lines 376–383 of `routes/owner.php` vs lines 138, 337, 410, 538, 619 of `resources/views/app/labor-machines/index.blade.php`:
- Route expects: `require.permission:labor_machines.manage`
- View checks: `\App\Support\Context::hasPermission('costing.manage')`
- Invalidation condition: If view is updated to `@can('labor_machines.manage')`, both route and view will check the exact same ability.

### 4. Verify Missing Controller-Level RBAC
Inspect `app/Http/Controllers/Web/LaborMachineWebController.php`:
- Verify presence of `implements HasMiddleware` and `public static function middleware(): array`.
- Invalidation condition: Controller must register `require.permission:labor_machines.view` for `index`, and `require.permission:labor_machines.manage` for `storeLabor`, `updateLabor`, `storeMachine`, `updateMachine`, `destroyLabor`, `destroyMachine`.

---

## 6. Prioritized Remediation Roadmap (Aligned with `ui-simplify-layout`)

### Priority 1: Fix Syntax & Security Vulnerabilities
1. **Fix `invoices/print.blade.php` Script**:
   Close the `else` block properly and isolate `downloadPDF()` so `window.addEventListener('load')` executes cleanly. Replace the CDN Tailwind script with local bundle assets or fallback.
2. **Align Labor-Machines Permissions**:
   Change all `Context::hasPermission('costing.manage')` in `labor-machines/index.blade.php` to `@can('labor_machines.manage')`.
3. **Implement Controller-Level RBAC in `LaborMachineWebController`**:
   Add `implements HasMiddleware` with middleware mapping for `labor_machines.view` and `labor_machines.manage`.
4. **Gate All Action Buttons with `@can`**:
   - Wrap top Save button in `invoices/create.blade.php` with `@can('invoices.create')`.
   - Wrap top Save button in `invoices/edit.blade.php` with `@can('invoices.edit')`.
   - Wrap PDF / Print links in `invoices/index.blade.php` and `invoices/show.blade.php` with `@can('invoices.export')` or `@can('invoices.view')`.

### Priority 2: Simplify UI & Resolve Button Competing Hierarchies
1. **Eliminate Duplicate Primary Buttons**:
   In `create.blade.php` and `edit.blade.php`, retain a single primary solid action button (in the top header, or sticky bottom bar on mobile). Change duplicate buttons to secondary or remove them.
2. **Consolidate Labor-Machines Creation Actions**:
   In `labor-machines/index.blade.php`, remove redundant "Tambah Posisi" and "Tambah Mesin" from section headers. Use a single context-aware primary button in the main header that dynamically switches between "Tambah Tarif Upah" or "Tambah Mesin" based on `activeSection`.
3. **Fix Mobile Inset List Touch Targets**:
   - In `invoices/index.blade.php`, replace the 4-button cluster with a single "Detail" chevron link, moving PDF, Edit, and Delete into an overflow menu (`⋯`).
   - In `labor-machines/index.blade.php`, increase Edit and Delete button touch targets to `min-h-[44px] min-w-[44px]` and enforce minimum 8px spacing.
4. **Upgrade Modals to Full-Size Canvas**:
   Expand labor and machine form modals from `sm:max-w-md` to comfortable 2-column Bento layouts (`max-w-4xl`), ensuring parameter inputs have proper breathing room.
5. **Localize Hardcoded Strings**:
   Wrap all hardcoded Indonesian text in `labor-machines/index.blade.php` with `__()` helpers in `resources/lang/id/labor_machines.php` and `resources/lang/en/labor_machines.php`.
