import os
import json

BASE_DIR = r"C:\laragon\www\cooca_core"

# Check the corrected paths
corrected_paths = [
    ("pembukuan-jurnal-otomatis-double-entry.png", "resources/views/app/finance/journals.blade.php"),
    ("buku-besar-neraca-saldo.png", "resources/views/app/finance/accounting/general-ledger.blade.php"),
    ("laporan-laba-rugi-profit-loss.png", "resources/views/app/reports/index.blade.php"),
    ("toko-online-instan.png", "resources/views/app/storefront/settings.blade.php"),
    ("theme-branding-editor.png", "resources/views/app/storefront/theme_editor.blade.php"),
    ("mini-bio-link-landing-page-builder.png", "resources/views/app/landing_page/edit.blade.php"),
    ("struk-digital-kasir-whatsapp.png", "resources/views/app/whatsapp/index.blade.php"),
    ("program-loyalitas-tingkatan-member.png", "resources/views/app/crm/members.blade.php"),
    ("reward-poin-belanja.png", "resources/views/app/crm/members.blade.php"),
    ("kupon-voucher-diskon-promo.png", "resources/views/app/crm/vouchers.blade.php"),
    ("roster-shift-jadwal-kerja.png", "resources/views/app/hrm/partials/tab-shifts.blade.php"),
    ("kasbon-pinjaman-karyawan.png", "resources/views/app/hrm/partials/tab-loans.blade.php")
]

print("Verifying corrected paths:")
for fn, p in corrected_paths:
    full_p = os.path.join(BASE_DIR, p)
    exists = os.path.exists(full_p)
    lines = 0
    if exists:
        with open(full_p, "r", encoding="utf-8", errors="ignore") as fp:
            lines = len(fp.readlines())
    print(f"  {fn:<45}: Exists={exists!s:<5} ({lines} lines) -> {p}")
