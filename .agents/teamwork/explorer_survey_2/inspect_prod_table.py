with open(r'c:\laragon\www\cooca_core\resources\views\app\products\index.blade.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print("Lines 910-960:")
for i in range(910, 960):
    print(f"{i+1}: {lines[i].strip()[:100]}")

print("\nMobile cards check (lines 1350-1420):")
for i in range(1350, 1420):
    print(f"{i+1}: {lines[i].strip()[:100]}")
