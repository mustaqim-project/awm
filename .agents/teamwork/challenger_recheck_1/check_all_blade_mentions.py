import os
import re

REPO_DIR = r"c:\laragon\www\cooca_core"
APP_DIR = os.path.join(REPO_DIR, "resources", "views", "app")
REPORT_PATH = os.path.join(REPO_DIR, "docs", "UI_UX_AUDIT_REPORT.md")

with open(REPORT_PATH, "r", encoding="utf-8") as f:
    report_text = f.read()

# Find all occurrences of something ending in .blade.php
blade_mentions = re.findall(r'([a-zA-Z0-9_\-\.\/]+\.blade\.php)', report_text)
unique_mentions = sorted(set(blade_mentions))

print(f"Total unique .blade.php mentions in report: {len(unique_mentions)}")

missing_blade_mentions = []
existing_blade_mentions = []

for mention in unique_mentions:
    # normalize path
    norm = mention
    if norm.startswith("resources/views/app/"):
        norm = norm.replace("resources/views/app/", "")
    elif norm.startswith("resources/views/"):
        norm = norm.replace("resources/views/", "")
    elif norm.startswith("app/"):
        norm = norm.replace("app/", "")
        
    # Check if exists in resources/views/app/ or resources/views/
    path_in_app = os.path.join(APP_DIR, norm.replace("/", os.sep))
    path_in_views = os.path.join(REPO_DIR, "resources", "views", norm.replace("/", os.sep))
    
    if os.path.isfile(path_in_app):
        existing_blade_mentions.append((mention, "in app"))
    elif os.path.isfile(path_in_views):
        existing_blade_mentions.append((mention, "in views"))
    else:
        # Check if it's just a bare filename like "index.blade.php" mentioned in a submodule section
        missing_blade_mentions.append(mention)

print(f"Directly resolved .blade.php mentions: {len(existing_blade_mentions)}")
print(f"Unresolved mentions: {len(missing_blade_mentions)}")
for m in missing_blade_mentions:
    print(f"  Unresolved: {m}")
