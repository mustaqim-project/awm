import os
import re
import json

APP_VIEWS_DIR = r"c:\laragon\www\cooca_core\resources\views\app"

results = {
    "total_views": 0,
    "directories": {},
    "layout_usage": {},
    "component_usage": {
        "x-module-header": 0,
        "x-breadcrumb": 0,
        "x-table-search": 0,
        "x-sortable-th": 0,
        "x-table-sort-th": 0,
        "x-document-stepper": 0,
        "x-quota-modal": 0
    },
    "patterns": {
        "modals_found": 0,
        "modals_with_teleport": 0,
        "modals_missing_teleport": 0,
        "modals_details": [],
        "tables_found": 0,
        "tables_with_overflow_wrapper": 0,
        "tables_missing_overflow_wrapper": 0,
        "tables_details": [],
        "tabular_nums_found": 0,
        "buttons_with_min_h_44": 0,
        "buttons_short_touch_target": 0,
        "pill_badges_found": 0,
        "pulse_dots_found": 0,
        "emoji_found": 0,
        "emoji_details": [],
        "pulse_details": [],
        "nested_cards_issues": []
    },
    "file_evaluations": []
}

# Regex patterns
RE_EXTENDS = re.compile(r"@extends\s*\(\s*['\"]([^'\"]+)['\"]\s*\)")
RE_MODAL = re.compile(r"x-show=[\"'][^\"']*(?:modal|Modal|dialog|Dialog|sheet|Sheet|drawer|Drawer|show|open|Open)[^\"']*[\"']")
RE_TELEPORT = re.compile(r"<template\s+x-teleport=[\"']body[\"']")
RE_TABLE = re.compile(r"<table\b[^>]*>")
RE_TABLE_WRAPPER = re.compile(r"(?:table-responsive|overflow-x-auto|overflow-x-scroll)")
RE_PULSE = re.compile(r"animate-pulse")
RE_EMOJI = re.compile(r"[\U0001F300-\U0001FAFF\U00002702-\U000027B0\U000024C2-\U0001F251]")

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
        
        with open(filepath, "r", encoding="utf-8", errors="ignore") as fp:
            content = fp.read()
            
        # Layouts
        extends_match = RE_EXTENDS.findall(content)
        layout = extends_match[0] if extends_match else "none (partial or standalone)"
        results["layout_usage"][layout] = results["layout_usage"].get(layout, 0) + 1
        
        # Component usage
        for comp in results["component_usage"].keys():
            if f"<{comp}" in content:
                results["component_usage"][comp] += 1
                
        # Modals
        has_modal = bool(RE_MODAL.search(content))
        has_teleport = bool(RE_TELEPORT.search(content))
        if has_modal:
            results["patterns"]["modals_found"] += 1
            if has_teleport:
                results["patterns"]["modals_with_teleport"] += 1
            else:
                results["patterns"]["modals_missing_teleport"] += 1
                results["patterns"]["modals_details"].append(relpath)
                
        # Tables
        tables = RE_TABLE.findall(content)
        if tables:
            results["patterns"]["tables_found"] += len(tables)
            has_table_wrapper = bool(RE_TABLE_WRAPPER.search(content))
            if has_table_wrapper:
                results["patterns"]["tables_with_overflow_wrapper"] += len(tables)
            else:
                results["patterns"]["tables_missing_overflow_wrapper"] += len(tables)
                results["patterns"]["tables_details"].append(relpath)
                
        # Tabular nums
        if "tabular-nums" in content:
            results["patterns"]["tabular_nums_found"] += 1
            
        # Pulse dots
        pulses = RE_PULSE.findall(content)
        if pulses:
            results["patterns"]["pulse_dots_found"] += len(pulses)
            results["patterns"]["pulse_details"].append((relpath, len(pulses)))
            
        # Emojis
        emojis = RE_EMOJI.findall(content)
        if emojis:
            results["patterns"]["emoji_found"] += len(emojis)
            results["patterns"]["emoji_details"].append((relpath, list(set(emojis))))
            
        # Per file evaluation
        issues = []
        if has_modal and not has_teleport:
            issues.append({"issue": "Modal without x-teleport=\"body\"", "severity": "MEDIUM", "fix": "Wrap modal sheet in <template x-teleport=\"body\"> to guarantee z-index layering above sidebars and headers"})
        if tables and not has_table_wrapper:
            issues.append({"issue": "Table without responsive overflow wrapper", "severity": "HIGH", "fix": "Wrap table in <div class=\"table-responsive\"> or <div class=\"overflow-x-auto\"> to prevent mobile viewport blowout"})
        if emojis:
            issues.append({"issue": f"Raw emojis in UI: {' '.join(list(set(emojis))[:5])}", "severity": "LOW", "fix": "Replace raw emojis with Lucide SVG icons"})
        if pulses:
            issues.append({"issue": f"Fake pulse dots ({len(pulses)} instances)", "severity": "LOW", "fix": "Restrict animate-pulse only to real hardware socket/bluetooth connectivity"})
        if layout == "none (partial or standalone)" and not relpath.startswith("partials") and "/partials/" not in relpath:
            issues.append({"issue": "Stand-alone view without layout extension", "severity": "LOW", "fix": "Ensure view is intended as a modal partial/component, or extend layouts.app"})
            
        results["file_evaluations"].append({
            "path": f"resources/views/app/{relpath}",
            "subdir": subdir,
            "layout": layout,
            "has_header": "<x-module-header" in content,
            "has_modal": has_modal,
            "has_table": bool(tables),
            "issues": issues
        })

out_file = r"c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\survey_summary.json"
with open(out_file, "w", encoding="utf-8") as out:
    json.dump(results, out, indent=2)

print(f"Total views surveyed: {results['total_views']}")
print(f"Total directories: {len(results['directories'])}")
print("Layout usage:")
for k, v in results["layout_usage"].items():
    print(f"  {k}: {v}")
print(f"Modals found: {results['patterns']['modals_found']} (with teleport: {results['patterns']['modals_with_teleport']}, missing teleport: {results['patterns']['modals_missing_teleport']})")
print(f"Tables found: {results['patterns']['tables_found']} (with wrapper: {results['patterns']['tables_with_overflow_wrapper']}, missing wrapper: {results['patterns']['tables_missing_overflow_wrapper']})")
print(f"Views with raw emojis: {len(results['patterns']['emoji_details'])}")
print(f"Views with animate-pulse: {len(results['patterns']['pulse_details'])}")
