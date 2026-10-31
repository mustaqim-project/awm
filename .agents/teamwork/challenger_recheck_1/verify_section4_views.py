import os
import re

REPO_DIR = r"c:\laragon\www\cooca_core"
APP_DIR = os.path.join(REPO_DIR, "resources", "views", "app")
REPORT_PATH = os.path.join(REPO_DIR, "docs", "UI_UX_AUDIT_REPORT.md")

with open(REPORT_PATH, "r", encoding="utf-8") as f:
    report_text = f.read()

# Match Section 4 headings: e.g. #### 1. Point of Sale (`resources/views/app/pos`)
sec4_matches = list(re.finditer(r'####\s+(\d+)\.\s+([^\n(]+)\(([^)\n]+)\)', report_text))
print(f"Found {len(sec4_matches)} Section 4 module sections.")

all_views_checked = []
missing_views = []

for i, m in enumerate(sec4_matches):
    sec_num = m.group(1)
    sec_title = m.group(2).strip()
    sec_dir_raw = m.group(3).strip().replace('`', '')
    
    if sec_dir_raw.startswith('resources/views/app/'):
        rel_mod_dir = sec_dir_raw.replace('resources/views/app/', '')
        mod_full_dir = os.path.join(APP_DIR, rel_mod_dir.replace('/', os.sep))
    elif sec_dir_raw == 'resources/views/app':
        rel_mod_dir = ''
        mod_full_dir = APP_DIR
    else:
        rel_mod_dir = sec_dir_raw
        mod_full_dir = os.path.join(REPO_DIR, sec_dir_raw.replace('/', os.sep))
        
    start_pos = m.end()
    end_pos = sec4_matches[i+1].start() if i+1 < len(sec4_matches) else len(report_text)
    chunk = report_text[start_pos:end_pos]
    
    # Extract any `*.blade.php` in this chunk
    chunk_views = re.findall(r'`([a-zA-Z0-9_\-\.\/]+\.blade\.php)`', chunk)
    for cv in chunk_views:
        # If it has a full path or relative
        if cv.startswith('resources/views/app/'):
            clean_cv = cv.replace('resources/views/app/', '')
            target_path = os.path.join(APP_DIR, clean_cv.replace('/', os.sep))
        elif '/' in cv and os.path.isfile(os.path.join(APP_DIR, cv.replace('/', os.sep))):
            target_path = os.path.join(APP_DIR, cv.replace('/', os.sep))
        else:
            # relative to mod_full_dir
            target_path = os.path.join(mod_full_dir, cv.replace('/', os.sep))
            
        exists = os.path.isfile(target_path)
        all_views_checked.append((sec_num, sec_title, cv, target_path, exists))
        if not exists:
            missing_views.append((sec_num, sec_title, cv, target_path))

print(f"Total views evaluated across Section 4 chunks: {len(all_views_checked)}")
print(f"Missing views: {len(missing_views)}")
for sec_num, sec_title, cv, target_path in missing_views:
    print(f"  FAILED: Sec {sec_num} ({sec_title}): `{cv}` -> {target_path}")

if len(missing_views) == 0:
    print(">>> SECTION 4 ALL VIEWS VERIFIED: PASS (100% of mentioned views exist on disk)")
