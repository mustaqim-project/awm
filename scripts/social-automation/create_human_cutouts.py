#!/usr/bin/env python3
"""Script untuk membuat cutout transparan dari foto orang asli dan aset 3D.
"""
from collections import deque
from pathlib import Path
from PIL import Image, ImageFilter

def flood_cutout(input_path, output_path, bg_threshold=235):
    img = Image.open(input_path).convert("RGB")
    w, h = img.size
    pixels = img.load()
    
    visited = set()
    queue = deque()
    
    def is_bg(x, y):
        r, g, b = pixels[x, y]
        return (r >= bg_threshold or g >= bg_threshold or b >= bg_threshold) and (min(r, g, b) >= 210)
    
    for x in range(w):
        if is_bg(x, 0):
            queue.append((x, 0))
            visited.add((x, 0))
        if is_bg(x, h - 1):
            queue.append((x, h - 1))
            visited.add((x, h - 1))
            
    for y in range(h):
        if is_bg(0, y) and (0, y) not in visited:
            queue.append((0, y))
            visited.add((0, y))
        if is_bg(w - 1, y) and (w - 1, y) not in visited:
            queue.append((w - 1, y))
            visited.add((w - 1, y))
            
    neighbors = [(-1, 0), (1, 0), (0, -1), (0, 1)]
    while queue:
        cx, cy = queue.popleft()
        for dx, dy in neighbors:
            nx, ny = cx + dx, cy + dy
            if 0 <= nx < w and 0 <= ny < h and (nx, ny) not in visited:
                if is_bg(nx, ny):
                    visited.add((nx, ny))
                    queue.append((nx, ny))
                    
    mask = Image.new("L", (w, h), 255)
    mask_pixels = mask.load()
    for (x, y) in visited:
        mask_pixels[x, y] = 0
        
    mask_blurred = mask.filter(ImageFilter.GaussianBlur(radius=1.5))
    
    rgba = img.convert("RGBA")
    rgba.putalpha(mask_blurred)
    
    bbox = rgba.getbbox()
    cropped = rgba.crop(bbox) if bbox else rgba
        
    Path(output_path).parent.mkdir(parents=True, exist_ok=True)
    cropped.save(output_path, "PNG")
    print(f"Extracted: {output_path} ({cropped.size})")

if __name__ == "__main__":
    assets_dir = Path(r"C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-feed-design\assets")
    brain_dir = Path(r"C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\89f83d93-46c5-4211-98a8-037f15bb0b62")

    # 1. Foto Orang Asli Tanpa Background
    woman_src = brain_dir / "indonesian_businesswoman_smartphone_1794007887432.jpg"
    flood_cutout(woman_src, assets_dir / "cutout_woman.png", bg_threshold=235)

    man_src = brain_dir / "indonesian_entrepreneur_portrait_1794007877481.jpg"
    flood_cutout(man_src, assets_dir / "cutout_man.png", bg_threshold=235)

    # 2. Aset 3D Profesional Tanpa Background
    growth_src = brain_dir / "financial_growth_3d_illustration_1794008288190.jpg"
    flood_cutout(growth_src, assets_dir / "cutout_3d_growth.png", bg_threshold=240)

    shop_src = brain_dir / "retail_store_pos_isometric_1794008307231.jpg"
    flood_cutout(shop_src, assets_dir / "cutout_3d_shop.png", bg_threshold=240)
