import os
import re

REPO_DIR = r"c:\laragon\www\cooca_core"
APP_DIR = os.path.join(REPO_DIR, "resources", "views", "app")
REPORT_PATH = os.path.join(REPO_DIR, "docs", "UI_UX_AUDIT_REPORT.md")

with open(REPORT_PATH, "r", encoding="utf-8") as f:
    report_text = f.read()

# Match Section 4 headings: e.g. #### 1. AI Studio & Prompt Engineering (`resources/views/app/ai-studio`)
# or #### 18. Root Executive Dashboard & Calculator (resources/views/app)
sec4_matches = list(re.finditer(r'####\s+(\d+)\.\s+([^(\n]+)\(([^)\n]+)\)', report_text))
print(f"Found {len(sec4_matches)} Section 4 module sections.")

all_findings = []

for i, m in enumerate(sec4_matches):
    sec_num = m.group(1)
    sec_title = m.group(2).strip()
    sec_dir_raw = m.group(3).strip()
    
    # clean dir path
    sec_dir_clean = sec_dir_raw.replace('`', '').strip()
    if sec_dir_clean.startswith('resources/views/app/'):
        rel_mod_dir = sec_dir_clean.replace('resources/views/app/', '')
        mod_full_dir = os.path.join(APP_DIR, rel_mod_dir.replace('/', os.sep))
    elif sec_dir_clean == 'resources/views/app':
        rel_mod_dir = ''
        mod_full_dir = APP_DIR
    else:
        rel_mod_dir = sec_dir_clean
        mod_full_dir = os.path.join(REPO_DIR, sec_dir_clean.replace('/', os.sep))
        
    # Get the text chunk until the next section
    start_pos = m.end()
    end_pos = sec4_matches[i+1].start() if i+1 < len(sec4_matches) else len(report_text)
    chunk = report_text[start_pos:end_pos]
    
    # Find all bullet points listing views, e.g. `- **index.blade.php** (3,443 lines)`
    # or `- **bom.blade.php** (439 lines)`
    # or `- **accounting/coa.blade.php**`
    view_bullets = re.findall(r'[-*]\s+\*\*([a-zA-Z0-9_\-\.\/]+\.blade\.php)\*\*', chunk)
    
    for v in view_bullets:
        target_path = os.path.join(mod_full_dir, v.replace('/', os.sep))
        exists = os.path.isfile(target_path)
        all_findings.append({
            'sec': sec_num,
            'title': sec_title,
            'mod_dir': rel_mod_dir,
            'view': v,
            'exists': exists,
            'target_path': target_path
        })

print(f"Total views cataloged under Section 4 bullets: {len(all_findings)}")
missing = [f for f in all_findings if not f['exists']]
print(f"Total missing views in Section 4: {len(missing)}")
for m in missing:
    print(f"  MISSING: Section {m['sec']} ({m['title']}): {m['view']} at {m['target_path']}")

if len(missing) == 0:
    print(">>> SECTION 4 AUDIT: 100% PASS (All cataloged view files exist on disk!)")
