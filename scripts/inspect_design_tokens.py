import os

BASE_DIR = r"C:\laragon\www\cooca_core"

def preview_file(rel_path, max_lines=60):
    full_path = os.path.join(BASE_DIR, rel_path)
    if not os.path.exists(full_path):
        print(f"File not found: {rel_path}")
        return
    print(f"\n=== {rel_path} ===")
    with open(full_path, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()
        print(f"Total lines: {len(lines)}")
        for l in lines[:max_lines]:
            print(l.rstrip())

preview_file("resources/views/layouts/partials/typography.blade.php")
preview_file("resources/views/layouts/partials/sidebar-styles.blade.php", 40)
