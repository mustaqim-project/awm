import os, glob

base = r'c:\laragon\www\cooca_core\resources\views\app'
all_files = glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True)
print("Total blade files found in app:", len(all_files))

submodules = {}
for f in all_files:
    rel = os.path.relpath(f, base)
    parts = rel.split(os.sep)
    if len(parts) == 1:
        mod = 'root'
    else:
        mod = parts[0]
    if mod not in submodules:
        submodules[mod] = []
    submodules[mod].append((rel, sum(1 for _ in open(f, 'r', encoding='utf-8', errors='ignore'))))

print("Total submodules:", len(submodules))
for mod, files in sorted(submodules.items(), key=lambda x: len(x[1]), reverse=True):
    tot_lines = sum(x[1] for x in files)
    print(f"{mod:20}: {len(files):2} files | {tot_lines:6,} lines")
