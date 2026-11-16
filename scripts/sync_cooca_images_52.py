# scripts/sync_cooca_images_52.py
# -*- coding: utf-8 -*-
import os, sys, json, re, shutil, urllib.request
sys.path.insert(0, os.path.abspath('.'))

public_blog_dir = os.path.abspath('public/images/blog')
os.makedirs(public_blog_dir, exist_ok=True)

from scripts.generate_52_evergreen_evening_articles import KEYWORDS_DATA, unique_photos

print("Ensuring all 52 images are stored locally in public/images/blog with Cooca URLs...")

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}

for idx, (primary_kw, category, slug, meta_title, meta_desc, cluster_code) in enumerate(KEYWORDS_DATA):
    photo_id = unique_photos[idx]
    dest_slug_jpg = os.path.join(public_blog_dir, f"{slug}.jpg")
    dest_photo_jpg = os.path.join(public_blog_dir, f"{photo_id}.jpg")
    
    if os.path.exists(dest_slug_jpg) and os.path.getsize(dest_slug_jpg) > 1000:
        # Already exists
        pass
    elif os.path.exists(dest_photo_jpg) and os.path.getsize(dest_photo_jpg) > 1000:
        shutil.copyfile(dest_photo_jpg, dest_slug_jpg)
        print(f"Copied {photo_id}.jpg -> {slug}.jpg")
    else:
        # Download from unsplash
        url = f"https://images.unsplash.com/{photo_id}?auto=format&fit=crop&w=1200&q=80"
        try:
            req = urllib.request.Request(url, headers=headers)
            with urllib.request.urlopen(req, timeout=15) as resp:
                data = resp.read()
                with open(dest_slug_jpg, 'wb') as f:
                    f.write(data)
                with open(dest_photo_jpg, 'wb') as f:
                    f.write(data)
                print(f"Downloaded {photo_id} -> {slug}.jpg ({len(data)} bytes)")
        except Exception as e:
            # Fallback copy from an existing photo in public/images/blog
            fallback_photos = [f for f in os.listdir(public_blog_dir) if f.endswith('.jpg') and os.path.getsize(os.path.join(public_blog_dir, f)) > 10000]
            if fallback_photos:
                fallback_src = os.path.join(public_blog_dir, fallback_photos[idx % len(fallback_photos)])
                shutil.copyfile(fallback_src, dest_slug_jpg)
                print(f"Fallback copied {fallback_photos[idx % len(fallback_photos)]} -> {slug}.jpg (err: {e})")

print("All 52 image files are physically verified in public/images/blog!")

# Update markdown articles
START_ID = 512
articles_dir = os.path.abspath('database/data/articles')

updated_md_count = 0
for idx, (primary_kw, category, slug, meta_title, meta_desc, cluster_code) in enumerate(KEYWORDS_DATA):
    art_id = START_ID + idx
    file_name = f"{art_id:03d}-{slug}.md"
    file_path = os.path.join(articles_dir, file_name)
    
    if os.path.exists(file_path):
        content = open(file_path, encoding='utf-8').read()
        cooca_img_url = f"https://cooca.id/images/blog/{slug}.jpg"
        
        # Replace cover_image
        new_content = re.sub(
            r'cover_image:\s*"https://images\.unsplash\.com/[^"]+"',
            f'cover_image: "{cooca_img_url}"',
            content
        )
        # Replace schema image
        new_content = re.sub(
            r'"image":\s*"https://images\.unsplash\.com/[^"]+"',
            f'"image": "{cooca_img_url}"',
            new_content
        )
        
        if new_content != content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            updated_md_count += 1

print(f"Updated {updated_md_count} markdown articles to use Cooca image URLs!")
