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

CSS_BASE = """
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background-color: #070D1E;
    color: #F8FAFC;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    -webkit-font-smoothing: antialiased;
    padding: 32px;
}

.mono { font-family: 'JetBrains Mono', monospace; }

/* ERP Window Frame Layout */
.app-window {
    background: #0B142F;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    margin-bottom: 40px;
}

/* Window Navigation Topbar */
.app-topbar {
    background: #0D193B;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.window-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.w-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
.w-red { background: #EF4444; }
.w-yellow { background: #F59E0B; }
.w-green { background: #10B981; }

.topbar-center {
    display: flex;
    align-items: center;
    gap: 16px;
}

.module-badge {
    background: rgba(14, 165, 233, 0.15);
    border: 1px solid rgba(14, 165, 233, 0.35);
    color: #38BDF8;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.app-body {
    display: flex;
    min-height: 720px;
}

/* Sidebar Navigation */
.app-sidebar {
    width: 240px;
    background: #091126;
    border-right: 1px solid rgba(255, 255, 255, 0.06);
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex-shrink: 0;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: #94A3B8;
}

.nav-item.active {
    background: #1E293B;
    color: #38BDF8;
    font-weight: 700;
    border-left: 3px solid #38BDF8;
}

/* Main Content Workspace */
.app-content {
    flex: 1;
    background: #0B142F;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    overflow: hidden;
}

/* Header within Content */
.content-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.content-title {
    font-size: 20px;
    font-weight: 700;
    color: #FFFFFF;
}

.content-subtitle {
    font-size: 13px;
    color: #94A3B8;
    margin-top: 2px;
}

/* Card Foundation */
.ui-card {
    background: #0F1D3D;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 18px;
}

.ui-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #FFFFFF;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Status Badges */
.badge {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}
.badge-success { background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3); }
.badge-warning { background: rgba(245, 158, 11, 0.15); color: #FBBF24; border: 1px solid rgba(245, 158, 11, 0.3); }
.badge-danger { background: rgba(239, 68, 68, 0.15); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.3); }
.badge-info { background: rgba(56, 189, 248, 0.15); color: #38BDF8; border: 1px solid rgba(56, 189, 248, 0.3); }

/* Buttons */
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
}
.btn-primary { background: #0284C7; color: #FFFFFF; }
.btn-success { background: #059669; color: #FFFFFF; }
.btn-secondary { background: rgba(255, 255, 255, 0.08); color: #E2E8F0; }

/* Table styling */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.data-table th {
    text-align: left;
    padding: 10px 12px;
    color: #94A3B8;
    background: #0A1329;
    font-weight: 600;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.data-table td {
    padding: 10px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    color: #E2E8F0;
}
"""
print("CSS Base defined successfully.")
