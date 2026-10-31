import os

app_dir = r"c:\laragon\www\cooca_core\resources\views\app"
blade_files = []

for root, dirs, files in os.walk(app_dir):
    for f in files:
        if f.endswith(".blade.php"):
            blade_files.append(os.path.join(root, f))

print(f"Total Blade files found: {len(blade_files)}")

total_physical_lines = 0
total_non_empty_lines = 0

for file_path in blade_files:
    with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()
        total_physical_lines += len(lines)
        total_non_empty_lines += len([l for l in lines if l.strip()])

print(f"Total physical lines (with newlines): {total_physical_lines}")
print(f"Total non-empty lines: {total_non_empty_lines}")

# Check pos/terminal.blade.php
pos_terminal = os.path.join(app_dir, "pos", "terminal.blade.php")
if os.path.exists(pos_terminal):
    with open(pos_terminal, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()
        print(f"pos/terminal.blade.php physical lines: {len(lines)}")
        print(f"pos/terminal.blade.php size in bytes: {os.path.getsize(pos_terminal)}")

# Check products/index.blade.php
products_index = os.path.join(app_dir, "products", "index.blade.php")
if os.path.exists(products_index):
    with open(products_index, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()
        print(f"products/index.blade.php physical lines: {len(lines)}")
