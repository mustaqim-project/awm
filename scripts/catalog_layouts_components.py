import os

BASE_DIR = r"C:\laragon\www\cooca_core"

def scan_dir(subpath):
    d = os.path.join(BASE_DIR, subpath)
    res = []
    for root, dirs, files in os.walk(d):
        for f in files:
            if f.endswith(".blade.php"):
                p = os.path.join(root, f)
                rel = os.path.relpath(p, BASE_DIR).replace("\\", "/")
                with open(p, "r", encoding="utf-8", errors="ignore") as fp:
                    lines = len(fp.readlines())
                res.append((rel, lines))
    return sorted(res)

print("--- Layouts ---")
for p, l in scan_dir("resources/views/layouts"):
    print(f"  {p:<60}: {l:>4} lines")

print("\n--- Components ---")
for p, l in scan_dir("resources/views/components"):
    print(f"  {p:<60}: {l:>4} lines")
