#!/usr/bin/env python3
"""Pembersihan presisi tinggi untuk 3d_shopping_cart, 3d_shop_store, dan cutout_3d_shop."""
from pathlib import Path
from PIL import Image

ASSETS_DIR = Path(r"C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-feed-design\assets")
ICONS_DIR = ASSETS_DIR / "3d-icons"
SRC_ICONS = Path(r"C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\89f83d93-46c5-4211-98a8-037f15bb0b62\.user_uploaded\media_1794007783635_69113e41.jpg")

im = Image.open(SRC_ICONS).convert("RGBA")
BG_COLOR = (241, 245, 246)

def clean_crop_icon(box, tolerance=22):
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

# 3d_shop_store: y2 dipendekkan ke 695 agar tidak menyentuh rumah/toko di bawahnya
shop_icon = clean_crop_icon((160, 605, 285, 695))
shop_icon.save(ICONS_DIR / "3d_shop_store.png", "PNG")

# 3d_shopping_cart: y2 dipendekkan ke 835 agar tidak menyentuh bola di bawahnya
cart_icon = clean_crop_icon((460, 745, 580, 835))
cart_icon.save(ICONS_DIR / "3d_shopping_cart.png", "PNG")

print("Cleaned 3d icons saved!")

# Perbaikan cutout_3d_shop.png:
# Bersihkan seluruh noda hitam pada lantai podium
shop_img = Image.open(ASSETS_DIR / "cutout_3d_shop.png").convert("RGBA")
sw, sh = shop_img.size
sp = shop_img.load()

for y in range(int(sh * 0.55), sh):
    for x in range(sw):
        r, g, b, a = sp[x, y]
        if a > 0:
            # Jika pixel gelap di area lantai/dasar (bukan dinding toko atau kasir)
            # Area lantai dasar: y > 0.65 * sh, di luar gedung (x < sw * 0.35 atau y > sh * 0.8)
            if y > sh * 0.62 and x < sw * 0.38:
                # Noda hitam pada lantai podium
                if r < 100 and g < 100 and b < 100:
                    sp[x, y] = (245, 247, 250, a) # Warnai sesuai lantai putih podium
            if y > sh * 0.88:
                # Hilangkan bayangan meja terluar di bawah piringan
                if r < 80 and g < 80 and b < 80:
                    sp[x, y] = (r, g, b, 0)

shop_img.save(ASSETS_DIR / "cutout_3d_shop.png", "PNG")
print("Cleaned cutout_3d_shop saved!")
