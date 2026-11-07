#!/usr/bin/env python3
"""Ekstraksi aset icon 3D dari gambar referensi pengguna ke PNG transparan.

Gambar sumber: media_1794007783635_69113e41.jpg (599 x 1024 px)
Grid: 4 kolom x 7 baris
Warna background: rgb(241, 245, 246)
"""
from pathlib import Path
from PIL import Image

SRC = Path(r"C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\89f83d93-46c5-4211-98a8-037f15bb0b62\.user_uploaded\media_1794007783635_69113e41.jpg")
OUT_DIR = Path(r"C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-feed-design\assets\3d-icons")
OUT_DIR.mkdir(parents=True, exist_ok=True)

im = Image.open(SRC).convert("RGBA")
W, H = im.size

# Background color sampling
BG_COLOR = (241, 245, 246)

def remove_bg(crop_img, tolerance=18):
    """Menghapus background solid dengan alpha feathering halus."""
    rgba = crop_img.convert("RGBA")
    datas = rgba.getdata()
    new_data = []
    br, bg, bb = BG_COLOR
    for item in datas:
        r, g, b, a = item
        diff = max(abs(r - br), abs(g - bg), abs(b - bb))
        if diff <= tolerance:
            new_data.append((r, g, b, 0))
        elif diff <= tolerance + 15:
            # feather / smooth edge
            alpha = int(255 * (diff - tolerance) / 15)
            new_data.append((r, g, b, alpha))
        else:
            new_data.append((r, g, b, 255))
    rgba.putdata(new_data)
    # Autocrop bounding box transparan
    bbox = rgba.getbbox()
    if bbox:
        return rgba.crop(bbox)
    return rgba

# Definisi nama-nama icon berdasarkan posisi row (0-6) dan col (0-3)
# Lebar tiap kolom ~ 599 / 4 = 149.75 px
# Tinggi tiap baris ~ 1024 / 7 = 146.28 px
COLS = 4
ROWS = 7
CELL_W = W / COLS
CELL_H = H / ROWS

ICON_NAMES = {
    (1, 1): "3d_pie_chart",
    (1, 2): "3d_shopping_bag",
    (2, 1): "3d_growth_arrow",
    (2, 2): "3d_trophy",
    (4, 1): "3d_shop_store",
    (4, 3): "3d_bank_building",
    (5, 0): "3d_rocket",
    (5, 1): "3d_target",
    (5, 3): "3d_shopping_cart",
    (6, 0): "3d_gold_star",
}

for (row, col), name in ICON_NAMES.items():
    x1 = int(col * CELL_W)
    y1 = int(row * CELL_H)
    x2 = int((col + 1) * CELL_W)
    y2 = int((row + 1) * CELL_H)
    
    cell_crop = im.crop((x1, y1, x2, y2))
    transparent_icon = remove_bg(cell_crop)
    
    out_path = OUT_DIR / f"{name}.png"
    transparent_icon.save(out_path, "PNG")
    print(f"Extracted {name} -> {out_path} ({transparent_icon.size})")

print("All 3D icons extracted successfully!")
