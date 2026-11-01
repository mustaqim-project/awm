import os
import sys

# Ensure UTF-8
sys.stdout.reconfigure(encoding='utf-8')

print("Starting Monolith Modularization...")

os.makedirs('resources/views/app/pos/modals', exist_ok=True)
os.makedirs('resources/views/app/hrm/partials', exist_ok=True)
os.makedirs('resources/views/app/hrm/modals', exist_ok=True)

# ==============================================================================
# 1. POS TERMINAL MODULARIZATION
# ==============================================================================
with open('resources/views/app/pos/terminal.blade.php', 'r', encoding='utf-8') as f:
    pos_lines = f.readlines()

pos_markers = [
    ('customer-modal', '3. MODAL: QUICK CUSTOMER'),
    ('payment-modal', '4. MODAL: PAYMENT MODAL'),
    ('qris-modal', '4.1. MODAL: DYNAMIC QRIS'),
    ('success-modal', '5. MODAL: PAYMENT SUCCESS'),
    ('settings-modal', '5B. MODAL: POS SETTINGS'),
    ('open-shift-modal', '6. MODAL: OPEN SHIFT'),
    ('close-shift-modal', '7. MODAL: CLOSE SHIFT'),
    ('held-orders-modal', '8. MODAL: HELD ORDERS QUEUE'),
    ('hold-prompt-modal', '9. MODAL: HOLD CART PROMPT'),
    ('cash-movement-modal', '10. MODAL: CASH MOVEMENT'),
    ('supervisor-pin-modal', '10B. MODAL: SUPERVISOR PIN'),
    ('scanner-permission-modal', '11. MODAL: CAMERA PERMISSION'),
    ('barcode-scanner-modal', '12. MODAL: CAMERA BARCODE SCANNER'),
    ('modifier-modal', 'MODAL: CASHIER PRODUCT MODIFIER'),
    ('service-vertical-modal', 'MODAL: DATA LAYANAN INDUSTRI'),
    ('item-detail-modal', 'MODAL: DETAIL ITEM & APOTEK'),
    ('incoming-orders-modal', 'MODAL: INCOMING QR TABLE ORDERS'),
    ('reject-reason-modal', 'MODAL: REJECT REASON PROMPT'),
    ('tables-modal', 'MODAL: RESTAURANT TABLES & STOREFRONT'),
    ('table-payment-modal', 'MODAL: PAY TABLE QR ORDER'),
]

pos_spans = []
for idx, (name, marker) in enumerate(pos_markers):
    m_idx = next(i for i, l in enumerate(pos_lines) if marker in l)
    start = m_idx
    while start > 0 and '<!-- ===' in pos_lines[start-1]:
        start -= 1
    
    if idx + 1 < len(pos_markers):
        next_name, next_marker = pos_markers[idx+1]
        next_m_idx = next(i for i, l in enumerate(pos_lines) if next_marker in l)
        next_start = next_m_idx
        while next_start > 0 and '<!-- ===' in pos_lines[next_start-1]:
            next_start -= 1
        end = next_start
        while end > start and (pos_lines[end-1].strip() == '' or pos_lines[end-1].strip().startswith('@if') or pos_lines[end-1].strip().startswith('@endif')):
            end -= 1
    else:
        end = next(i for i in range(start, len(pos_lines)) if pos_lines[i].strip() == '@endif')

    pos_spans.append((name, start, end))

for name, s, e in pos_spans:
    target_path = os.path.join('resources/views/app/pos/modals', f'{name}.blade.php')
    with open(target_path, 'w', encoding='utf-8') as out:
        out.write(''.join(pos_lines[s:e]).strip() + '\n')
    print(f'Wrote {target_path} ({e-s} lines)')

# Add aliases for explorer_rem_3 naming conventions so both conventions work seamlessly
pos_aliases = {
    'quick-customer': 'customer-modal',
    'payment': 'payment-modal',
    'dynamic-qris': 'qris-modal',
    'receipt-success': 'success-modal',
    'settings-hardware': 'settings-modal',
    'open-shift': 'open-shift-modal',
    'close-shift': 'close-shift-modal',
    'held-orders': 'held-orders-modal',
    'hold-prompt': 'hold-prompt-modal',
    'cash-movement': 'cash-movement-modal',
    'supervisor-pin': 'supervisor-pin-modal',
    'camera-permission': 'scanner-permission-modal',
    'barcode-scanner': 'barcode-scanner-modal',
    'modifier-picker': 'modifier-modal',
    'service-vertical': 'service-vertical-modal',
    'pharmacy-batch': 'item-detail-modal',
    'incoming-orders': 'incoming-orders-modal',
    'reject-reason': 'reject-reason-modal',
    'restaurant-tables': 'tables-modal',
    'pay-table-order': 'table-payment-modal',
}

for alias, target in pos_aliases.items():
    alias_path = os.path.join('resources/views/app/pos/modals', f'{alias}.blade.php')
    if not os.path.exists(alias_path):
        with open(alias_path, 'w', encoding='utf-8') as out:
            out.write(f"@include('app.pos.modals.{target}')\n")
        print(f'Created alias {alias_path} -> {target}')

# New POS terminal replacement content
pos_replacement = '''    {{-- ======================================================== --}}
    {{-- MODULAR POS TERMINAL MODALS (EXTRACTED)                  --}}
    {{-- ======================================================== --}}
    @include('app.pos.modals.customer-modal')
    @include('app.pos.modals.payment-modal')
    @include('app.pos.modals.qris-modal')
    @include('app.pos.modals.success-modal')
    @include('app.pos.modals.settings-modal')
    @include('app.pos.modals.open-shift-modal')
    @include('app.pos.modals.close-shift-modal')
    @include('app.pos.modals.held-orders-modal')
    @include('app.pos.modals.hold-prompt-modal')
    @include('app.pos.modals.cash-movement-modal')
    @include('app.pos.modals.supervisor-pin-modal')
    @include('app.pos.modals.scanner-permission-modal')
    @include('app.pos.modals.barcode-scanner-modal')
    @include('app.pos.modals.modifier-modal')

    @if ($isWorkshop || $isLaundry || (!$isFnB && !$isRetail && !$isPharmacy))
        @include('app.pos.modals.service-vertical-modal')
    @endif

    @include('app.pos.modals.item-detail-modal')

    @if ($business && $business->hasDineInFeature())
        @include('app.pos.modals.incoming-orders-modal')
        @include('app.pos.modals.reject-reason-modal')
        @include('app.pos.modals.tables-modal')
        @include('app.pos.modals.table-payment-modal')
    @endif
'''

pos_start_idx = pos_spans[0][1]
pos_end_idx = next(i for i in range(pos_spans[-1][2], len(pos_lines)) if pos_lines[i].strip() == '@endif') + 1

new_pos_content = ''.join(pos_lines[:pos_start_idx]) + pos_replacement + ''.join(pos_lines[pos_end_idx:])
with open('resources/views/app/pos/terminal.blade.php', 'w', encoding='utf-8') as out:
    out.write(new_pos_content)
print(f'Updated terminal.blade.php: lines {len(pos_lines)} -> {len(new_pos_content.splitlines())}')

# ==============================================================================
# 2. HRM INDEX MODULARIZATION
# ==============================================================================
with open('resources/views/app/hrm/index.blade.php', 'r', encoding='utf-8') as f:
    hrm_lines = f.readlines()

tab_markers = [
    ('tab-employees', 'TAB 1: KARYAWAN & PROFIL GAJI'),
    ('tab-attendance', 'TAB 2: PRESENSI WAJAH & REKAP HARIAN'),
    ('tab-corrections', 'TAB 3: PERSETUJUAN TIKET KOREKSI ABSENSI'),
    ('tab-locations', 'TAB 4: PENGATURAN LOKASI & GEOFENCE'),
    ('tab-shifts', 'TAB 4B: SHIFT KERJA KARYAWAN'),
    ('tab-schedules', 'TAB 4C: JADWAL & ROSTER KARYAWAN'),
    ('tab-payrolls', 'TAB 5: PENGGAJIAN BULANAN'),
    ('tab-loans', 'TAB 6: KASBON & PINJAMAN KARYAWAN'),
]

modal_markers = [
    ('register-face', 'MODAL 1: DAFTARKAN BIOMETRIK WAJAH'),
    ('face-attendance', 'MODAL 2: 1-DIRECTION FRONTAL BIOMETRIC'),
    ('add-location', 'MODAL 3: TAMBAH LOKASI KANTOR'),
    ('edit-location', 'MODAL 4: EDIT LOKASI KANTOR'),
    ('add-employee', 'MODAL 5: TAMBAH KARYAWAN BARU'),
    ('edit-employee', 'MODAL 6: EDIT PROFIL HRM KARYAWAN'),
    ('add-loan', 'MODAL 7: CATAT KASBON BARU'),
    ('add-correction', 'MODAL 8: AJUKAN TIKET PERBAIKAN'),
    ('review-correction', 'MODAL 9: REVIEW PERSUTUJUAN TIKET'),
    ('add-shift', 'MODAL SHIFT 1: TAMBAH SHIFT KERJA'),
    ('edit-shift', 'MODAL SHIFT 2: EDIT SHIFT KERJA'),
    ('add-schedule', 'MODAL ROSTER 1: TAMBAH JADWAL'),
    ('edit-schedule', 'MODAL ROSTER 2: EDIT JADWAL'),
    ('import-employee', 'MODAL IMPORT KARYAWAN MASSAL'),
    ('import-attendance', 'MODAL IMPORT PRESENSI MESIN'),
]

tab_spans = []
for idx, (name, marker) in enumerate(tab_markers):
    m_idx = next(i for i, l in enumerate(hrm_lines) if marker in l)
    start = m_idx
    while start > 0 and '<!-- ===' in hrm_lines[start-1]:
        start -= 1
    
    if idx + 1 < len(tab_markers):
        next_name, next_marker = tab_markers[idx+1]
        next_m_idx = next(i for i, l in enumerate(hrm_lines) if next_marker in l)
        next_start = next_m_idx
        while next_start > 0 and '<!-- ===' in hrm_lines[next_start-1]:
            next_start -= 1
        end = next_start
        while end > start and hrm_lines[end-1].strip() == '':
            end -= 1
    else:
        m1_idx = next(i for i, l in enumerate(hrm_lines) if modal_markers[0][1] in l)
        end = m1_idx
        while end > 0 and '<!-- ===' in hrm_lines[end-1]:
            end -= 1
        while end > start and hrm_lines[end-1].strip() == '':
            end -= 1

    tab_spans.append((name, start, end))

for name, s, e in tab_spans:
    target_path = os.path.join('resources/views/app/hrm/partials', f'{name}.blade.php')
    with open(target_path, 'w', encoding='utf-8') as out:
        out.write(''.join(hrm_lines[s:e]).strip() + '\n')
    print(f'Wrote {target_path} ({e-s} lines)')

modal_spans = []
for idx, (name, marker) in enumerate(modal_markers):
    m_idx = next(i for i, l in enumerate(hrm_lines) if marker in l)
    start = m_idx
    while start > 0 and '<!-- ===' in hrm_lines[start-1]:
        start -= 1
    
    if idx + 1 < len(modal_markers):
        next_name, next_marker = modal_markers[idx+1]
        next_m_idx = next(i for i, l in enumerate(hrm_lines) if next_marker in l)
        next_start = next_m_idx
        while next_start > 0 and '<!-- ===' in hrm_lines[next_start-1]:
            next_start -= 1
        end = next_start
        while end > start and hrm_lines[end-1].strip() == '':
            end -= 1
    else:
        end = len(hrm_lines) - 2
        while end > start and hrm_lines[end-1].strip() == '':
            end -= 1

    modal_spans.append((name, start, end))

for name, s, e in modal_spans:
    target_path = os.path.join('resources/views/app/hrm/modals', f'{name}.blade.php')
    with open(target_path, 'w', encoding='utf-8') as out:
        out.write(''.join(hrm_lines[s:e]).strip() + '\n')
    print(f'Wrote {target_path} ({e-s} lines)')

# Build new hrm/index.blade.php content
# 1. Start up to tab_spans[0][1]
# 2. Tab includes
# 3. Modal includes
# 4. Closing </div> and @endsection

tabs_replacement = '''    {{-- ======================================================== --}}
    {{-- 3. TAB SUB-PANELS (MODULARIZED)                          --}}
    {{-- ======================================================== --}}
    @include('app.hrm.partials.tab-employees')
    @include('app.hrm.partials.tab-attendance')
    @include('app.hrm.partials.tab-corrections')
    @include('app.hrm.partials.tab-locations')
    @include('app.hrm.partials.tab-shifts')
    @include('app.hrm.partials.tab-schedules')
    @include('app.hrm.partials.tab-payrolls')
    @include('app.hrm.partials.tab-loans')
'''

modals_replacement = '''
    {{-- ======================================================== --}}
    {{-- 4. TELEPORTED MODAL DIALOGS (MODULARIZED)                --}}
    {{-- ======================================================== --}}
    @include('app.hrm.modals.register-face')
    @include('app.hrm.modals.face-attendance')
    @include('app.hrm.modals.add-location')
    @include('app.hrm.modals.edit-location')
    @include('app.hrm.modals.add-employee')
    @include('app.hrm.modals.edit-employee')
    @include('app.hrm.modals.add-loan')
    @include('app.hrm.modals.add-correction')
    @include('app.hrm.modals.review-correction')
    @include('app.hrm.modals.add-shift')
    @include('app.hrm.modals.edit-shift')
    @include('app.hrm.modals.add-schedule')
    @include('app.hrm.modals.edit-schedule')
    @include('app.hrm.modals.import-employee')
    @include('app.hrm.modals.import-attendance')
'''

hrm_tab_start = tab_spans[0][1]
hrm_modal_end = modal_spans[-1][2]

new_hrm_content = (
    ''.join(hrm_lines[:hrm_tab_start]) +
    tabs_replacement +
    modals_replacement +
    '\n' +
    ''.join(hrm_lines[hrm_modal_end:])
)

with open('resources/views/app/hrm/index.blade.php', 'w', encoding='utf-8') as out:
    out.write(new_hrm_content)
print(f'Updated hrm/index.blade.php: lines {len(hrm_lines)} -> {len(new_hrm_content.splitlines())}')
print("Modularization complete!")
