import os
import re
import json

base_dir = r"c:\laragon\www\cooca_core\resources\views\app"
target_modules = [
    'pos', 'sales', 'sales-orders', 'products', 'inventory', 
    'purchasing', 'purchase-orders', 'suppliers', 'invoices', 
    'quotations', 'warehouse', 'materials', 'storefront', 'marketplace', 'customers'
]

results = {}

# Patterns to search for
RE_MODAL = re.compile(r'(?:x-show|id=["\'][^"\']*modal[^"\']*["\']|class=["\'][^"\']*modal[^"\']*["\'])', re.IGNORECASE)
RE_MODAL_WIDTH = re.compile(r'max-w-(xs|sm|md|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|screen\S*)')
RE_BACKDROP_WRONG = re.compile(r'fixed\s+inset-0[^>]*flex[^>]*bg-black/(?:[0-9]+)')
RE_STANDALONE_BACKDROP = re.compile(r'<div[^>]*fixed\s+inset-0[^>]*bg-black/(?:50|60|70|75|80)[^>]*></div>|<div[^>]*fixed\s+inset-0[^>]*backdrop-blur')

RE_PLUS_DUPLICATION = re.compile(r'(?:<i[^>]*lucide-plus[^>]*>|data-lucide=["\']plus["\'])[^<]*\+|(?:>\s*\+\s*<[^>]*lucide-plus)', re.IGNORECASE)
RE_DOUBLE_PLUS_LABEL = re.compile(r'(?:data-lucide=["\']plus["\'][^>]*>\s*<\/i>\s*\+\s*Tambah|\+\s*Tambah)', re.IGNORECASE)

RE_TABLE = re.compile(r'<table\b', re.IGNORECASE)
RE_TABLE_OVERFLOW = re.compile(r'overflow-x-(auto|scroll)', re.IGNORECASE)
RE_TH_COUNT = re.compile(r'<th\b', re.IGNORECASE)

RE_TAP_TARGET_SMALL = re.compile(r'\b(h-6|h-7|h-8|p-1|py-0\.5|py-1|px-2)\b(?![^<]*\bsm:min-h-)')
RE_TOUCH_44 = re.compile(r'\b(min-h-\[44px\]|min-h-\[48px\]|h-11|h-12|h-14)\b')

RE_TABULAR_NUMS = re.compile(r'tabular-nums')
RE_SNAP_SLIDER = re.compile(r'snap-x\s+snap-mandatory')

RE_FIXED_BOTTOM = re.compile(r'(fixed\s+inset-x-0\s+bottom-0|fixed\s+bottom-0|sticky\s+bottom-0)')
RE_INPUT_SMALL_FONT = re.compile(r'<input[^>]*class=["\'][^"\']*\btext-xs\b', re.IGNORECASE)

for mod in target_modules:
    mod_path = os.path.join(base_dir, mod)
    if not os.path.exists(mod_path):
        continue
    
    for root, dirs, files in os.walk(mod_path):
        for file in files:
            if not file.endswith('.blade.php'):
                continue
            full_path = os.path.join(root, file)
            rel_path = os.path.relpath(full_path, base_dir)
            
            with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
                lines = content.splitlines()
            
            file_info = {
                'rel_path': rel_path,
                'lines': len(lines),
                'size': len(content),
                'modals': [],
                'narrow_modals': [],
                'wrong_backdrops': len(RE_BACKDROP_WRONG.findall(content)),
                'standalone_backdrops': len(RE_STANDALONE_BACKDROP.findall(content)),
                'tables': len(RE_TABLE.findall(content)),
                'table_overflow': len(RE_TABLE_OVERFLOW.findall(content)),
                'plus_duplications': len(RE_PLUS_DUPLICATION.findall(content)) + len(RE_DOUBLE_PLUS_LABEL.findall(content)),
                'has_snap_slider': bool(RE_SNAP_SLIDER.search(content)),
                'has_fixed_bottom': bool(RE_FIXED_BOTTOM.search(content)),
                'has_tabular_nums': bool(RE_TABULAR_NUMS.search(content)),
                'input_small_fonts': len(RE_INPUT_SMALL_FONT.findall(content)),
                'touch_target_44_count': len(RE_TOUCH_44.findall(content)),
                'alpine_x_data': content.count('x-data'),
            }
            
            # Find modal definitions and widths
            for match in re.finditer(r'(<(?:div|section)[^>]*x-(?:show|if)=["\'][^"\']*modal[^"\']*["\'][^>]*>|(?:id|class)=["\'][^"\']*modal[^"\']*["\'][^>]*>)', content, re.IGNORECASE):
                snippet = content[max(0, match.start()-100):min(len(content), match.end()+500)]
                widths = RE_MODAL_WIDTH.findall(snippet)
                file_info['modals'].append({'match': match.group(0)[:80], 'widths': widths})
                for w in widths:
                    if w in ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl']:
                        file_info['narrow_modals'].append(w)
            
            # General modal width check in file
            all_widths = RE_MODAL_WIDTH.findall(content)
            narrow_all = [w for w in all_widths if w in ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl']]
            file_info['all_modal_widths'] = all_widths
            file_info['narrow_modal_classes'] = narrow_all
            
            results[rel_path] = file_info

output_file = r"c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\scan_results.json"
with open(output_file, 'w', encoding='utf-8') as f:
    json.dump(results, f, indent=2)

print(f"Scanned {len(results)} files. Results written to {output_file}")
