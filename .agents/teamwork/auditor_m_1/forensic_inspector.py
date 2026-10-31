import os
import re
import glob

REPO_ROOT = r"c:\laragon\www\cooca_core"

def check_language_files():
    id_files = glob.glob(os.path.join(REPO_ROOT, "lang", "id", "*.php"))
    en_files = glob.glob(os.path.join(REPO_ROOT, "lang", "en", "*.php"))
    
    print(f"Checking {len(id_files)} ID files and {len(en_files)} EN files...")
    
    empty_values = []
    dummy_values = []
    untranslated_long_strings = []
    
    # Suspicious placeholder patterns
    dummy_patterns = [
        re.compile(r'^\s*(test|todo|lorem|ipsum|asdf|placeholder|xxx|sample|dummy|stub)\s*$', re.I),
        re.compile(r'^\s*TBD\s*$', re.I),
    ]
    
    for fpath in id_files + en_files:
        rel = os.path.relpath(fpath, REPO_ROOT)
        with open(fpath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            
        # extract key => value pairs
        matches = re.findall(r"['\"]([^'\"]+)['\"]\s*=>\s*['\"]([^'\"]*)['\"]", content)
        for k, v in matches:
            if v == "" or v.isspace():
                empty_values.append((rel, k, v))
            for pat in dummy_patterns:
                if pat.match(v):
                    dummy_values.append((rel, k, v))
                    
    print(f"Empty values found: {len(empty_values)}")
    for e in empty_values[:10]:
        print(f"  Empty: {e[0]} -> {e[1]}")
        
    print(f"Dummy/placeholder values found: {len(dummy_values)}")
    for d in dummy_values[:10]:
        print(f"  Dummy: {d[0]} -> {d[1]} = '{d[2]}'")
        
    # Check parity and translation comparison for key domains
    domains = ['admin', 'purchasing', 'inventory', 'pos', 'marketplace', 'sales', 'social_media', 'whatsapp']
    for dom in domains:
        id_path = os.path.join(REPO_ROOT, "lang", "id", f"{dom}.php")
        en_path = os.path.join(REPO_ROOT, "lang", "en", f"{dom}.php")
        if os.path.exists(id_path) and os.path.exists(en_path):
            with open(id_path, 'r', encoding='utf-8') as f:
                id_dict = dict(re.findall(r"['\"]([^'\"]+)['\"]\s*=>\s*['\"]([^'\"]*)['\"]", f.read()))
            with open(en_path, 'r', encoding='utf-8') as f:
                en_dict = dict(re.findall(r"['\"]([^'\"]+)['\"]\s*=>\s*['\"]([^'\"]*)['\"]", f.read()))
                
            # Sample check for English quality:
            identical_count = 0
            for k in id_dict:
                if k in en_dict:
                    val_id = id_dict[k]
                    val_en = en_dict[k]
                    if len(val_id) > 15 and val_id == val_en:
                        # Identical long strings might indicate untranslated copy-paste
                        # Unless it's technical terms, e.g. "API Key", "WhatsApp Business"
                        if not any(tech in val_id.lower() for tech in ['whatsapp', 'meta', 'token', 'url', 'id', 'sku', 'pos', 'pdf', 'qr']):
                            untranslated_long_strings.append((dom, k, val_id, val_en))
                            
    print(f"Potentially untranslated identical long strings: {len(untranslated_long_strings)}")
    for u in untranslated_long_strings[:10]:
        print(f"  Untranslated: {u[0]} -> {u[1]} ID='{u[2]}' EN='{u[3]}'")

def check_views_for_facades():
    print("\nChecking Blade views for empty __('') calls and bypass facades...")
    empty_i18n = []
    blade_files = glob.glob(os.path.join(REPO_ROOT, "resources", "views", "**", "*.blade.php"), recursive=True)
    
    for bf in blade_files:
        rel = os.path.relpath(bf, REPO_ROOT)
        with open(bf, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            
        # Check for empty __('') or __(' ')
        if re.search(r"__\(\s*['\"]\s*['\"]\s*\)", content):
            empty_i18n.append(rel)
            
    print(f"Blade views with empty __('') calls: {len(empty_i18n)}")
    for e in empty_i18n:
        print(f"  Empty i18n in: {e}")

if __name__ == '__main__':
    check_language_files()
    check_views_for_facades()
