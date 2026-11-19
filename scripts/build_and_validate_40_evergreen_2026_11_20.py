# scripts/build_and_validate_40_evergreen_2026_11_20.py
# -*- coding: utf-8 -*-
import os
import sys
import json
import re
import subprocess

# Ensure scripts dir and current dir are on sys.path
sys.path.insert(0, os.path.dirname(__file__))
sys.path.insert(0, os.path.abspath("."))

from batch_evergreen_common import make_json_ld
from generate_40_evergreen_2026_11_20_p1 import get_articles_p1
from generate_40_evergreen_2026_11_20_p2 import get_articles_p2
from generate_40_evergreen_2026_11_20_p3 import get_articles_p3
from generate_40_evergreen_2026_11_20_p4 import get_articles_p4
from generate_40_evergreen_2026_11_20_p5 import get_articles_p5
from harmonize_40_articles_2026_11_20 import harmonize_all

def main():
    arts1 = get_articles_p1()
    arts2 = get_articles_p2()
    arts3 = get_articles_p3()
    arts4 = get_articles_p4()
    arts5 = get_articles_p5()

    raw_articles = arts1 + arts2 + arts3 + arts4 + arts5
    articles = harmonize_all(raw_articles)
    total = len(articles)
    print(f"Loaded {total} evergreen articles for 2026-11-20 (P1: {len(arts1)}, P2: {len(arts2)}, P3: {len(arts3)}, P4: {len(arts4)}, P5: {len(arts5)})")

    assert total == 40, f"Expected exactly 40 articles, got {total}"

    out_dir = os.path.join("database", "data", "articles")
    os.makedirs(out_dir, exist_ok=True)

    links_path = os.path.join("database", "data", "blog_links.json")
    if not os.path.exists(links_path):
        links_path = "blog_links.json"

    # 1. Update blog_links.json FIRST so all 40 new URLs are recognized during linting
    if os.path.exists(links_path):
        with open(links_path, "r", encoding="utf-8") as f:
            links_data = json.load(f)
        
        existing_urls = {l["url"] for l in links_data.get("links", [])}
        links_added = 0
        for art in articles:
            url = f"https://cooca.id/blog/{art['slug']}"
            if url not in existing_urls:
                links_data.setdefault("links", []).append({
                    "url": url,
                    "slug": art["slug"],
                    "lastmod": art["published_at"],
                    "topic_from_slug": art["slug"].replace("-", " ")
                })
                existing_urls.add(url)
                links_added += 1
        links_data["count"] = len(links_data["links"])
        with open(links_path, "w", encoding="utf-8") as f:
            json.dump(links_data, f, indent=2, ensure_ascii=False)
        print(f"[INFO] blog_links.json pre-updated: {links_added} new URLs added (Total: {links_data['count']})")

    progress_summary = []
    failed = False
    fail_details = []

    for idx, art in enumerate(articles, start=1):
        art_id = art["id"]
        slug = art["slug"]
        pillar = art.get("pillar", "")
        md_filename = f"{slug}.md"
        json_filename = f"{slug}.json"
        md_path = os.path.join(out_dir, md_filename)
        json_path = os.path.join(out_dir, json_filename)

        # Check dashes in metadata and body
        for field in ["title", "meta_title", "meta_description", "body", "primary_keyword"]:
            val = art.get(field, "")
            if "\u2014" in val or "\u2013" in val:
                print(f"[FAIL] Article {art_id} ({slug}) contains em/en dash in field '{field}'!")
                failed = True
                fail_details.append(f"Article {art_id} ({slug}) dash in field '{field}'")

        # Check body word count
        prose = re.sub(r"```.*?```", "", art["body"], flags=re.S)
        words = len(re.findall(r"\w+", prose))
        if words < 1500:
            print(f"[FAIL] Article {art_id} ({slug}) has only {words} words (min 1500)!")
            failed = True
            fail_details.append(f"Article {art_id} ({slug}) word count {words} < 1500")

        # Build JSON-LD & FAQ
        json_ld, faqs = make_json_ld(art)

        # Build Markdown content
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
author: Tim Edukasi COOCA
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
            fail_details.append(f"Article {art_id} ({slug}) dash in final markdown")

        with open(md_path, "w", encoding="utf-8") as f:
            f.write(md_content)

        # Build JSON file
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
            "local_image_asset": f"public/images/blog/{slug}.jpg",
            "published_at": art["published_at"],
            "author": "Tim Edukasi COOCA",
            "word_count": words,
            "faqs": faqs,
            "schema_org": json.loads(json_ld),
            "content_markdown": md_content
        }

        with open(json_path, "w", encoding="utf-8") as f:
            json.dump(art_json, f, indent=2, ensure_ascii=False)

        # Run lint_content.py
        lint_cmd = [
            sys.executable,
            os.path.join("scripts", "lint_content.py"),
            md_path,
            "--links", links_path,
            "--min-words", "1500"
        ]
        res = subprocess.run(lint_cmd, capture_output=True, text=True, encoding="utf-8")
        if res.returncode != 0:
            print(f"[FAIL] Article {art_id:02d} ({slug}) failed linting:\n{res.stdout}\n{res.stderr}")
            failed = True
            lint_status = "FAIL"
            fail_details.append(f"Article {art_id} ({slug}) lint error:\n{res.stdout}")
        else:
            # Check for any WARN in stdout
            warn_count = res.stdout.count("[WARN]")
            if warn_count > 0:
                print(f"[WARN] Article {art_id:02d} ({slug}) had {warn_count} warnings:\n{res.stdout}")
                lint_status = "WARN"
                fail_details.append(f"Article {art_id} ({slug}) lint warning:\n{res.stdout}")
                failed = True
            else:
                print(f"[{idx:02d}/40] [PASS] ID {art_id:02d} | {slug} | Words: {words} | 0 em/en | Lint 100% OK (0 FAIL, 0 WARN)")
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
    progress_file = os.path.join("database", "data", "articles", ".progress-2026-11-20.json")
    with open(progress_file, "w", encoding="utf-8") as f:
        json.dump({
            "date": "2026-11-20",
            "pipeline": "DAILY EVERGREEN SEO PIPELINE COOCA (40 ARTIKEL EDUKASI UMKM PER HARI)",
            "total_articles": len(progress_summary),
            "status": "COMPLETED" if not failed else "FAILED",
            "summary": progress_summary,
            "errors": fail_details
        }, f, indent=2, ensure_ascii=False)
    print(f"\n[INFO] Progress saved to {progress_file}")

    # Update keyword-registry.json
    reg_path = os.path.join("database", "data", "keyword-registry.json")
    if os.path.exists(reg_path):
        with open(reg_path, "r", encoding="utf-8") as f:
            registry_data = json.load(f)
        
        # Check existing entries
        existing_slugs = {item["slug"] for item in registry_data if isinstance(item, dict) and "slug" in item}
        added_count = 0
        for art in articles:
            if art["slug"] not in existing_slugs:
                registry_data.append({
                    "date": "2026-11-20",
                    "pillar": art.get("pillar", ""),
                    "category": art.get("category", ""),
                    "primary_keyword": art["primary_keyword"],
                    "secondary_keywords": art["secondary_keywords"],
                    "slug": art["slug"],
                    "meta_title": art["meta_title"],
                    "meta_description": art["meta_description"]
                })
                existing_slugs.add(art["slug"])
                added_count += 1
        with open(reg_path, "w", encoding="utf-8") as f:
            json.dump(registry_data, f, indent=2, ensure_ascii=False)
        print(f"[INFO] keyword-registry.json updated: {added_count} new entries added (Total: {len(registry_data)})")

    if failed:
        print("\n[ERROR] Pipeline completed with ERRORS. See above.")
        sys.exit(1)
    else:
        print(f"\n[SUCCESS] ALL 40 EVERGREEN ARTICLES BUILT, LINTED, AND VALIDATED 100% WITH ZERO FAILS AND ZERO WARNS!")

if __name__ == "__main__":
    main()
