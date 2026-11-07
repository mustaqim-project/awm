#!/usr/bin/env python3
"""Pembersihan aset 3D & cutouts agar 100% bersih tanpa artefak tetangga."""
from pathlib import Path
from PIL import Image

ASSETS_DIR = Path(r"C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-feed-design\assets")
ICONS_DIR = ASSETS_DIR / "3d-icons"
SRC_ICONS = Path(r"C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\89f83d93-46c5-4211-98a8-037f15bb0b62\.user_uploaded\media_1794007783635_69113e41.jpg")

im = Image.open(SRC_ICONS).convert("RGBA")
BG_COLOR = (241, 245, 246)

def clean_crop_icon(box, tolerance=20):
    crop_img = im.crop(box)
    rgba = crop_img.convert("RGBA")
    datas = rgba.getdata()
    new_data = []
    br, bg, bb = BG_COLOR
    for item in datas:
        r, g, b, a = item
        diff = max(abs(r - br), abs(g - bg), abs(b - bb))
        if diff <= tolerance:
            new_data.append((r, g, b, 0))
        elif diff <= tolerance + 12:
            alpha = int(255 * (diff - tolerance) / 12)
            new_data.append((r, g, b, alpha))
        else:
            new_data.append((r, g, b, 255))
    rgba.putdata(new_data)
    bbox = rgba.getbbox()
    return rgba.crop(bbox) if bbox else rgba

# Koordinat presisi manual untuk tiap icon agar tidak memotong tetangga
# 3d_shop_store: row 4, col 1 (antara y: 590 sampai 715, x: 155 sampai 290)
shop_icon = clean_crop_icon((155, 600, 290, 715))
shop_icon.save(ICONS_DIR / "3d_shop_store.png", "PNG")

# 3d_shopping_cart: row 5, col 3 (antara y: 745 sampai 870, x: 450 sampai 590)
cart_icon = clean_crop_icon((450, 745, 590, 865))
cart_icon.save(ICONS_DIR / "3d_shopping_cart.png", "PNG")

# 3d_pie_chart: row 1, col 1 (antara y: 155 sampai 280, x: 155 sampai 290)
pie_icon = clean_crop_icon((155, 160, 290, 280))
pie_icon.save(ICONS_DIR / "3d_pie_chart.png", "PNG")

# 3d_growth_arrow: row 2, col 1 (antara y: 300 sampai 430, x: 155 sampai 290)
growth_arrow = clean_crop_icon((155, 305, 290, 425))
growth_arrow.save(ICONS_DIR / "3d_growth_arrow.png", "PNG")

# 3d_trophy: row 2, col 2 (antara y: 300 sampai 430, x: 305 sampai 440)
trophy_icon = clean_crop_icon((305, 305, 440, 425))
trophy_icon.save(ICONS_DIR / "3d_trophy.png", "PNG")

# 3d_gold_star: row 6, col 0 (antara y: 885 sampai 1010, x: 10 sampai 145)
star_icon = clean_crop_icon((10, 890, 145, 1010))
star_icon.save(ICONS_DIR / "3d_gold_star.png", "PNG")

# 3d_shopping_bag: row 1, col 2 (antara y: 155 sampai 280, x: 305 sampai 440)
bag_icon = clean_crop_icon((305, 160, 440, 280))
bag_icon.save(ICONS_DIR / "3d_shopping_bag.png", "PNG")

print("Cleaned 3D icons saved!")

# Pembersihan cutout 3D Growth: hapus residu bayangan bawah
growth_cutout = Image.open(ASSETS_DIR / "cutout_3d_growth.png").convert("RGBA")
gw, gh = growth_cutout.size
gp = growth_cutout.load()
for y in range(int(gh * 0.88), gh):
    for x in range(gw):
        r, g, b, a = gp[x, y]
        if a > 0:
            # Jika pixel abu-abu/terang bayangan meja
            if abs(r - g) < 15 and abs(g - b) < 15 and r > 160:
                gp[x, y] = (r, g, b, 0)
growth_cutout.save(ASSETS_DIR / "cutout_3d_growth.png", "PNG")
print("Cleaned 3D Growth cutout saved!")

# Pembersihan cutout 3D Shop: hilangkan artefak gelap di bawah lingkaran panggung
shop_cutout = Image.open(ASSETS_DIR / "cutout_3d_shop.png").convert("RGBA")
sw, sh = shop_cutout.size
sp = shop_cutout.load()
for y in range(int(sh * 0.72), sh):
    for x in range(0, int(sw * 0.45)):
        r, g, b, a = sp[x, y]
        # Hilangkan blob hitam di luar lantai podium
        if a > 0 and r < 50 and g < 50 and b < 50:
            sp[x, y] = (r, g, b, 0)
shop_cutout.save(ASSETS_DIR / "cutout_3d_shop.png", "PNG")
print("Cleaned 3D Shop cutout saved!")
