import os
import glob

BASE_DIR = r"C:\laragon\www\cooca_core"
app_views_dir = os.path.join(BASE_DIR, "resources", "views", "app")

blade_files = []
for root, dirs, files in os.walk(app_views_dir):
    for f in files:
        if f.endswith(".blade.php"):
            full_path = os.path.join(root, f)
            rel_path = os.path.relpath(full_path, BASE_DIR).replace("\\", "/")
            lines = 0
            with open(full_path, "r", encoding="utf-8", errors="ignore") as fp:
                lines = len(fp.readlines())
            blade_files.append((rel_path, lines))

blade_files.sort(key=lambda x: x[0])
print(f"Total Blade files in resources/views/app: {len(blade_files)}")

# Group by subfolder
folders = {}
for path, lines in blade_files:
    parts = path.replace("resources/views/app/", "").split("/")
    folder = parts[0] if len(parts) > 1 else "_root"
    folders[folder] = folders.get(folder, 0) + 1

print("\nBlade files count by module folder under resources/views/app:")
for folder, count in sorted(folders.items()):
    print(f"  {folder:<25}: {count:>3} files")
