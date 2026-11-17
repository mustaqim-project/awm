# scripts/update_sitemap_40.py
import json
import os
import subprocess
import shutil
import sys

def main():
    research_file = os.path.join("database", "data", "articles", "keyword-research-2026-11-17.json")
    with open(research_file, "r", encoding="utf-8") as f:
        data = json.load(f)

    topics = data["topics"]
    sitemap_path = os.path.join("public", "sitemap_blog.xml")

    with open(sitemap_path, "r", encoding="utf-8") as f:
        content = f.read()

    new_entries = []
    for t in topics:
        slug = t["slug"]
        pub_date = t.get("published_at", "2026-11-17T09:00:00+07:00")
        loc = f"https://cooca.id/blog/{slug}"
        if loc in content:
            continue
        entry = f"""    <url>
        <loc>{loc}</loc>
        <lastmod>{pub_date}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>"""
        new_entries.append(entry)

    if new_entries:
        marker = "</url>"
        pos = content.find(marker)
        if pos != -1:
            split_pos = pos + len(marker)
            new_block = "\n" + "\n".join(new_entries)
            updated = content[:split_pos] + new_block + content[split_pos:]
            with open(sitemap_path, "w", encoding="utf-8") as f:
                f.write(updated)
            print(f"[OK] Added {len(new_entries)} URLs to {sitemap_path}")
        else:
            print("[WARN] Marker </url> not found in sitemap")
    else:
        print("[INFO] All URLs already present in sitemap")

    fetch_cmd = [
        sys.executable,
        os.path.join("scripts", "fetch_blog_links.py"),
        "--from-file", sitemap_path,
        "--out", "blog_links.json"
    ]
    res = subprocess.run(fetch_cmd, capture_output=True, text=True, encoding="utf-8")
    if res.returncode == 0:
        print("[PASS] Refreshed blog_links.json")
        db_links = os.path.join("database", "data", "blog_links.json")
        shutil.copy("blog_links.json", db_links)
        print(f"[PASS] Copied blog_links.json to {db_links}")
    else:
        print(f"[FAIL] Error refreshing blog_links.json: {res.stderr}")

if __name__ == "__main__":
    main()
