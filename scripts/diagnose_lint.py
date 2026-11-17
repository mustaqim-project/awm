import os
import sys
import json
import subprocess

with open("database/data/articles/keyword-research-2026-11-17.json", "r", encoding="utf-8") as f:
    topics = json.load(f)["topics"]

print("Analyzing lint results for 40 articles...\n")
total_fails = 0
total_warns = 0

for t in topics:
    slug = t["slug"]
    path = os.path.join("database", "data", "articles", f"{slug}.md")
    if not os.path.exists(path):
        print(f"Missing file: {slug}.md")
        continue
    cmd = [sys.executable, "scripts/lint_content.py", path, "--links", "database/data/blog_links.json"]
    res = subprocess.run(cmd, capture_output=True, text=True, encoding="utf-8")
    fails = [l.strip() for l in res.stdout.splitlines() if "[FAIL]" in l]
    warns = [l.strip() for l in res.stdout.splitlines() if "[WARN]" in l]
    if fails or warns:
        total_fails += len(fails)
        total_warns += len(warns)
        print(f"ID {t['id']:02d} ({slug}):")
        for f in fails:
            print(f"   {f}")
        for w in warns:
            print(f"   {w}")

print(f"\nTotal FAIL items: {total_fails}, Total WARN items: {total_warns}")
