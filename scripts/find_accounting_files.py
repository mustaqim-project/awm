import os

BASE_DIR = r"C:\laragon\www\cooca_core\resources\views\app"

print("Searching for journal / income / profit files...")
for root, dirs, files in os.walk(BASE_DIR):
    for f in files:
        if any(k in f.lower() for k in ["journal", "income", "profit", "laba", "rugi", "report"]):
            print(" ", os.path.relpath(os.path.join(root, f), BASE_DIR).replace("\\", "/"))
