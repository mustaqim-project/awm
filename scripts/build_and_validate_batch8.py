# scripts/build_and_validate_batch8.py
import os, sys, json, re, subprocess

# Ensure scripts dir is on sys.path
sys.path.insert(0, os.path.dirname(__file__))

from batch8_common import make_json_ld
from generate_batch8_part1 import get_articles_part1
from generate_batch8_part2 import get_articles_part2
from generate_batch8_part3 import get_articles_part3
from generate_batch8_part4 import get_articles_part4

def main():
    arts1 = get_articles_part1()
    arts2 = get_articles_part2()
    arts3 = get_articles_part3()
    arts4 = get_articles_part4()
    articles = arts1 + arts2 + arts3 + arts4
    print(f"Loaded {len(articles)} articles (IDs 372 to 391).")

    out_dir = os.path.join("database", "data", "articles")
    os.makedirs(out_dir, exist_ok=True)

    failed = False
    for art in articles:
        art_id = art["id"]
        slug = art["slug"]
        md_filename = f"{art_id}-{slug}.md"
        json_filename = f"{art_id}-{slug}.json"
        md_path = os.path.join(out_dir, md_filename)
        json_path = os.path.join(out_dir, json_filename)

        # 1. Check dashes in body, title, description
        for field in ["title", "meta_title", "meta_description", "body", "primary_keyword"]:
            val = art.get(field, "")
            if "\u2014" in val or "\u2013" in val:
                print(f"[FAIL] Article {art_id} contains em/en dash in field '{field}'!")
                failed = True

        # 2. Build JSON-LD and FAQ
        json_ld, faqs = make_json_ld(art)

        # 3. Build Markdown content
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
        # Ensure no dashes in final md_content
        if "\u2014" in md_content or "\u2013" in md_content:
            print(f"[FAIL] Article {art_id} contains em/en dash in final markdown!")
            failed = True

        with open(md_path, "w", encoding="utf-8") as f:
            f.write(md_content)

        # 4. Build JSON content
        art_json = {
            "id": art_id,
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
            "faqs": faqs,
            "schema_org": json.loads(json_ld),
            "content_markdown": md_content
        }

        with open(json_path, "w", encoding="utf-8") as f:
            json.dump(art_json, f, indent=2, ensure_ascii=False)

        # 5. Run lint_content.py
        lint_cmd = [
            sys.executable,
            os.path.join("scripts", "lint_content.py"),
            md_path,
            "--links", "blog_links.json"
        ]
        res = subprocess.run(lint_cmd, capture_output=True, text=True, encoding="utf-8")
        if res.returncode != 0:
            print(f"[FAIL] Linter failed for {md_filename}:\n{res.stdout}\n{res.stderr}")
            failed = True
        else:
            print(f"[PASS] {md_filename} passed linter (0 FAIL).")

    if failed:
        print("\n[ERROR] One or more validations failed!")
        sys.exit(1)
    else:
        print(f"\n[SUCCESS] All {len(articles)} articles generated and validated successfully with 0 FAIL!")

if __name__ == "__main__":
    main()
