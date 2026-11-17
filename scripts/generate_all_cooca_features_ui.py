import os
import base64
import asyncio
from playwright.async_api import async_playwright

BASE_DIR = r"C:\laragon\www\cooca_core"
OUTPUT_DIR = os.path.join(BASE_DIR, r"storage\app\public\features_ui_render")
os.makedirs(OUTPUT_DIR, exist_ok=True)

def get_base64_img(rel_path):
    full_path = os.path.join(BASE_DIR, rel_path)
    if not os.path.exists(full_path):
        return ""
    ext = os.path.splitext(full_path)[1].replace(".", "") or "png"
    with open(full_path, "rb") as f:
        data = f.read()
    return f"data:image/{ext};base64,{base64.b64encode(data).decode('utf-8')}"

logo_light = get_base64_img("public/assets/image/1785229034_logo_light.png")
if not logo_light:
    logo_light = get_base64_img("public/assets/image/cooca-logo-landscape.png")

COMMON_CSS = """
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background-color: #030712;
    color: #F8FAFC;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    -webkit-font-smoothing: antialiased;
    line-height: 1.5;
}

.mono { font-family: 'JetBrains Mono', monospace; }

/* Gradients & Glows */
.bg-canvas {
    background: radial-gradient(circle at 50% 0%, #0F172A 0%, #030712 70%);
}

.glow-blue {
    box-shadow: 0 0 50px -10px rgba(56, 189, 248, 0.25);
}

.glow-emerald {
    box-shadow: 0 0 50px -10px rgba(16, 185, 129, 0.25);
}

/* Window Chrome */
.window-frame {
    background: #0B132B;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);
}

.window-header {
    background: #0F1B3E;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.traffic-dots {
    display: flex;
    gap: 8px;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
.dot-red { background: #EF4444; }
.dot-yellow { background: #F59E0B; }
.dot-green { background: #10B981; }

.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.02em;
}

.badge-cyan {
    background: rgba(6, 182, 212, 0.15);
    color: #38BDF8;
    border: 1px solid rgba(6, 182, 212, 0.3);
}

.badge-emerald {
    background: rgba(16, 185, 129, 0.15);
    color: #34D399;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.badge-amber {
    background: rgba(245, 158, 11, 0.15);
    color: #FBBF24;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.badge-rose {
    background: rgba(244, 63, 94, 0.15);
    color: #FB7185;
    border: 1px solid rgba(244, 63, 94, 0.3);
}

.badge-purple {
    background: rgba(168, 85, 247, 0.15);
    color: #C084FC;
    border: 1px solid rgba(168, 85, 247, 0.3);
}

.card {
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 16px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #0284C7, #0EA5E9);
    color: #FFFFFF;
    box-shadow: 0 4px 14px -3px rgba(14, 165, 233, 0.4);
}

.btn-emerald {
    background: linear-gradient(135deg, #059669, #10B981);
    color: #FFFFFF;
    box-shadow: 0 4px 14px -3px rgba(16, 185, 129, 0.4);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.06);
    color: #E2E8F0;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.table-ui {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.table-ui th {
    text-align: left;
    padding: 10px 12px;
    color: #94A3B8;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    font-weight: 600;
}

.table-ui td {
    padding: 10px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    color: #E2E8F0;
}
"""

print("Common CSS defined.")
