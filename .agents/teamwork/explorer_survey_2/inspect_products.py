import re

with open(r'c:\laragon\www\cooca_core\resources\views\app\products\index.blade.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print("=== PRODUCTS INDEX SCAN ===")
for i, l in enumerate(lines):
    if any(k in l for k in ['x-show="showCreateModal', 'x-show="showEditModal', 'x-show="showCategoryModal', 'x-show="showImportModal', 'x-show="showExportModal', 'x-show="showBarcodeModal', 'x-show="showBatchModal', 'x-show="showPriceModal', 'x-show="showBranchStockModal', 'x-show="showUnitModal', 'x-show="showBrandModal']):
        print(f"Modal line {i+1}: {l.strip()[:100]}")
    if '<table' in l:
        print(f"Table line {i+1}: {l.strip()[:100]}")
    if re.search(r'class="[^"]*fixed\s+inset-0[^"]*"', l):
        print(f"Fixed overlay line {i+1}: {l.strip()[:100]}")
