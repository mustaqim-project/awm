# Progress — Worker Milestone 2 (UI Layout & Simplification)

Last visited: 2026-11-08T19:44:00Z
- [x] Fix floating calculation bar offset in resources/views/app/hrm/payroll/create.blade.php (`bottom-[calc(68px+env(safe-area-inset-bottom))] lg:bottom-4`)
- [x] Standardize pb-28 lg:pb-12 safe-area padding in inventory/stocks, opname, transfers (matching movements.blade.php)
- [x] Upgrade table actions in stocks, opname, and transfers to w-10 h-10 min-w-[40px] min-h-[40px] sm:w-9 sm:h-9
- [x] Eliminate duplicate CTA buttons in hrm/partials/tab-corrections and tab-locations card headers
- [x] Restyle filter submit button to neutral/tonal in tab-schedules.blade.php
- [x] Remove pt-5 and align filter form inputs/button in tab-attendance.blade.php
- [x] Upgrade action buttons to min-h-[44px] h-11 px-4 in hrm/payroll/payslip.blade.php
- [x] Wrap sensitive action buttons with Context::hasPermission(...) view guards across HRM tabs (hrm/index.blade.php, tab-corrections, tab-locations, tab-schedules)
- [x] Wrap sensitive action buttons with Context::hasPermission('inventory.manage') in stocks, opname, and transfers
- [x] Upgrade sub-44px touch targets across 28 buttons in Domain Cards (h-7/h-8 -> min-h-[40px] sm:min-h-[32px] px-3.5) and segmented tabs (min-h-[44px] sm:min-h-[36px]) in import/index.blade.php
- [x] Add reactive filtered row getters and empty state rows in all 4 import preview tables (Materials, Products, Recipes, Inventory)
- [x] Wrap execute import buttons in import/index.blade.php with Context::hasPermission(...) and upgrade touch targets to min-h-[44px] sm:min-h-[36px]
- [x] Run php -l syntax check on all 11 modified files (100% clean)
- [x] Run php artisan view:cache verification (100% clean compilation)
- [x] Write handoff.md and report completion to parent orchestrator
