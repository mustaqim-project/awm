# Technical UI Layout, Decluttering & Responsiveness Survey Report

**Module Scope**: HRM (`resources/views/app/hrm`), Import (`resources/views/app/import`), Inventory (`resources/views/app/inventory`)  
**Survey Date**: 2026-11-08  
**Survey Agent**: teamwork_explorer (`explorer_survey_ui`)  
**Status**: Completed (Read-Only Investigation)

---

## 1. Observation

### 1.1 Complete View Inventory (32 Blade Files Surveyed)

| Module | Sub-Directory / File | File Size | Primary Purpose | Key Layout Characteristics |
|---|---|---|---|---|
| **HRM** | `resources/views/app/hrm/index.blade.php` | 53.5 KB | Main HRM Hub & Controller State | Alpine.js reactive state (lines 8–772), Bento executive hero (lines 777–840), segmented tab navigation (lines 865–933), tab CTA bar (lines 935–1039), modular partial includes (lines 1045–1072). |
| HRM | `partials/tab-employees.blade.php` | 27.5 KB | Karyawan & Profil Gaji | Dual view: sortable desktop table (min-w 1050px) + mobile bento card list (<768px). Action buttons in table use sticky right column. |
| HRM | `partials/tab-attendance.blade.php` | 34.6 KB | Presensi & Rekap Harian | Face attendance widget + digital clock + GPS radar card + daily stats + daily log table + filter form. |
| HRM | `partials/tab-corrections.blade.php` | 13.8 KB | Tiket Koreksi Presensi | Approval table + status badges + header action button. |
| HRM | `partials/tab-locations.blade.php` | 14.7 KB | Lokasi Outlet & Geofence | Location list + GPS coordinates + radius limits + primary office badge + header action button. |
| HRM | `partials/tab-shifts.blade.php` | 15.6 KB | Shift Kerja Karyawan | Concept explanation bento alert + shift list + grace period + branch coverage table. |
| HRM | `partials/tab-schedules.blade.php` | 18.3 KB | Jadwal & Roster Karyawan | Multi-parameter filter form (4-col grid) + weekly schedule matrix table. |
| HRM | `partials/tab-payrolls.blade.php` | 15.7 KB | Riwayat Penggajian | Monthly payroll batch run table + gross/deductions/THP breakdown + status badges. |
| HRM | `partials/tab-loans.blade.php` | 13.9 KB | Kasbon & Pinjaman | Active loans table + installments + remaining balance + status badges. |
| HRM | `modals/add-employee.blade.php` | 31.3 KB | Tambah Karyawan | Teleported 2-column XXL canvas modal (`z-[200]`, max-w-5xl) with 3 functional clusters. |
| HRM | `modals/edit-employee.blade.php` | 31.8 KB | Edit Karyawan | Teleported 2-column XXL canvas modal (`z-[200]`, max-w-5xl). |
| HRM | `modals/add-correction.blade.php` | 10.6 KB | Pengajuan Koreksi | Teleported modal for correction request. |
| HRM | `modals/review-correction.blade.php` | 15.1 KB | Review & Otorisasi Koreksi | Modal for owner/admin approval with reason & evidence inspection. |
| HRM | `modals/add-loan.blade.php` | 8.7 KB | Catat Pinjaman/Kasbon | Modal for recording employee advance loan. |
| HRM | `modals/add-location.blade.php` | 11.0 KB | Tambah Lokasi Geofence | Modal for branch coordinates and geofence radius. |
| HRM | `modals/edit-location.blade.php` | 10.9 KB | Edit Lokasi Geofence | Modal for updating branch coordinates. |
| HRM | `modals/add-shift.blade.php` | 11.7 KB | Tambah Shift Kerja | Modal for setting work shift hours & grace period. |
| HRM | `modals/edit-shift.blade.php` | 12.2 KB | Edit Shift Kerja | Modal for updating shift hours. |
| HRM | `modals/add-schedule.blade.php` | 13.4 KB | Atur Jadwal Roster | Modal for assigning employees to shifts. |
| HRM | `modals/edit-schedule.blade.php` | 14.1 KB | Edit Jadwal Roster | Modal for updating employee rosters. |
| HRM | `modals/face-attendance.blade.php` | 9.4 KB | Presensi Wajah Real-Time | Teleported webcam HUD modal with iOS bottom sheet mobile layout. |
| HRM | `modals/register-face.blade.php` | 10.9 KB | Perekaman Wajah | Camera capture and biometric face embedding modal. |
| HRM | `modals/import-employee.blade.php` | 21.1 KB | Import Karyawan Excel | Pre-commit preview modal with duplicate resolution strategy. |
| HRM | `modals/import-attendance.blade.php` | 22.6 KB | Import Log Mesin Presensi | Pre-commit preview modal for multi-brand biometric logs. |
| HRM | `payroll/create.blade.php` | 33.1 KB | Form Batch Penggajian Baru | Multi-employee calculation table + floating sticky summary action bar. |
| HRM | `payroll/show.blade.php` | 61.7 KB | Detail Batch Penggajian | Bento financial summary + export hub + payroll approval and disbursement workflow. |
| HRM | `payroll/payslip.blade.php` | 27.6 KB | Slip Gaji Karyawan | Standalone printable A4 sheet + top screen action bar (PDF, print, WA share). |
| **Import** | `resources/views/app/import/index.blade.php` | 186.8 KB | Unified Data Exchange Hub | **2,185 lines**. Monolithic view containing 5 domain cards (over 25 micro-buttons) preceding the 4 in-hub tabs (Materials, Products, Recipes, Inventory). |
| **Inventory** | `resources/views/app/inventory/stocks.blade.php` | 33.5 KB | Inventori Multi-Gudang | Bento KPI cards (valuation, min stock, locations) + sortable desktop table + mobile grouped inset list + quick adjust modal sheet. |
| Inventory | `resources/views/app/inventory/movements.blade.php` | 27.6 KB | Riwayat Mutasi Stok | Filter toolbar + sortable audit log table + mobile grouped card list. |
| Inventory | `resources/views/app/inventory/opname.blade.php` | 58.9 KB | Stock Opname Fisik | Toolbar + opname run table + mobile list + create opname modal + reconcile modal + import opname modal. |
| Inventory | `resources/views/app/inventory/transfers.blade.php` | 28.5 KB | Transfer Antar-Cabang | Transfer document table + mobile list + new transfer modal + receive modal. |

---

### 1.2 Direct Code Observations & Verbatim Excerpts

#### Observation O-HRM-1: Button Duplication in Tab Corrections & Tab Locations
- **File**: `resources/views/app/hrm/index.blade.php` (lines 992–1006)
  ```blade
  <template x-if="activeTab === 'corrections'">
      <button type="button" @click="showAddCorrectionModal = true"
          class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(255,149,0,0.3)] transition active:scale-[0.98] cursor-pointer">
          <i data-lucide="plus" class="w-4 h-4"></i>
          <span>{{ __('hrm.btn_apply_correction') }}</span>
      </button>
  </template>

  <template x-if="activeTab === 'locations'">
      <button type="button" @click="showAddLocationModal = true"
          class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#FF2D55] hover:bg-[#E02648] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(255,45,85,0.3)] transition active:scale-[0.98] cursor-pointer">
          <i data-lucide="plus" class="w-4 h-4"></i>
          <span>{{ __('hrm.btn_add_location') }}</span>
      </button>
  </template>
  ```
- **Concurrently in**: `resources/views/app/hrm/partials/tab-corrections.blade.php` (lines 16–20)
  ```blade
  <button type="button" @click="showAddCorrectionModal = true"
      class="h-10 px-4 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(255,149,0,0.3)] transition active:scale-[0.98] cursor-pointer shrink-0">
      <i data-lucide="plus" class="w-4 h-4"></i>
      <span>{{ __('hrm.btn_apply_correction') }}</span>
  </button>
  ```
- **Concurrently in**: `resources/views/app/hrm/partials/tab-locations.blade.php` (lines 16–20)
  ```blade
  <button type="button" @click="showAddLocationModal = true"
      class="h-10 px-4 rounded-[12px] bg-[#FF2D55] hover:bg-[#E02648] text-white text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(255,45,85,0.3)] transition active:scale-[0.98] cursor-pointer shrink-0">
      <i data-lucide="plus" class="w-4 h-4"></i>
      <span>{{ __('hrm.btn_add_location') }}</span>
  </button>
  ```
- **Impact**: Two identical solid primary buttons appear simultaneously in the viewport whenever the user activates the "Koreksi" or "Lokasi" tabs.

#### Observation O-HRM-2: Competing Visual Weight in Schedule Filter vs CTA
- **File**: `resources/views/app/hrm/partials/tab-schedules.blade.php` (line 38)
  ```blade
  <button type="submit" class="flex-1 h-10 px-4 rounded-[10px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-xs font-bold transition cursor-pointer">
      {{ __('hrm.apply_filter') }}
  </button>
  ```
- **Compared with**: `resources/views/app/hrm/index.blade.php` (line 1018)
  ```blade
  <button type="button" @click="showAddScheduleModal = true"
      class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(88,86,214,0.3)] transition active:scale-[0.98] cursor-pointer">
      <i data-lucide="plus" class="w-4 h-4"></i>
      <span>{{ __('hrm.btn_set_roster_schedule') }}</span>
  </button>
  ```
- **Impact**: The routine table filter button and the primary modal trigger CTA use the exact same solid purple (`#5856D6`) background, violating button hierarchy rules.

#### Observation O-HRM-3: Floating Sticky Bottom Bar Collision in Payroll Calculation
- **File**: `resources/views/app/hrm/payroll/create.blade.php` (lines 378–379)
  ```blade
  <div class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-8 z-40 max-w-xl mx-auto sm:mx-0">
      <div class="p-3 sm:p-3.5 rounded-[18px] bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/10 dark:border-white/15 shadow-[0_12px_32px_rgba(0,0,0,0.18)] backdrop-blur-xl flex items-center justify-between gap-3">
  ```
- **Compared with**: `resources/views/layouts/app.blade.php` (lines 2317–2318)
  ```blade
  <nav id="bottom-nav" class="fixed inset-x-0 bottom-0 z-30 bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border-t border-black/5 dark:border-white/10 lg:hidden px-4 py-1.5 pb-[calc(0.375rem+env(safe-area-inset-bottom,0px))] shadow-[0_-4px_20px_rgba(0,0,0,0.05)] select-none">
  ```
- **Impact**: On mobile devices (`lg:hidden`), the fixed bottom bar at `bottom-4` sits directly on top of the fixed mobile bottom navigation bar (`bottom-0`, height ~56px), colliding visually and blocking taps to bottom tabs.

#### Observation O-HRM-4: Sub-44px Touch Targets in Payslip Header Actions
- **File**: `resources/views/app/hrm/payroll/payslip.blade.php` (lines 91, 97, 102, 107, 112)
  ```blade
  <a href="..." class="h-10 sm:h-9 min-h-[40px] px-3.5 rounded-[11px] ...">
  <button type="button" onclick="window.print()" class="h-10 sm:h-9 min-h-[40px] px-3.5 ...">
  <button id="btnPdf" type="button" onclick="downloadPayslipPDF()" class="h-10 sm:h-9 min-h-[40px] px-3.5 ...">
  <a href="{{ $waUrl }}" class="h-10 sm:h-9 min-h-[40px] px-3.5 ...">
  ```
- **Impact**: Height `sm:h-9` (36px) and `min-h-[40px]` are below the Apple HIG 44px minimum touch target standard. Additionally, in mobile view (`grid grid-cols-2`), solid Black (PDF) and solid Green (WhatsApp) compete with equal visual prominence.

#### Observation O-HRM-5: Misaligned Vertical Padding in Attendance Filter Bar
- **File**: `resources/views/app/hrm/partials/tab-attendance.blade.php` (line 212)
  ```blade
  <div class="flex items-end pt-5">
      <button type="submit"
          class="h-11 sm:h-9 px-4.5 rounded-[10px] sm:rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] ...">
          {{ __('common.filter') }}
      </button>
  </div>
  ```
- **Impact**: Hardcoded `pt-5` is used to push the button down to align with labeled inputs on desktop. When the form wraps into multiple lines on mobile, `pt-5` creates asymmetric, awkward vertical gaps between fields.

#### Observation O-IMP-1: Extreme Monolithic Bloat & Pre-Workspace Visual Clutter
- **File**: `resources/views/app/import/index.blade.php`
  - Total lines: **2,185 lines** in a single file without partials.
  - Lines 1–479: Alpine.js state and fetch handlers.
  - Lines 480–523: Pro plan limit upgrade banner.
  - Lines 528–630: 6 KPI metrics cards.
  - Lines 631–1054: 5 Domain Cards containing 28+ template and upload links.
  - Lines 1058–2185: Actual In-Hub Workspace with 4 tabs (Materials, Products, Recipes, Inventory).
- **Impact**: Users navigating to the Import Hub to upload a file must scroll past ~500 lines of static cards and links before reaching the actual drag-and-drop workspace at `#in-hub-workspace`.

#### Observation O-IMP-2: Severe Apple HIG Touch Target Sizing Violations (28px & 32px Buttons)
- **File**: `resources/views/app/import/index.blade.php`
  - Lines 950–960 (Domain 4 - HRM):
    ```blade
    <a href="..." class="h-7 px-2.5 rounded-[8px] bg-[#30B0C7]/10 ... text-[11px] ...">Template</a>
    <a href="..." class="h-7 px-2.5 rounded-[8px] bg-black/[0.05] ... text-[11px] ...">Upload</a>
    ```
  - Lines 973–983 (Fingerprint Machine):
    ```blade
    <a href="..." class="h-7 px-2.5 rounded-[8px] bg-[#34C759]/10 ... text-[11px] ...">Template</a>
    <a href="..." class="h-7 px-2.5 rounded-[8px] bg-black/[0.05] ... text-[11px] ...">Upload</a>
    ```
  - Lines 1019–1023 (Omnichannel Manifest):
    ```blade
    <a href="..." class="h-8 px-3 rounded-[8px] bg-[#5856D6]/10 ... text-[11px] ...">Unduh Manifest</a>
    ```
  - Lines 1040–1044 (Marketplace Consolidation):
    ```blade
    <a href="..." class="h-8 px-3 rounded-[8px] bg-[#FF9500]/10 ... text-[11px] ...">Ekspor Rekap</a>
    ```
  - Lines 1078–1114 (Segmented Tab Bar):
    ```blade
    <button type="button" @click="activeTab = 'materials'" class="h-9 px-4 rounded-[9px] text-[13px] ...">
    ```
- **Impact**: Buttons with heights of 28px (`h-7`), 32px (`h-8`), and 36px (`h-9`) represent severe usability barriers on touch screens and iPads, causing frequent missed taps.

#### Observation O-IMP-3: Lack of Mobile-Adaptive Card Views in Import Previews
- **File**: `resources/views/app/import/index.blade.php` (lines 1301–1355, 1565–1620, 1830–1885, 2095–2150)
  ```blade
  <div class="overflow-x-auto max-h-96 rounded-[10px] border border-black/5 dark:border-white/5">
      <table class="w-full text-left text-[13px]">
  ```
- **Impact**: While HRM and Inventory implement a dual layout pattern (`hidden sm:block` for tables, `sm:hidden` for cards), the Import module only provides a horizontally scrollable table with 9–12 columns inside a fixed `max-h-96` box. On a 375px mobile screen, reviewing rows and fixing validation errors is very uncomfortable.

#### Observation O-IMP-4: Missing Empty State Feedback on Filtered Previews
- **File**: `resources/views/app/import/index.blade.php` (lines 1317–1353)
  ```blade
  <tbody class="divide-y divide-black/5 dark:divide-white/5">
      <template x-for="row in (materialPreview ? materialPreview.rows : [])" :key="row.row_number">
          <tr ... x-show="materialFilter === 'all' || materialFilter === row.status">
  ```
- **Impact**: When the user clicks the filter chip "Error" or "Duplikat", and there are 0 matching items, no empty state row is displayed; the table body becomes a blank void without explanatory feedback.

#### Observation O-INV-1: Mobile Bottom Tab Bar Safe-Area Clipping in Inventory Views
- **File**: `resources/views/app/inventory/stocks.blade.php` (line 8)
  ```blade
  <div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{ ... }">
  ```
- **File**: `resources/views/app/inventory/opname.blade.php` (line 8)
  ```blade
  <div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{ ... }">
  ```
- **File**: `resources/views/app/inventory/transfers.blade.php` (line 8)
  ```blade
  <div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{ ... }">
  ```
- **Compared with**: `resources/views/app/inventory/movements.blade.php` (line 8)
  ```blade
  <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12">
  ```
- **Impact**: In `stocks`, `opname`, and `transfers`, the bottom padding is only 48px (`pb-12`). When viewed on a mobile device where `layouts/app.blade.php` renders the fixed bottom navigation bar (`z-30 lg:hidden`, height ~56px + home indicator safe-area), the bottom-most list cards and pagination controls (`{{ $stocks->links() }}`, `{{ $transfers->links() }}`) are clipped and covered by the navigation bar.

#### Observation O-INV-2: Sub-44px Hitboxes in Desktop Table Action Buttons
- **File**: `resources/views/app/inventory/stocks.blade.php` (line 237)
  ```blade
  <button type="button" @click="openAdjust('{{ $st->id }}')" class="w-9 h-9 min-h-[36px] min-w-[36px] rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] ...">
  ```
- **File**: `resources/views/app/inventory/transfers.blade.php` (line 149)
  ```blade
  <button type="button" @click="openReceive('{{ $tr->id }}', '{{ $tr->transfer_number }}')" class="w-9 h-9 min-h-[36px] min-w-[36px] rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] ...">
  ```
- **Impact**: On tablets in landscape mode (`sm:` breakpoint), the table view is active, and action buttons have a 36x36px hitbox, which falls below the 44px threshold.

---

## 2. Logic Chain

```
Observation O-HRM-1 (index.blade.php defines CTA button for active tab; tab-corrections & tab-locations also define the same CTA in card header)
  └──> Root Cause: Duplicate button declarations created during incremental feature additions without centralizing CTA placement.
  └──> Implication: User sees two identical solid primary buttons (orange/pink) simultaneously in viewport, confusing user on which to press.

Observation O-HRM-2 (tab-schedules uses solid indigo bg-[#5856D6] for filter button; index CTA also uses solid indigo)
  └──> Root Cause: Lack of differentiation between utilitarian search/filter actions and primary entity creation CTAs.
  └──> Implication: Breaks single-primary-action rule per view, diluting visual focus.

Observation O-HRM-3 (create.blade.php floating bar has fixed bottom-4; layouts/app.blade.php has fixed bottom-0 mobile nav)
  └──> Root Cause: Floating bar offset did not calculate mobile navigation bar height or safe area inset.
  └──> Implication: On mobile devices, the summary calculation card covers the mobile bottom navigation bar, causing tap collision and visual occlusion.

Observation O-HRM-4 & O-IMP-2 & O-INV-2 (buttons defined with h-7 [28px], h-8 [32px], sm:h-9 [36px], min-h-[40px])
  └──> Root Cause: Desktop-first CSS sizing applied to interactive elements without responsive min-h-[44px] touch safeguards.
  └──> Implication: Direct violation of Apple Human Interface Guidelines; produces high mis-tap rate on touchscreens.

Observation O-IMP-1 (import/index.blade.php is 2,185 lines with 5 massive domain cards before workspace)
  └──> Root Cause: Monolithic architecture where all domain template download hubs and 4 distinct import workflows are forced into one view.
  └──> Implication: Excessive page weight, cognitive overload, buried primary workspace, and poor maintainability.

Observation O-IMP-3 & O-IMP-4 (Import preview tables lack mobile cards and empty states)
  └──> Root Cause: Previews implemented solely for desktop data inspection without mobile-first progressive disclosure or Alpine fallback templates.
  └──> Implication: Mobile users experience poor UX trying to scroll a 10-column table in a 384px box, and filtering to empty yields a silent blank box.

Observation O-INV-1 (stocks, opname, transfers use pb-12; movements uses pb-28 lg:pb-12)
  └──> Root Cause: Inconsistent bottom padding conventions across views in the same module.
  └──> Implication: Mobile bottom navigation bar clips pagination links and the last row of data in 3 out of 4 inventory views.
```

---

## 3. Caveats

1. **Read-Only Investigation**: No source code was modified during this survey. Proposed adjustments are based on static analysis, responsive breakpoint tracing, and layout comparison.
2. **Dynamic Data Scaling**: Behavior of tables with 100+ items was evaluated through Blade loop analysis and pagination structure, not live browser viewport rendering with dynamic server data.
3. **Cross-Browser Font Rendering**: Font rendering on mobile Safari vs. Chrome Android was inferred from CSS utility classes (`text-[16px]` vs `text-xs` auto-zoom thresholds).
4. **Permissions Gating**: Some buttons and actions are guarded by `@if(\App\Support\Context::hasPermission(...))`. Layout checks accounted for both permitted and non-permitted states.

---

## 4. Conclusion & Actionable Recommendations

### 4.1 Prioritized Remediation Matrix

| Priority | Target File(s) | Identified Defect | Recommended Resolution |
|---|---|---|---|
| **P1 (Critical)** | `resources/views/app/hrm/payroll/create.blade.php` | Floating calculation bar at `fixed bottom-4` collides with mobile bottom navigation bar (`fixed bottom-0`). | Change wrapper class from `bottom-4` to `bottom-[calc(68px+env(safe-area-inset-bottom))] lg:bottom-4` to properly clear the mobile bottom navigation bar. |
| **P1 (Critical)** | `resources/views/app/inventory/stocks.blade.php`<br>`resources/views/app/inventory/opname.blade.php`<br>`resources/views/app/inventory/transfers.blade.php` | Main container uses `pb-12` (48px), causing mobile bottom nav bar to occlude pagination and last rows. | Standardize main container to `pb-28 lg:pb-12` (matching `movements.blade.php`) or `pb-[calc(5rem+env(safe-area-inset-bottom))]`. |
| **P1 (Critical)** | `resources/views/app/hrm/partials/tab-corrections.blade.php`<br>`resources/views/app/hrm/partials/tab-locations.blade.php` | Identical primary CTA buttons duplicated in tab card header while already present in `hrm/index.blade.php` tab CTA bar. | Remove the duplicate buttons from the card headers inside `tab-corrections` and `tab-locations`. Rely exclusively on the unified tab CTA bar in `index.blade.php`. |
| **P2 (High)** | `resources/views/app/hrm/partials/tab-schedules.blade.php` | Filter submit button uses solid indigo `bg-[#5856D6]`, competing directly with the primary "Atur Jadwal Roster" CTA. | Re-style filter button as neutral/tonal (`bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white font-semibold`), preserving solid indigo only for the main action. |
| **P2 (High)** | `resources/views/app/hrm/payroll/payslip.blade.php` | Header buttons use `h-10 sm:h-9 min-h-[40px] px-3.5` (<44px Apple HIG standard). Competing solid buttons on mobile. | Upgrade action buttons to `min-h-[44px] h-11 px-4`. Set "Cetak A4" and "Unduh PDF" as primary, while "Kirim WhatsApp" and "Salin Ringkasan" use outline/tonal styles. |
| **P2 (High)** | `resources/views/app/hrm/partials/tab-attendance.blade.php` | Filter submit wrapper has `pt-5`, causing vertical misalignment on mobile wrap. | Replace `flex items-end pt-5` with `flex items-end` and apply consistent grid alignment (`grid grid-cols-1 sm:grid-cols-4 gap-3 items-end`). |
| **P2 (High)** | `resources/views/app/import/index.blade.php` | Over 25 buttons in Domain cards use `h-7` (28px) and `h-8` (32px), violating Apple HIG 44px touch targets. | Upgrade touch targets on mobile: use `min-h-[40px] sm:min-h-[32px] px-3` or wrap in touch-friendly containers with `p-1.5`. Segmented tab bar upgraded to `h-11 sm:h-9`. |
| **P2 (High)** | `resources/views/app/import/index.blade.php` | Preview tables have no mobile card view and no empty state row when filtered to 0 items. | Add mobile card preview list (`sm:hidden`) for preview rows, and add empty state row `<tr x-show="filteredRows.length === 0"><td colspan="10" ...>` inside preview tables. |
| **P3 (Medium)** | `resources/views/app/inventory/stocks.blade.php`<br>`resources/views/app/inventory/transfers.blade.php` | Desktop table action buttons use `w-9 h-9 min-h-[36px] min-w-[36px]` (<44px on tablet landscape). | Increase button size to `w-10 h-10 min-w-[40px] min-h-[40px] sm:w-9 sm:h-9` or expand invisible tap padding. |
| **P3 (Medium)** | `resources/views/app/import/index.blade.php` | Monolithic 2,185-line file creates massive cognitive clutter before reaching the workspace. | Modularize tabs into partials (`tab-materials.blade.php`, `tab-products.blade.php`, `tab-recipes.blade.php`, `tab-inventory.blade.php`), and collapse or streamline Domain cards into progressive disclosure accordions. |

---

## 5. Verification Method

### 5.1 Syntax & Integrity Checks
Run the Blade compilation / template rendering validation:
```powershell
php artisan view:cache
```
*Expected Result*: Zero syntax errors or missing partial exceptions across all 32 files.

### 5.2 Layout Overlap Inspection Checklist
1. **Mobile Bottom Navigation Clearing**:
   - Open mobile browser (viewport 375x812px).
   - Navigate to `/app/inventory/stocks`, `/app/inventory/opname`, `/app/inventory/transfers`.
   - Scroll to the bottom of the list.
   - Verify that pagination links (`{{ $stocks->links() }}`) and the last list item are completely visible above the fixed mobile bottom bar, with at least 16px of comfortable breathing room.
2. **Payroll Create Floating Bar**:
   - Navigate to `/app/hrm/payrolls/create` on mobile viewport (375px).
   - Verify that the bottom summary bar floats at least 68px above the bottom of the screen, leaving the app navigation bar unobstructed.
3. **Button De-duplication Verification**:
   - Navigate to `/app/hrm?tab=corrections` and `/app/hrm?tab=locations`.
   - Verify that only ONE primary CTA button ("Ajukan Koreksi" / "Tambah Lokasi") is visible in the header action bar, and no duplicate button is rendered inside the table card.
4. **Touch Target Inspection**:
   - Inspect element bounding box in Chrome DevTools Device Mode for table actions, tab buttons, and modal close buttons.
   - Verify all interactive elements achieve a minimum touch bounding box of 44x44px on mobile devices.

### 5.3 Invalidation Conditions
- If `layouts/app.blade.php` removes the mobile bottom bar (`#bottom-nav`), the padding requirements can be reduced from `pb-28` to `pb-12`.
- If tablet-specific views are introduced, desktop table action sizes can remain 36px provided touch devices are routed to touch-optimized layouts.
