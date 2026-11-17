import os

for folder in ["resources/views/app/finance", "resources/views/app/reports"]:
    print(f"\n=== {folder} ===")
    for root, dirs, files in os.walk(folder):
        for f in files:
            print(" ", os.path.relpath(os.path.join(root, f), folder).replace("\\", "/"))
