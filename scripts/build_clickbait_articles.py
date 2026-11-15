# scripts/build_clickbait_articles.py
import json, re
from pathlib import Path
from clickbait_helpers import assemble_markdown
from gen_clickbait_part1 import get_articles_part1
from gen_clickbait_part2 import get_articles_part2
from gen_clickbait_part3 import get_articles_part3
from gen_clickbait_part4 import get_articles_part4

out_dir = Path("database/data/articles")
out_dir.mkdir(parents=True, exist_ok=True)

all_articles = (
    get_articles_part1() +
    get_articles_part2() +
    get_articles_part3() +
    get_articles_part4()
)

print(f"Total articles to build: {len(all_articles)}")

# Assert 40 articles
assert len(all_articles) == 40, f"Expected 40 articles, got {len(all_articles)}"

for art in all_articles:
    art_id = art["id"]
    slug = art["slug"]
    
    # 1. Assemble markdown
    md_content = assemble_markdown(art)
    
    # Assert zero em dash and zero en dash
    assert "\u2014" not in md_content, f"Em dash found in {slug}"
    assert "\u2013" not in md_content, f"En dash found in {slug}"
    
    md_filename = f"{art_id}-{slug}.md"
    json_filename = f"{art_id}-{slug}.json"
    
    md_path = out_dir / md_filename
    json_path = out_dir / json_filename
    
    # Write markdown
    with open(md_path, "w", encoding="utf-8") as f:
        f.write(md_content)
        
    # Write json
    meta = {
        "id": art_id,
        "title": art["title"],
        "meta_title": art["meta_title"],
        "meta_description": art["meta_description"],
        "slug": art["slug"],
        "primary_keyword": art["primary_keyword"],
        "secondary_keywords": art["secondary_keywords"],
        "search_intent": art["search_intent"],
        "category": art["category"],
        "reading_time": art["reading_time"],
        "cover_image": art["cover_image"],
        "published_at": art["published_at"],
        "author": "Tim Edukasi COOCA",
        "is_published": True
    }
    with open(json_path, "w", encoding="utf-8") as f:
        json.dump(meta, f, indent=2, ensure_ascii=False)
        
    print(f"[{art_id}] Built {slug}")

print("All 40 articles successfully built into database/data/articles/!")
