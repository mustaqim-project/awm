# Handoff Report: WhatsApp UI & Blade View Architecture Investigation

**Date**: 2026-11-07  
**Agent**: `explorer_wa_ui` (teamwork_explorer)  
**Parent**: `072c1567-fb7e-4663-b29a-0dc2a878655b`  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_wa_ui`

---

## Executive Summary

The WhatsApp module UI consists of four major views in `resources/views/app/whatsapp/` (`index.blade.php`, `broadcast.blade.php`, `broadcast_detail.blade.php`, and `logs.blade.php`). While the views demonstrate a strong foundation with Apple HIG aesthetic tokens, the investigation revealed **critical RBAC gaps** (no `@can` directives are used anywhere; critical destructive/mutating controls like disconnect, service toggle, and storage pruning lack any authorization guard; and Laravel's `Gate::before` does not yet bridge tenant users to `Context::hasPermission`), **action button hierarchy inversions & duplicates** (e.g., duplicated primary buttons and weak secondary submit stylings), **minor 32px touch target non-compliances** on desktop table action icons, and **hardcoded strings** in banner callouts.

---

## 1. Observation

### 1.1 Scope and File Inventory
Directly inspected the entire view hierarchy under `resources/views/app/whatsapp/`:
- `resources/views/app/whatsapp/index.blade.php` (944 lines, 63,303 bytes) — Gateway Cockpit
- `resources/views/app/whatsapp/broadcast.blade.php` (1,001 lines, 79,527 bytes) — Broadcast Hub & Modal Sheet Composer
- `resources/views/app/whatsapp/broadcast_detail.blade.php` (434 lines, 27,029 bytes) — Campaign Delivery Report & Polling
- `resources/views/app/whatsapp/logs.blade.php` (527 lines, 38,387 bytes) — Message Logs, Inspector Sheet & Pruning Modal
- Related components: `resources/views/components/module-header.blade.php`, `resources/views/components/module-tabs.blade.php`
- Routing & Navigation: `routes/owner.php` (lines 925–962), `app/Support/Navigation/NavigationRegistry.php` (lines 442–504)
- Gate & Context: `app/Providers/AppServiceProvider.php` (lines 139–150), `app/Support/Context.php` (lines 195–216)
- Test Suite: `tests/Feature/WhatsApp/MerchantWhatsAppWebFeatureTest.php` (1,105 lines)

---

### 1.2 RBAC Directives & Gate Observation
Across all four Blade views, a search for `@can` and `@cannot` yielded **0 occurrences**:
```powershell
Select-String -Path "resources\views\app\whatsapp\*.blade.php" -Pattern "@can|@cannot"
# Result: 0 matches found
```

Existing permission checks in the views exclusively use manual PHP expressions calling `\App\Support\Context::hasPermission(...)`:
- `broadcast.blade.php:19`: `@if (\App\Support\Context::hasPermission('whatsapp.manage'))`
- `broadcast.blade.php:126`: `@if (\App\Support\Context::hasPermission('whatsapp.manage'))`
- `broadcast.blade.php:811`: `@if (\App\Support\Context::hasPermission('whatsapp.manage'))`
- `broadcast_detail.blade.php:91`: `@if (\App\Support\Context::hasPermission('whatsapp.manage'))`
- `index.blade.php:24`: `@if (\App\Support\Context::hasPermission('pos.terminal'))`
- `index.blade.php:162`: `@if (\App\Support\Context::hasPermission('whatsapp.manage'))`
- `index.blade.php:282`: `@if (\App\Support\Context::hasPermission('whatsapp.manage'))`
- `index.blade.php:318`: `@if (\App\Support\Context::hasPermission('whatsapp.manage'))`

**Critical Unguarded UI Controls Directly Observed:**
1. `index.blade.php:146`: Disconnect Meta button (`@click="disconnectMeta()"`) inside the connected state card is **not wrapped in any permission check**. Any viewer with `whatsapp.view` can trigger the modal or direct disconnect call.
2. `index.blade.php:223–227`: Status Layanan WhatsApp Toko toggle (`<input type="checkbox" name="is_active" @change="toggleServiceStatus($event)">`) has **no permission check and no disabled state**. A read-only user can toggle the merchant's live service.
3. `index.blade.php:264, 277`: Auto-receipt toggle and textarea are active even if the user lacks `whatsapp.manage`. While the save button is wrapped, the form controls remain editable.
4. `index.blade.php:308, 314`: Test send phone and message input fields remain enabled for read-only users.
5. `index.blade.php:366` & `index.blade.php:515`: "Kirim Blast dengan Template" button and per-card `[Blast ->]` links in the Meta Catalog have **no permission checks**.
6. `logs.blade.php:27`: "Ekspor Excel" button (`route('whatsapp.logs.export')`) has **no permission check** in the Blade view.
7. `logs.blade.php:32`: "Bersihkan Storage" (Pruning) button and modal (`logs.blade.php:418–471`) have **no permission check**. Pruning is a destructive data purge accessible to any user who can view logs.

**Underlying Gate Architectural Observation (`app/Providers/AppServiceProvider.php:139–150`):**
```php
// Platform Admin RBAC Gate Integration
\Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
    if ($user instanceof \App\Models\Admin) {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission($ability) ? true : null;
    }

    return null;
});
```
*Direct observation*: `Gate::before` handles only `\App\Models\Admin`. For `\App\Models\User` (merchant/tenant users), `Gate::before` returns `null`. Because no gates are defined via `Gate::define()`, `@can('whatsapp.manage')` in Blade currently evaluates to `false` for all tenant users until Gate is bridged to `\App\Support\Context::hasPermission($ability)`.

---

### 1.3 Layout, Hierarchy & Button Placement Observations

#### Screen 1: `whatsapp/index.blade.php` (Gateway Cockpit)
1. **Header Action Inversion**:
   - `x-module-header` actions:
     - `a[whatsapp.inbox.index]`: Solid Blue `#007AFF` (`bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25`).
     - `a[whatsapp.logs.index]`: Tonal gray (`bg-black/[0.05] dark:bg-white/[0.08]`).
     - `a[pos.terminal]`: Tonal gray (`bg-black/[0.05] dark:bg-white/[0.08]`).
   - "Kotak Masuk" is styled as the primary solid action on a gateway connection page, competing with the setup/management CTAs inside the cockpit.
2. **Duplicated Button**:
   - "Buka Kotak Masuk" (`route('whatsapp.inbox.index')`) is rendered in the top header (lines 14–18) AND duplicated verbatim inside the Meta Connected Account card footer (lines 141–145).
3. **Migration Notice Banner (`index.blade.php:37–58`)**:
   - Takes excessive vertical height (50–60px padding) with a loud multi-color gradient (`from-[#007AFF]/[0.08] via-[#34C759]/[0.06] to-transparent`).
   - Hardcoded Indonesian strings:
     - Line 44: `<span>Pusat Pengaturan &amp; Konfigurasi WhatsApp Terpadu</span>`
     - Line 48: `<p class="...">Konfigurasi WABA, nomor pengirim, kredensial Meta Cloud API, dan template struk kasir kini dapat dikelola langsung dari menu <strong>Pengaturan Usaha</strong>.</p>`
4. **Weak Button Hierarchy in Settings Form (`index.blade.php:283–286`)**:
   - Auto-receipt save button is styled as a weak secondary gray (`bg-black/[0.05] text-black/80`), making it appear disabled or secondary rather than the action for that card.
5. **Meta Message Catalog Hardcoded Text (`index.blade.php:385, 391, 517, 523`)**:
   - Line 385: `<span>Operasional / Utility (7)</span>`
   - Line 391: `<span>Pemasaran / Marketing (3)</span>`
   - Line 517: `<span>Blast</span>`
   - Line 523: `<span>Aman 24 Jam</span>`

#### Screen 2: `whatsapp/broadcast.blade.php` (Broadcast Hub & Modal Sheet)
1. **Desktop Table Touch Target (`broadcast.blade.php:219`)**:
   - Action button in sticky right column: `class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] ..."`
   - `w-8 h-8` = 32×32px. Violates the 44×44px minimum touch target standard for mobile/touch-screen devices.
2. **Modal Composer Sheet Complexity (`broadcast.blade.php:330–829`)**:
   - Contains 500+ lines of modal markup in a single file with complex nested scroll areas:
     - Modal canvas (`max-h-[92vh] flex flex-col overflow-hidden`)
     - Outlet selector (`max-h-52 overflow-y-auto pr-1 overscroll-contain`)
     - Smartphone simulator (`h-full overflow-y-auto px-2.5 pt-2.5 pb-24`)
   - On small screens, multiple vertical scroll containers can trap touch scrolling.
   - Target audience radio grid: When business has many outlets, multi-outlet section dominates vertical height.

#### Screen 3: `whatsapp/broadcast_detail.blade.php` (Campaign Delivery Report)
1. **Clean Apple HIG Baseline**:
   - Uses Sonoma toolbar header with back button (`min-h-[44px] min-w-[44px]`).
   - 4 KPI Bento cards (`grid-cols-2 lg:grid-cols-4`).
   - Sent message preview card with monospace text and banner support.
   - Recipients audit table with automatic PII masking for non-owners (`displayPhone`).
   - Live polling via JSON endpoint every 3.5s with visibility change suspension.
2. **Action Button**:
   - Header button: `a[whatsapp.broadcast.create]` with `[+ Buat Siaran Baru]`. Needs `@can('whatsapp.manage')`.

#### Screen 4: `whatsapp/logs.blade.php` (Message Logs & Inspector)
1. **Header Action Hierarchy (`logs.blade.php:27–37`)**:
   - "Ekspor Excel": `bg-[#007AFF]/10 text-[#007AFF] min-h-[44px]` (Secondary-Accent tonal).
   - "Bersihkan Storage": `bg-black/[0.05] text-black/80 min-h-[44px]` (Secondary tonal).
   - Both actions lack RBAC guards.
2. **Desktop Table Touch Target (`logs.blade.php:202`)**:
   - Action button: `class="w-8 h-8 rounded-[8px] ..."` = 32×32px. Needs 44×44px hit area.
3. **Storage Pruning Modal (`logs.blade.php:418–471`)**:
   - Destructive dialog with warning styling (`bg-[#FF9500]`). Completely unguarded by RBAC.

---

### 1.4 Test Suite Baseline Verification
Executed `vendor\bin\phpunit --filter=test_whatsapp_views_satisfy_mobile_touch_targets_and_wcag_a11y`:
```
{"tool":"phpunit","result":"passed","tests":1,"passed":1,"assertions":17,"duration_ms":32367}
```
Existing tests require the following DOM contracts to remain intact:
- `min-h-[44px]` in `index`, `broadcast`, `logs`, `broadcast_detail`
- `min-w-[44px]` in `broadcast_detail`
- `role="dialog"` in `broadcast` and `logs`
- `aria-modal="true"` in `broadcast` and `logs`
- `aria-labelledby="broadcastModalTitle"` in `broadcast`
- `aria-label="{{ __('whatsapp.close_btn') }}"` in `broadcast`
- `aria-labelledby="inspectorModalTitle"` in `logs`
- `aria-labelledby="pruneModalTitle"` in `logs`

---

## 2. Logic Chain

```
[Requirement R2: RBAC Enforcement]
  │
  ├─► Observation: 0 `@can` directives found across all 4 WhatsApp views.
  │   Observation: `Gate::before` in `AppServiceProvider` ignores `User` model.
  │   Observation: Destructive/mutating actions (disconnect, service toggle, pruning, export) have no checks.
  │   Logic: If `Gate::before` is wired to `Context::hasPermission($ability)` for `User`,
  │          all Blade views can cleanly use native `@can('whatsapp.manage')` / `@can('whatsapp.view')`.
  │   Action: Define exact permission mapping per UI element and coordinate with RBAC explorer.
  │
[Requirement R1: UI Simplification & Decluttering]
  │
  ├─► Observation: In `index.blade.php`, "Buka Kotak Masuk" is duplicate in header and card footer.
  │   Observation: In `index.blade.php`, header action button style is solid blue primary,
  │                competing with in-page setup CTAs.
  │   Logic: By demoting header buttons to tonal secondary actions and removing the duplicated
  │          button from the Meta Connected card footer, visual clutter is reduced and hierarchy is clarified.
  │
  ├─► Observation: Form inputs (auto-receipt, test console) and service toggle are active for read-only users.
  │   Logic: Users without `whatsapp.manage` should see these controls in a disabled state
  │          (`@cannot('whatsapp.manage') disabled @endcannot`) so they clearly understand read-only status.
  │
  ├─► Observation: Desktop table action buttons in `broadcast.blade.php` and `logs.blade.php` use `w-8 h-8` (32px).
  │   Logic: Mobile/touch devices using the desktop viewport require minimum 44×44px touch targets.
  │          Updating to `min-w-[44px] min-h-[44px]` or 44px tap zone satisfies WCAG 2.2 / Apple HIG.
  │
  └─► Observation: Hardcoded Indonesian strings exist in migration notice banner and template pills.
      Logic: Wrapping in `__()` localization helpers eliminates i18n violations and supports English locale.
```

---

## 3. Caveats

1. **Gate Integration Dependency**:
   Blade's `@can('whatsapp.manage')` depends on Laravel's authorization gate recognizing the permission for tenant `User` instances. In `AppServiceProvider.php`, `Gate::before` currently only delegates for `Admin`. The implementer must ensure `Gate::before` also delegates to `Context::hasPermission($ability)` for `User` so `@can` succeeds. If `@can` is used before that bridge is added, it will fail closed (deny access).
2. **Controller-Level Guards**:
   `WhatsAppWebController` and `WhatsAppBroadcastWebController` already implement `HasMiddleware` with `require.permission:whatsapp.view` and `require.permission:whatsapp.manage`. Blade `@can` directives serve to align the UI with these already-enforced server-side guards.
3. **Multi-Outlet Scaling**:
   In businesses with 20+ branches/outlets, the outlet selector in `broadcast.blade.php` (`max-h-52 overflow-y-auto`) expands. The current layout handles this via scroll, but a compact searchable dropdown or segmented selector is recommended if outlet count exceeds 15.

---

## 4. Conclusion & Concrete Refactoring Proposals

### 4.1 Permission Mapping Matrix

| View | UI Element / Action | Proposed Directive | Permitted Role(s) |
|---|---|---|---|
| `index.blade.php` | Disconnect Meta Button (line 146) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |
| `index.blade.php` | Embedded Signup Connect Button (line 162) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |
| `index.blade.php` | Service Active Toggle (line 223) | `@can('whatsapp.manage')` (or `disabled` if cannot) | Owner, Admin (Manage) |
| `index.blade.php` | Auto-Receipt Form & Submit (lines 264, 282) | `@can('whatsapp.manage')` (controls disabled if cannot) | Owner, Admin (Manage) |
| `index.blade.php` | Test Console Form & Send Button (line 318) | `@can('whatsapp.manage')` (inputs disabled if cannot) | Owner, Admin (Manage) |
| `index.blade.php` | Meta Template Catalog Blast Buttons (lines 366, 515) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |
| `index.blade.php` | POS Kasir Link in Header (line 24) | `@can('pos.terminal')` | Cashier, Owner, Admin |
| `broadcast.blade.php` | Header "Buat Siaran Baru" Button (line 19) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |
| `broadcast.blade.php` | Empty State "Mulai Broadcast" (line 126) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |
| `broadcast.blade.php` | Modal Composer Submit Blast (line 811) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |
| `broadcast_detail.blade.php` | Header "Buat Siaran Baru" Button (line 91) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |
| `logs.blade.php` | Header "Ekspor Excel" Button (line 27) | `@can('whatsapp.view')` & `@can('reports.export')` | Owner, Admin, Manager |
| `logs.blade.php` | Header "Bersihkan Storage" Button (line 32) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |
| `logs.blade.php` | Storage Pruning Modal & Action (lines 418, 463) | `@can('whatsapp.manage')` | Owner, Admin (Manage) |

---

### 4.2 Code Snippet Diff Proposals (Before → After)

#### A. `resources/views/app/whatsapp/index.blade.php`

**1. Header Actions (Lines 13–31): Eliminate Primary Inversion & Apply `@can`**
```html
<!-- BEFORE -->
<x-slot:actions>
    <a href="{{ route('whatsapp.inbox.index') }}"
        class="flex-1 sm:flex-initial min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062cc] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto shadow-sm shadow-[#007AFF]/25">
        <i data-lucide="message-square" class="w-4 h-4 text-white"></i>
        <span>{{ __('social_media.tab_inbox') }}</span>
    </a>
    <a href="{{ route('whatsapp.logs.index') }}"
        class="flex-1 sm:flex-initial min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
        <i data-lucide="history" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
        <span>{{ __('whatsapp.tab_logs') }}</span>
    </a>
    @if (\App\Support\Context::hasPermission('pos.terminal'))
        <a href="{{ route('pos.terminal') }}"
            class="flex-1 sm:flex-initial min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
            <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
            <span>{{ __('whatsapp.open_pos') }}</span>
        </a>
    @endif
</x-slot:actions>

<!-- AFTER -->
<x-slot:actions>
    <a href="{{ route('whatsapp.inbox.index') }}"
        class="flex-1 sm:flex-initial min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
        <i data-lucide="message-square" class="w-4 h-4 text-[#007AFF]"></i>
        <span>{{ __('social_media.tab_inbox') }}</span>
    </a>
    <a href="{{ route('whatsapp.logs.index') }}"
        class="flex-1 sm:flex-initial min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
        <i data-lucide="history" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
        <span>{{ __('whatsapp.tab_logs') }}</span>
    </a>
    @can('pos.terminal')
        <a href="{{ route('pos.terminal') }}"
            class="flex-1 sm:flex-initial min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
            <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
            <span>{{ __('whatsapp.open_pos') }}</span>
        </a>
    @endcan
</x-slot:actions>
```

**2. Connected Account Footer: Remove Duplicate "Buka Kotak Masuk" & Guard Disconnect (Lines 140–151)**
```html
<!-- BEFORE -->
<div class="flex items-center gap-2">
    <a href="{{ route('whatsapp.inbox.index') }}"
        class="min-h-[44px] px-4 rounded-[12px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0062cc] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm">
        <i data-lucide="message-square" class="w-4 h-4"></i>
        <span>{{ __('social_media.tab_inbox') }}</span>
    </a>
    <button type="button" @click="disconnectMeta()"
        class="min-h-[44px] px-4 rounded-[12px] text-[12.5px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center">
        {{ __('whatsapp.disconnect_btn') }}
    </button>
</div>

<!-- AFTER -->
<div class="flex items-center gap-2">
    @can('whatsapp.manage')
        <button type="button" @click="disconnectMeta()"
            class="min-h-[44px] px-4 rounded-[12px] text-[12.5px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="unlink" class="w-3.5 h-3.5"></i>
            <span>{{ __('whatsapp.disconnect_btn') }}</span>
        </button>
    @endcan
</div>
```

**3. Service Status Toggle (Lines 223–227): Guard with Disabled Attribute**
```html
<!-- BEFORE -->
<label class="relative inline-flex items-center cursor-pointer shrink-0">
    <input type="checkbox" name="is_active" value="1" @change="toggleServiceStatus($event)" class="sr-only peer"
        {{ ($waSession?->is_active ?? true) ? 'checked' : '' }}>
    <div class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
</label>

<!-- AFTER -->
<label class="relative inline-flex items-center shrink-0 @cannot('whatsapp.manage') opacity-60 cursor-not-allowed @else cursor-pointer @endcannot">
    <input type="checkbox" name="is_active" value="1" 
        @can('whatsapp.manage') @change="toggleServiceStatus($event)" @else disabled @endcan
        class="sr-only peer"
        {{ ($waSession?->is_active ?? true) ? 'checked' : '' }}>
    <div class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
</label>
```

**4. Auto Receipt Settings: Button Hierarchy Elevation & Read-Only Handling (Lines 277–287)**
```html
<!-- BEFORE -->
<textarea name="receipt_template" rows="2"
    placeholder="{{ __('whatsapp.receipt_footer_placeholder') }}"
    class="... resize-none ...">{{ $waSession?->receipt_template ?? '' }}</textarea>
</div>

@if (\App\Support\Context::hasPermission('whatsapp.manage'))
    <button type="submit"
        class="w-full min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center">
        {{ __('whatsapp.save_receipt_settings_btn') }}
    </button>
@endif

<!-- AFTER -->
<textarea name="receipt_template" rows="2"
    @cannot('whatsapp.manage') disabled @endcannot
    placeholder="{{ __('whatsapp.receipt_footer_placeholder') }}"
    class="... resize-none @cannot('whatsapp.manage') opacity-60 cursor-not-allowed @endcannot ...">{{ $waSession?->receipt_template ?? '' }}</textarea>
</div>

@can('whatsapp.manage')
    <button type="submit"
        class="w-full min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2">
        <i data-lucide="check" class="w-4 h-4"></i>
        <span>{{ __('whatsapp.save_receipt_settings_btn') }}</span>
    </button>
@endcan
```

---

#### B. `resources/views/app/whatsapp/broadcast.blade.php`

**1. Desktop Table Eye Button Touch Target Fix (Lines 217–225)**
```html
<!-- BEFORE -->
<td class="px-5 py-3.5 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
    <div class="flex items-center justify-end">
        <a href="{{ route('whatsapp.broadcast.show', $campaign) }}"
            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
            title="{{ __('whatsapp.view_detail_action') }}"
            aria-label="{{ __('whatsapp.view_detail_action') }}">
            <i data-lucide="eye" class="w-4 h-4"></i>
        </a>
    </div>
</td>

<!-- AFTER -->
<td class="px-5 py-3.5 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
    <div class="flex items-center justify-end">
        <a href="{{ route('whatsapp.broadcast.show', $campaign) }}"
            class="min-w-[44px] min-h-[44px] w-9 h-9 rounded-[10px] flex items-center justify-center text-[#007AFF] bg-[#007AFF]/8 hover:bg-[#007AFF]/15 dark:hover:bg-[#007AFF]/25 transition-all cursor-pointer shrink-0"
            title="{{ __('whatsapp.view_detail_action') }}"
            aria-label="{{ __('whatsapp.view_detail_action') }}">
            <i data-lucide="eye" class="w-4 h-4"></i>
        </a>
    </div>
</td>
```

**2. Modal Composer Footer: Standardize `@can` Directive (Lines 811–824)**
```html
<!-- BEFORE -->
@if (\App\Support\Context::hasPermission('whatsapp.manage'))
    <button type="button" @click="submitBlast()"
        :disabled="submitting || !message.trim() || !title.trim()"
        ...
        class="min-h-[44px] px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13.5px] ...">
        ...
    </button>
@endif

<!-- AFTER -->
@can('whatsapp.manage')
    <button type="button" @click="submitBlast()"
        :disabled="submitting || !message.trim() || !title.trim()"
        ...
        class="min-h-[44px] px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13.5px] shadow-md shadow-[#007AFF]/25 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-all active:scale-[0.98] w-full sm:w-auto">
        <svg x-show="submitting" class="w-4 h-4 animate-spin text-white" ...></svg>
        <i data-lucide="send" x-show="!submitting" class="w-4 h-4"></i>
        <span x-text="..."></span>
    </button>
@endcan
```

---

#### C. `resources/views/app/whatsapp/logs.blade.php`

**1. Header Actions: Guard Excel Export & Storage Pruning (Lines 26–38)**
```html
<!-- BEFORE -->
<div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
    <a href="{{ route('whatsapp.logs.export', request()->query()) }}"
        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
        <i data-lucide="download" class="w-4 h-4"></i>
        <span>{{ __('whatsapp.export_excel') }}</span>
    </a>
    <button type="button" @click="openPruneModal = true"
        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
        <i data-lucide="database-zap" class="w-4 h-4 text-[#FF9500]"></i>
        <span>{{ __('whatsapp.storage_pruning_btn') }}</span>
    </button>
</div>

<!-- AFTER -->
<div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
    @can('whatsapp.view')
        <a href="{{ route('whatsapp.logs.export', request()->query()) }}"
            class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
            <i data-lucide="download" class="w-4 h-4"></i>
            <span>{{ __('whatsapp.export_excel') }}</span>
        </a>
    @endcan
    @can('whatsapp.manage')
        <button type="button" @click="openPruneModal = true"
            class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
            <i data-lucide="database-zap" class="w-4 h-4 text-[#FF9500]"></i>
            <span>{{ __('whatsapp.storage_pruning_btn') }}</span>
        </button>
    @endcan
</div>
```

**2. Desktop Table Eye Button Touch Target Fix (Lines 201–207)**
```html
<!-- BEFORE -->
<button type="button" @click.stop='inspectLog(@json($logJson))'
    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
    title="{{ __('whatsapp.view_detail_log') }}"
    aria-label="{{ __('whatsapp.view_detail_log') }}">
    <i data-lucide="eye" class="w-4 h-4"></i>
</button>

<!-- AFTER -->
<button type="button" @click.stop='inspectLog(@json($logJson))'
    class="min-w-[44px] min-h-[44px] w-9 h-9 rounded-[10px] flex items-center justify-center text-[#007AFF] bg-[#007AFF]/8 hover:bg-[#007AFF]/15 dark:hover:bg-[#007AFF]/25 transition-all cursor-pointer shrink-0"
    title="{{ __('whatsapp.view_detail_log') }}"
    aria-label="{{ __('whatsapp.view_detail_log') }}">
    <i data-lucide="eye" class="w-4 h-4"></i>
</button>
```

**3. Storage Pruning Modal Guard (Lines 418–471)**
```html
<!-- BEFORE -->
<template x-teleport="body">
    <div x-show="openPruneModal" x-cloak ...>
        ...
    </div>
</template>

<!-- AFTER -->
@can('whatsapp.manage')
<template x-teleport="body">
    <div x-show="openPruneModal" x-cloak ...>
        ...
    </div>
</template>
@endcan
```

---

## 5. Verification Method

1. **Accessibility and Touch Target Test**:
   ```powershell
   vendor\bin\phpunit --filter=test_whatsapp_views_satisfy_mobile_touch_targets_and_wcag_a11y
   ```
   *Pass Condition*: 1 test passed, 17 assertions. Confirms all `min-h-[44px]`, `min-w-[44px]`, `role="dialog"`, `aria-modal="true"`, and modal label attributes remain satisfied.

2. **Merchant WhatsApp Feature Test Suite**:
   ```powershell
   vendor\bin\phpunit --filter=MerchantWhatsAppWebFeatureTest
   ```
   *Pass Condition*: All 28 tests pass without regression.

3. **Blade Compilation & Syntax Verification**:
   ```powershell
   php artisan view:cache
   ```
   *Pass Condition*: Zero syntax errors during compilation of all Blade templates.

4. **RBAC Negative Authorization Test (Read-Only Staff Account)**:
   - Authenticate as a non-owner merchant staff member possessing only `whatsapp.view`.
   - Access `GET /whatsapp`: Disconnect button, Embedded Signup CTA, Save Receipt Settings button, Test Send button, and Template Blast buttons are completely hidden or disabled.
   - Access `GET /whatsapp/logs`: "Bersihkan Storage" button and pruning modal are completely absent.

5. **Invalidation Conditions**:
   - Any removal of `min-h-[44px]` or `min-w-[44px]` on interactive buttons will invalidate WCAG compliance and fail feature tests.
   - Using `@can` directives before bridging `Gate::before` to `Context::hasPermission` for tenant `User` will cause authorized merchants to erroneously see buttons hidden.
