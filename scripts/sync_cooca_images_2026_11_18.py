# scripts/sync_cooca_images_2026_11_18.py
# -*- coding: utf-8 -*-
import json
import os
import shutil

def main():
    research_file = os.path.join("database", "data", "articles", "keyword-research-2026-11-18.json")
    with open(research_file, "r", encoding="utf-8") as f:
        data = json.load(f)

    topics = data["topics"]
    blog_dir = os.path.join("public", "images", "blog")
    os.makedirs(blog_dir, exist_ok=True)

    success_count = 0
    for t in topics:
        slug = t["slug"]
        src_name = t["source_image"]
        src_path = os.path.join(blog_dir, src_name)
        dst_path = os.path.join(blog_dir, f"{slug}.jpg")

        if not os.path.exists(src_path):
            print(f"[ERR] Source image {src_name} not found in {blog_dir}!")
            continue

        shutil.copyfile(src_path, dst_path)
        sz_kb = os.path.getsize(dst_path) / 1024
        print(f"[OK] {slug}.jpg created from {src_name} ({sz_kb:.1f} KB)")
        success_count += 1

    print(f"\nSynced {success_count}/{len(topics)} Cooca owned image assets to public/images/blog/")
    assert success_count == len(topics), "Not all images were synced successfully!"

if __name__ == "__main__":
    main()
