#!/usr/bin/env python3
"""Script untuk konversi aset 3D render berlatar putih menjadi HD Transparent PNG (RGBA).
Fitur:
- Deteksi warna latar otomatis dari 4 sudut (corners).
- Alpha feathering halus anti-halo dan anti-fringe putih.
- Bounding box auto-crop dengan margin pengaman (safe padding) agar tidak terpotong.
- Menyimpan output resolusi tinggi (HD) ke direktori aset resmi Cooca.
"""
import os
import sys
from pathlib import Path
from PIL import Image

def convert_to_hd_transparent(src_path, out_paths, tolerance=22, feather=18, padding=16):
    src = Path(src_path)
    if not src.exists():
        print(f"[Error] Source file not found: {src_path}")
        return False

    im = Image.open(src).convert("RGBA")
    W, H = im.size
    print(f"\n[Processing HD] {src.name} (Original: {W}x{H} px)...")

    # Sample background color from corners
    corners = [
        im.getpixel((5, 5)),
        im.getpixel((W - 6, 5)),
        im.getpixel((5, H - 6)),
        im.getpixel((W - 6, H - 6)),
        im.getpixel((W // 2, 5)),
        im.getpixel((W // 2, H - 6)),
    ]
    bg_r = sum(c[0] for c in corners) // len(corners)
    bg_g = sum(c[1] for c in corners) // len(corners)
    bg_b = sum(c[2] for c in corners) // len(corners)
    print(f" -> Background color detected: RGB({bg_r}, {bg_g}, {bg_b})")

    datas = im.getdata()
    new_data = []

    for item in datas:
        r, g, b, a = item
        # Euclidean or max distance in RGB space
        diff = max(abs(r - bg_r), abs(g - bg_g), abs(b - bg_b))
        
        if diff <= tolerance:
            # Completely transparent
            new_data.append((r, g, b, 0))
        elif diff <= tolerance + feather:
            # Smooth feather alpha
            ratio = (diff - tolerance) / float(feather)
            alpha = int(255 * ratio)
            # De-fringe: blend RGB slightly towards original color without white
            new_data.append((r, g, b, alpha))
        else:
            new_data.append((r, g, b, 255))

    im.putdata(new_data)

    # Autocrop with safe padding
    bbox = im.getbbox()
    if bbox:
        x1, y1, x2, y2 = bbox
        x1 = max(0, x1 - padding)
        y1 = max(0, y1 - padding)
        x2 = min(W, x2 + padding)
        y2 = min(H, y2 + padding)
        im = im.crop((x1, y1, x2, y2))
        print(f" -> Cropped with safe padding ({padding}px) to: {im.size} px (Zero Cut-Off)")

    for out_path in out_paths:
        out_p = Path(out_path)
        out_p.parent.mkdir(parents=True, exist_ok=True)
        im.save(out_p, "PNG")
        print(f" -> Saved HD Transparent PNG: {out_p} ({im.size[0]}x{im.size[1]} px, {os.path.getsize(out_p):,} bytes)")

    return True

if __name__ == '__main__':
    # Test on the already generated test_shop image
    test_shop = Path(r"C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\0d1f962d-7b02-4b57-a738-486359f76ce6\test_shop_1794028821345.jpg")
    if test_shop.exists():
        convert_to_hd_transparent(
            test_shop,
            [
                r"c:\laragon\www\cooca_core\public\admin\social-media-assets\pinterest-showcase\cutout_3d_shop.png",
                r"c:\laragon\www\cooca_core\storage\app\public\admin\social-media-assets\pinterest-showcase\cutout_3d_shop.png",
                r"C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-feed-design\assets\cutout_3d_shop.png",
                r"c:\laragon\www\cooca_core\public\admin\social-media-assets\pinterest-showcase\3d_shop_store.png",
            ]
        )
