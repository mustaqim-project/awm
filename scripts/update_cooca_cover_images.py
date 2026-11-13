import json
from pathlib import Path
import re

articles_dir = Path("database/data/articles")

count = 0
for i in range(532, 572):
    json_files = list(articles_dir.glob(f"{i}-*.json"))
    md_files = list(articles_dir.glob(f"{i}-*.md"))

    if not json_files or not md_files:
        print(f"Warning: Missing files for ID {i}")
        continue

    json_path = json_files[0]
    md_path = md_files[0]

    with open(json_path, "r", encoding="utf-8") as f:
        meta = json.load(f)

    slug = meta.get("slug")
    if not slug:
        print(f"Warning: Missing slug in {json_path}")
        continue

    new_cover = f"https://cooca.id/images/blog/cooca-{slug}.jpg"
    meta["cover_image"] = new_cover

    with open(json_path, "w", encoding="utf-8") as f:
        json.dump(meta, f, indent=2, ensure_ascii=False)

    with open(md_path, "r", encoding="utf-8") as f:
        md_content = f.read()

    # Update frontmatter cover_image
    md_content = re.sub(
        r'cover_image:\s*"[^"]*"',
        f'cover_image: "{new_cover}"',
        md_content
    )

    with open(md_path, "w", encoding="utf-8") as f:
        f.write(md_content)

    count += 1
    print(f"[{count}/40] Updated cover for ID {i}: {slug} -> {new_cover}")

print("Successfully updated all 40 articles cover images.")
