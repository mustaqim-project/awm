# scripts/build_and_validate_40_evergreen.py
# -*- coding: utf-8 -*-
import os
import sys
import json
import re
import subprocess

# Ensure scripts dir is on sys.path
sys.path.insert(0, os.path.dirname(__file__))

from batch_evergreen_common import make_json_ld
from generate_40_evergreen_p1 import get_articles_p1
from generate_40_evergreen_p2 import get_articles_p2
from generate_40_evergreen_p3 import get_articles_p3
from generate_40_evergreen_p4 import get_articles_p4
from generate_40_evergreen_p5 import get_articles_p5
from harmonize_40_articles import harmonize_all

def main():
    arts1 = get_articles_p1()
    arts2 = get_articles_p2()
    arts3 = get_articles_p3()
    arts4 = get_articles_p4()
    arts5 = get_articles_p5()

    raw_articles = arts1 + arts2 + arts3 + arts4 + arts5
    articles = harmonize_all(raw_articles)
    total = len(articles)
    print(f"Loaded {total} evergreen articles (P1: {len(arts1)}, P2: {len(arts2)}, P3: {len(arts3)}, P4: {len(arts4)}, P5: {len(arts5)})")

    assert total == 40, f"Expected exactly 40 articles, got {total}"

    out_dir = os.path.join("database", "data", "articles")
    os.makedirs(out_dir, exist_ok=True)

    links_path = os.path.join("database", "data", "blog_links.json")
    if not os.path.exists(links_path):
        links_path = "blog_links.json"

    progress_summary = []
    failed = False

    for idx, art in enumerate(articles, start=1):
        art_id = art["id"]
        slug = art["slug"]
        pillar = art.get("pillar", "")
        md_filename = f"{slug}.md"
        json_filename = f"{slug}.json"
        md_path = os.path.join(out_dir, md_filename)
        json_path = os.path.join(out_dir, json_filename)

        # 1. Check dashes in metadata and body
        for field in ["title", "meta_title", "meta_description", "body", "primary_keyword"]:
            val = art.get(field, "")
            if "\u2014" in val or "\u2013" in val:
                print(f"[FAIL] Article {art_id} ({slug}) contains em/en dash in field '{field}'!")
                failed = True

        # 2. Check body word count
        words = len(re.findall(r"\w+", art["body"].strip()))
        if words < 1500:
            print(f"[FAIL] Article {art_id} ({slug}) has only {words} words (min 1500)!")
            failed = True

        # 3. Build JSON-LD & FAQ
        json_ld, faqs = make_json_ld(art)

        # 4. Build Markdown content
        md_content = f"""---
title: "{art['title']}"
meta_title: "{art['meta_title']}"
meta_description: "{art['meta_description']}"
slug: "{art['slug']}"
primary_keyword: "{art['primary_keyword']}"
secondary_keywords: "{art['secondary_keywords']}"
search_intent: "{art['search_intent']}"
category: "{art['category']}"
reading_time: "{art['reading_time']}"
cover_image: "{art['cover_image']}"
published_at: "{art['published_at']}"
---
{art['body'].strip()}

```json
{json_ld}
```
"""
        # Ensure no dashes in final markdown
        if "\u2014" in md_content or "\u2013" in md_content:
            print(f"[FAIL] Article {art_id} contains em/en dash in final markdown string!")
            failed = True

        with open(md_path, "w", encoding="utf-8") as f:
            f.write(md_content)

        # 5. Build JSON file
        art_json = {
            "id": art_id,
            "pillar": pillar,
            "title": art["title"],
            "slug": slug,
            "meta_title": art["meta_title"],
            "meta_description": art["meta_description"],
            "primary_keyword": art["primary_keyword"],
            "secondary_keywords": art["secondary_keywords"],
            "search_intent": art["search_intent"],
            "category": art["category"],
            "reading_time": art["reading_time"],
            "cover_image": art["cover_image"],
            "published_at": art["published_at"],
            "word_count": words,
            "faqs": faqs,
            "schema_org": json.loads(json_ld),
            "content_markdown": md_content
        }

        with open(json_path, "w", encoding="utf-8") as f:
            json.dump(art_json, f, indent=2, ensure_ascii=False)

        # 6. Run lint_content.py
        lint_cmd = [
            sys.executable,
            os.path.join("scripts", "lint_content.py"),
            md_path,
            "--links", links_path
        ]
        res = subprocess.run(lint_cmd, capture_output=True, text=True, encoding="utf-8")
        if res.returncode != 0:
            print(f"[FAIL] Article {art_id} ({slug}) failed linting:\n{res.stdout}\n{res.stderr}")
            failed = True
            lint_status = "FAIL"
        else:
            print(f"[{idx:02d}/40] [PASS] ID {art_id:02d} | {slug} | Words: {words} | 0 em/en | Lint 100% OK")
            lint_status = "PASS"

        progress_summary.append({
            "id": art_id,
            "pillar": pillar,
            "title": art["title"],
            "slug": slug,
            "published_at": art["published_at"],
            "cover_image": art["cover_image"],
            "word_count": words,
            "lint_status": lint_status
        })

    # Save progress file
    progress_file = os.path.join("database", "data", "articles", ".progress-2026-11-17.json")
    with open(progress_file, "w", encoding="utf-8") as f:
        json.dump({
            "date": "2026-11-17",
            "pipeline": "DAILY EVERGREEN SEO PIPELINE COOCA (40 ARTIKEL EDUKASI UMKM PER HARI)",
            "total_articles": len(progress_summary),
            "status": "COMPLETED" if not failed else "FAILED",
            "summary": progress_summary
        }, f, indent=2, ensure_ascii=False)
    print(f"\n[INFO] Progress saved to {progress_file}")

    if failed:
        print("\n[ERROR] Pipeline completed with ERRORS. See above.")
        sys.exit(1)
    else:
        print(f"\n[SUCCESS] ALL 40 EVERGREEN ARTICLES BUILT, LINTED, AND VALIDATED 100% WITH ZERO FAILS!")

if __name__ == "__main__":
    main()
