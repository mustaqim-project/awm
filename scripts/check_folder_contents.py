import os

folders = [
    "resources/views/app/finance/accounting",
    "resources/views/app/storefront",
    "resources/views/app/landing_page",
    "resources/views/app/whatsapp",
    "resources/views/app/crm",
    "resources/views/app/hrm"
]

for f in folders:
    print(f"\n=== {f} ===")
    if os.path.exists(f):
        for root, dirs, files in os.walk(f):
            for file in files:
                p = os.path.join(root, file).replace("\\", "/")
                print(" ", p)
    else:
        print("  FOLDER NOT FOUND!")
