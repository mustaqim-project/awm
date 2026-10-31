import os
import re
import json

APP_VIEWS_DIR = r"c:\laragon\www\cooca_core\resources\views\app"

results = {
    "total_views": 0,
    "directories_count": 0,
    "directories": {},
    "layout_usage": {},
    "component_usage": {
        "x-module-header": 0,
        "x-breadcrumb": 0,
        "x-module-tabs": 0,
        "x-table-search": 0,
        "x-sortable-th": 0,
        "x-table-sort-th": 0,
        "x-document-stepper": 0,
        "x-quota-modal": 0
    },
    "architectural_patterns": {
        "double_headers": [],
        "views_without_mobile_bottom_padding": [],
        "views_with_hardcoded_grid_cols": [],
        "modals_total": 0,
        "modals_with_teleport": 0,
        "modals_missing_teleport": [],
        "modals_narrow_sizing": [],
        "tables_total": 0,
        "tables_with_overflow_wrapper": 0,
        "tables_missing_overflow_wrapper": [],
        "tables_without_sort": [],
        "tables_missing_tabular_nums": [],
        "tables_missing_empty_state": [],
        "emoji_in_views": [],
        "animate_pulse_in_views": [],
        "button_touch_target_issues": []
    },
    "directory_breakdown": {},
    "findings_by_severity": {
        "CRITICAL": [],
        "HIGH": [],
        "MEDIUM": [],
        "LOW": []
    }
}

RE_EXTENDS = re.compile(r"@extends\s*\(\s*['\"]([^'\"]+)['\"]")
RE_HEADER_TITLE = re.compile(r"['\"]headerTitle['\"]\s*=>")
RE_MODULE_HEADER = re.compile(r"<x-module-header\b")
RE_H1 = re.compile(r"<h1\b[^>]*>")
RE_MODAL = re.compile(r"x-show=[\"'][^\"']*(?:modal|Modal|dialog|Dialog|sheet|Sheet|drawer|Drawer|show[A-Z]|open[A-Z])[^\"']*[\"']")
RE_TELEPORT = re.compile(r"<template\s+x-teleport=[\"']body[\"']")
RE_MODAL_NARROW = re.compile(r"\bmax-w-(?:sm|md|lg)\b")
RE_TABLE = re.compile(r"<table\b[^>]*>")
RE_TABLE_WRAPPER = re.compile(r"(?:table-responsive|overflow-x-auto|overflow-x-scroll)")
RE_PULSE = re.compile(r"animate-pulse")
RE_EMOJI = re.compile(r"[\U0001F300-\U0001FAFF\U00002702-\U000027B0\U000024C2-\U0001F251]")
RE_HARDCODED_GRID = re.compile(r"(?<![a-zA-Z0-9:])grid-cols-(?:[3-9]|1[0-2])\b")
RE_PB_MOBILE = re.compile(r"\bpb-(?:2[4-9]|3[0-9]|4[0-9]|28|32|36)\b")
RE_TABULAR_NUMS = re.compile(r"\btabular-nums\b")
RE_EMPTY_STATE = re.compile(r"(?:empty-state|Belum ada|tidak ada|no-data|data-empty|empty_state)", re.IGNORECASE)
RE_SORT_TH = re.compile(r"(?:x-sortable-th|x-table-sort-th|coocaTableSort|data-sort-value|data-sort)")

for root, dirs, files in os.walk(APP_VIEWS_DIR):
    for f in files:
        if not f.endswith(".blade.php"):
            continue
        filepath = os.path.join(root, f)
        relpath = os.path.relpath(filepath, APP_VIEWS_DIR).replace("\\", "/")
        results["total_views"] += 1
        
        subdir = relpath.split("/")[0] if "/" in relpath else "_root"
        results["directories"].setdefault(subdir, 0)
        results["directories"][subdir] += 1
        
        if subdir not in results["directory_breakdown"]:
            results["directory_breakdown"][subdir] = {
                "views": [],
                "modals": 0,
                "tables": 0,
                "issues": []
            }
        results["directory_breakdown"][subdir]["views"].append(relpath)
        
        with open(filepath, "r", encoding="utf-8", errors="ignore") as fp:
            content = fp.read()
            
        # Layouts
        extends_match = RE_EXTENDS.findall(content)
        layout = extends_match[0] if extends_match else "none (partial or standalone)"
        results["layout_usage"][layout] = results["layout_usage"].get(layout, 0) + 1
        
        # Components
        for comp in results["component_usage"].keys():
            if f"<{comp}" in content:
                results["component_usage"][comp] += 1
                
        # 1. Double Header Check
        has_extends_headertitle = bool(RE_HEADER_TITLE.search(content))
        has_module_header = bool(RE_MODULE_HEADER.search(content))
        has_h1 = bool(RE_H1.search(content))
        if has_extends_headertitle and has_module_header:
            results["architectural_patterns"]["double_headers"].append(relpath)
            results["findings_by_severity"]["MEDIUM"].append({
                "view": f"resources/views/app/{relpath}",
                "issue": "Double Header Redundancy: Topbar receives headerTitle while page content renders <x-module-header>, causing duplicate page titles and vertical space wastage.",
                "recommendation": "Harmonize topbar headerTitle with module header actions, or streamline <x-module-header> to breadcrumbs/actions toolbar without repeating title/subtitle."
            })
            
        # 2. Mobile Bottom Tab Bar Obstruction (pb-28 / pb-36)
        if layout != "none (partial or standalone)" and not relpath.startswith("pos/terminal"):
            if not RE_PB_MOBILE.search(content):
                results["architectural_patterns"]["views_without_mobile_bottom_padding"].append(relpath)
                results["findings_by_severity"]["HIGH"].append({
                    "view": f"resources/views/app/{relpath}",
                    "issue": "Missing Mobile Bottom Padding: View lacks pb-28/pb-36 padding on main container. Content and action buttons at page bottom will be obstructed by the fixed iOS bottom navigation tab bar.",
                    "recommendation": "Add pb-28 sm:pb-32 lg:pb-10 to the page content wrapper container."
                })
                
        # 3. Hardcoded Multi-Column Grids
        unprefixed_grids = RE_HARDCODED_GRID.findall(content)
        if unprefixed_grids:
            results["architectural_patterns"]["views_with_hardcoded_grid_cols"].append((relpath, unprefixed_grids))
            results["findings_by_severity"]["HIGH"].append({
                "view": f"resources/views/app/{relpath}",
                "issue": f"Unresponsive Hardcoded Grid: Uses '{', '.join(set(unprefixed_grids))}' without mobile breakpoint prefix (e.g., grid-cols-1 sm:grid-cols-2 lg:grid-cols-4). Breaks layout on mobile viewports (<640px).",
                "recommendation": "Convert to mobile-first responsive grid: grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4."
            })
            
        # 4. Modals
        modals = RE_MODAL.findall(content)
        if modals:
            results["architectural_patterns"]["modals_total"] += len(modals)
            results["directory_breakdown"][subdir]["modals"] += len(modals)
            has_teleport = bool(RE_TELEPORT.search(content))
            if has_teleport:
                results["architectural_patterns"]["modals_with_teleport"] += len(modals)
            else:
                results["architectural_patterns"]["modals_missing_teleport"].append(relpath)
                results["findings_by_severity"]["HIGH"].append({
                    "view": f"resources/views/app/{relpath}",
                    "issue": "Modal Missing Teleportation: Modal lacks <template x-teleport=\"body\">. Causes stacking context issues where modal can appear behind sticky topbars or navigation drawers.",
                    "recommendation": "Wrap modal dialog in <template x-teleport=\"body\"> with z-[200] and backdrop layer covering $y=0$."
                })
            # Narrow modal check
            narrow_matches = RE_MODAL_NARROW.findall(content)
            if narrow_matches:
                results["architectural_patterns"]["modals_narrow_sizing"].append((relpath, list(set(narrow_matches))))
                results["findings_by_severity"]["MEDIUM"].append({
                    "view": f"resources/views/app/{relpath}",
                    "issue": f"Cramped Modal Dialog: Uses narrow width constraints ({', '.join(set(narrow_matches))}) instead of Bento Apple HIG full-size canvas (app-modal-dialog-md/lg/xxl).",
                    "recommendation": "Upgrade to Bento Apple HIG full-size modal: class=\"app-modal-dialog app-modal-dialog-lg sm:rounded-[24px]\" or 2-column bento layout."
                })
                
        # 5. Tables
        tables = RE_TABLE.findall(content)
        if tables:
            results["architectural_patterns"]["tables_total"] += len(tables)
            results["directory_breakdown"][subdir]["tables"] += len(tables)
            has_wrapper = bool(RE_TABLE_WRAPPER.search(content))
            if has_wrapper:
                results["architectural_patterns"]["tables_with_overflow_wrapper"] += len(tables)
            else:
                results["architectural_patterns"]["tables_missing_overflow_wrapper"].append(relpath)
                results["findings_by_severity"]["HIGH"].append({
                    "view": f"resources/views/app/{relpath}",
                    "issue": "Table Without Overflow Container: <table> element lacks parent wrapper with .table-responsive or overflow-x-auto. Causes severe horizontal scroll and page blowout on mobile screens.",
                    "recommendation": "Wrap table inside <div class=\"table-responsive overflow-x-auto rounded-[16px] border border-black/5 dark:border-white/10\">."
                })
            # Tabular numbers on currency
            if "Rp" in content or "formatCurrency" in content or "selling_price" in content or "amount" in content:
                if not RE_TABULAR_NUMS.search(content):
                    results["architectural_patterns"]["tables_missing_tabular_nums"].append(relpath)
                    results["findings_by_severity"]["LOW"].append({
                        "view": f"resources/views/app/{relpath}",
                        "issue": "Missing Tabular Numbers on Numeric/Currency Data: Financial figures rendered without tabular-nums font feature, causing jagged column alignment.",
                        "recommendation": "Add tabular-nums to currency and quantity cells (e.g., class=\"font-mono font-semibold tabular-nums\")."
                    })
            # Table sorting
            if not RE_SORT_TH.search(content) and len(tables) == 1:
                results["architectural_patterns"]["tables_without_sort"].append(relpath)
            # Empty state
            if not RE_EMPTY_STATE.search(content):
                results["architectural_patterns"]["tables_missing_empty_state"].append(relpath)
                results["findings_by_severity"]["MEDIUM"].append({
                    "view": f"resources/views/app/{relpath}",
                    "issue": "Missing Structured Empty State: Table lacks user-friendly empty state illustration and action button when data collection is empty.",
                    "recommendation": "Implement Apple HIG quiet empty state with Lucide icon, reassuring microcopy, and a clear primary action button."
                })
                
        # 6. Emojis
        emojis = RE_EMOJI.findall(content)
        if emojis:
            unique_emojis = list(set(emojis))
            results["architectural_patterns"]["emoji_in_views"].append((relpath, unique_emojis))
            results["findings_by_severity"]["LOW"].append({
                "view": f"resources/views/app/{relpath}",
                "issue": f"Raw Unicode Emojis in Interface: Found raw emojis ({' '.join(unique_emojis[:6])}) violating Apple HIG professional UI standards.",
                "recommendation": "Replace raw emojis with semantic Lucide SVG icons (<i data-lucide=\"...\">)."
            })
            
        # 7. Pulse Dots
        pulses = RE_PULSE.findall(content)
        if pulses:
            results["architectural_patterns"]["animate_pulse_in_views"].append((relpath, len(pulses)))
            results["findings_by_severity"]["LOW"].append({
                "view": f"resources/views/app/{relpath}",
                "issue": f"Decorative Pulse Dot: Contains {len(pulses)} instance(s) of animate-pulse on UI badges/metrics. Design system mandates animate-pulse only for live hardware status.",
                "recommendation": "Remove animate-pulse from static labels, use clean typographic weight or solid status indicator."
            })

results["directories_count"] = len(results["directories"])

out_file = r"c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\survey_summary_v2.json"
with open(out_file, "w", encoding="utf-8") as out:
    json.dump(results, out, indent=2)

print("--- SURVEY SUMMARY V2 ---")
print(f"Total views: {results['total_views']}")
print(f"Total directories: {results['directories_count']}")
print(f"Layout usage: {results['layout_usage']}")
print(f"Component usage: {results['component_usage']}")
print(f"Double header issues: {len(results['architectural_patterns']['double_headers'])}")
print(f"Views missing mobile bottom padding: {len(results['architectural_patterns']['views_without_mobile_bottom_padding'])}")
print(f"Views with hardcoded grid-cols: {len(results['architectural_patterns']['views_with_hardcoded_grid_cols'])}")
print(f"Modals total: {results['architectural_patterns']['modals_total']} (missing teleport: {len(results['architectural_patterns']['modals_missing_teleport'])}, narrow: {len(results['architectural_patterns']['modals_narrow_sizing'])})")
print(f"Tables total: {results['architectural_patterns']['tables_total']} (missing overflow wrapper: {len(results['architectural_patterns']['tables_missing_overflow_wrapper'])})")
print(f"Tables missing tabular nums: {len(results['architectural_patterns']['tables_missing_tabular_nums'])}")
print(f"Tables missing empty state: {len(results['architectural_patterns']['tables_missing_empty_state'])}")
print(f"Views with raw emojis: {len(results['architectural_patterns']['emoji_in_views'])}")
print(f"Views with decorative pulse: {len(results['architectural_patterns']['animate_pulse_in_views'])}")
print(f"Findings by severity: CRITICAL: {len(results['findings_by_severity']['CRITICAL'])}, HIGH: {len(results['findings_by_severity']['HIGH'])}, MEDIUM: {len(results['findings_by_severity']['MEDIUM'])}, LOW: {len(results['findings_by_severity']['LOW'])}")
