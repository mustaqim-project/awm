import os
import json

base_dir = r"c:\laragon\www\cooca_core\resources\views\app"

with open(r"c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\scan_results.json", "r") as f:
    scan = json.load(f)

modules = {}
for path, info in scan.items():
    mod = path.split(os.sep)[0]
    modules.setdefault(mod, []).append((path, info))

print("| Modul | File Count | Total Baris | Mega-Files (>1000 baris) | Narrow Modals | Flawed Backdrops |")
print("|---|---|---|---|---|---|")

grand_files = 0
grand_lines = 0
grand_megas = 0
grand_narrow = 0
grand_backdrops = 0

for mod, files in sorted(modules.items(), key=lambda x: -sum(f[1]['lines'] for f in x[1])):
    f_count = len(files)
    l_count = sum(f[1]['lines'] for f in files)
    megas = [f[0] for f in files if f[1]['lines'] > 1000]
    narrow = sum(len(f[1]['narrow_modal_classes']) for f in files)
    backdrops = sum(f[1]['wrong_backdrops'] for f in files)
    
    grand_files += f_count
    grand_lines += l_count
    grand_megas += len(megas)
    grand_narrow += narrow
    grand_backdrops += backdrops
    
    mega_str = f"{len(megas)} ({', '.join([os.path.basename(m) for m in megas])})" if megas else "0"
    print(f"| `{mod}` | {f_count} | {l_count:,} | {mega_str} | {narrow} | {backdrops} |")

print(f"| **TOTAL** | **{grand_files}** | **{grand_lines:,}** | **{grand_megas} mega-files** | **{grand_narrow}** | **{grand_backdrops}** |")
