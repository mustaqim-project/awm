#!/usr/bin/env python3
"""Redesign parity guard for Laravel/Blade (or plain HTML) sites. Standard library only.

A redesign may change markup and styling but must not remove routes, links, forms,
form fields, or backend-bound data. This script snapshots those before the redesign
and diffs them afterwards.

  snapshot ROOT --out before.json [--views resources/views] [--views public] [--guard routes]
  diff before.json ROOT [--allow allow.txt] [--json]
  colors ROOT [--top 25]            # find the existing brand palette before designing

FAIL categories (exit code 1 if anything is missing): routes, urls, links, forms,
fields, guard (lines removed from guarded files such as routes/).
WARN categories (review by hand): ids, includes, sections, components, echoes,
loops, alpine.

Acknowledge an intentional removal by listing its value (one per line) in --allow.
"""
import argparse
import hashlib  # noqa: F401  (kept for future use)
import json
import re
import sys
from collections import Counter
from pathlib import Path

VIEW_PATTERNS = ("*.blade.php", "*.html", "*.htm")
SKIP_DIRS = {"node_modules", "vendor", ".git", "storage", "cache", "dist", "build"}

RE_ROUTE = re.compile(r"""\broute\(\s*['"]([^'"]+)['"]""")
RE_URLFN = re.compile(r"""\b(?:url|action)\(\s*['"]([^'"]+)['"]""")
RE_HREF = re.compile(r"""<a\b[^>]*?\bhref\s*=\s*(["'])(.*?)\1""", re.S | re.I)
RE_FORM = re.compile(r"<form\b([^>]*)>", re.S | re.I)
RE_FIELD = re.compile(r"<(?:input|select|textarea|button)\b([^>]*)>", re.S | re.I)
RE_ID = re.compile(r"""\bid\s*=\s*(["'])([^"'{}]+)\1""")
RE_INCLUDE = re.compile(r"""@(?:include|includeIf|includeWhen|includeFirst|extends|component|livewire|each)\(\s*(?:[^,'"]*,\s*)?['"]([^'"]+)['"]""")
RE_SECTION = re.compile(r"""@(section|yield|push|stack|slot|error|session)\(\s*['"]([^'"]+)['"]""")
RE_XCOMP = re.compile(r"<(x-[\w.\-:]+|livewire:[\w.\-]+)")
RE_ECHO = re.compile(r"\{\{\{?\s*(.+?)\s*\}?\}\}|\{!!\s*(.+?)\s*!!\}", re.S)
RE_LOOP = re.compile(r"@(foreach|forelse|for|while)\s*\(([^()]*(?:\([^()]*\)[^()]*)*)\)")
RE_ALPINE = re.compile(
    r"""(?:^|\s)(@click[\w.]*|x-on:[\w.:-]+|@submit[\w.]*|x-model[\w.]*|wire:[\w.:-]+)\s*=\s*(["'])(.*?)\2""",
    re.S)
RE_HEX = re.compile(r"#(?:[0-9a-fA-F]{8}|[0-9a-fA-F]{6}|[0-9a-fA-F]{3})\b")
RE_TWCOLOR = re.compile(r"\b(?:bg|text|border|from|via|to|ring|fill|stroke|divide|outline|accent|decoration)-([a-z]+)-(\d{2,3})\b")
RE_CSSVAR = re.compile(r"(--[\w-]+)\s*:\s*([^;}{]+)(?=;|\})")
IGNORE_ECHO = re.compile(r"^(asset|mix|vite|Vite|csrf_token|url|route|e\()")

FAIL_KEYS = ["routes", "urls", "links", "forms", "fields"]
WARN_KEYS = ["ids", "includes", "sections", "components", "echoes", "loops", "alpine"]


def norm(s):
    return re.sub(r"\s+", " ", s).strip()


def walk(root, rel_dirs, patterns):
    seen = set()
    for rd in rel_dirs:
        base = (root / rd)
        if not base.exists():
            continue
        for pat in patterns:
            for p in base.rglob(pat):
                if any(part in SKIP_DIRS for part in p.parts) or p in seen:
                    continue
                seen.add(p)
                yield p


def attr(tag_attrs, name):
    m = re.search(r"""\b%s\s*=\s*(["'])(.*?)\1""" % re.escape(name), tag_attrs, re.S | re.I)
    return norm(m.group(2)) if m else None


def collect(root, views):
    data = {k: set() for k in FAIL_KEYS + WARN_KEYS}
    seo = {}
    files = list(walk(root, views, VIEW_PATTERNS))
    for p in files:
        try:
            t = p.read_text(encoding="utf-8", errors="ignore")
        except OSError:
            continue
        for m in RE_ROUTE.finditer(t):
            data["routes"].add(m.group(1))
        for m in RE_URLFN.finditer(t):
            data["urls"].add(m.group(1))
        for m in RE_HREF.finditer(t):
            h = norm(m.group(2))
            if not h or h == "#" or h.startswith("javascript:") or any(x in h for x in ("{{", "{!!", "@")):
                continue
            data["links"].add(h)
        for m in RE_FORM.finditer(t):
            a = attr(m.group(1), "action") or ""
            meth = (attr(m.group(1), "method") or "get").lower()
            data["forms"].add(f"{meth.upper()} {a}")
        for m in RE_FIELD.finditer(t):
            n = attr(m.group(1), "name")
            if n:
                data["fields"].add(n)
        for m in RE_ID.finditer(t):
            data["ids"].add(m.group(2))
        for m in RE_INCLUDE.finditer(t):
            data["includes"].add(m.group(1))
        for m in RE_SECTION.finditer(t):
            data["sections"].add(f"{m.group(1)}:{m.group(2)}")
        for m in RE_XCOMP.finditer(t):
            data["components"].add(m.group(1))
        for m in RE_ECHO.finditer(t):
            e = norm(m.group(1) or m.group(2) or "")
            if e and not IGNORE_ECHO.match(e) and len(e) < 160:
                data["echoes"].add(e)
        for m in RE_LOOP.finditer(t):
            data["loops"].add(f"@{m.group(1)}({norm(m.group(2))})")
        for m in RE_ALPINE.finditer(t):
            data["alpine"].add(f"{m.group(1)}={norm(m.group(3))}"[:200])
        rel = str(p.relative_to(root))
        seo[rel] = {
            "title": bool(re.search(r"<title\b|@section\(\s*['\"]title", t)),
            "description": bool(re.search(r"""name=["']description["']""", t)),
            "canonical": bool(re.search(r"""rel=["']canonical["']""", t)),
            "jsonld": len(re.findall(r"application/ld\+json", t)),
        }
    return {k: sorted(v) for k, v in data.items()}, seo, len(files)


def collect_guard(root, guard):
    out = {}
    for g in guard:
        base = root / g
        paths = [base] if base.is_file() else (sorted(base.rglob("*.php")) if base.exists() else [])
        for p in paths:
            if any(part in SKIP_DIRS for part in p.parts):
                continue
            lines = []
            for ln in p.read_text(encoding="utf-8", errors="ignore").splitlines():
                s = ln.strip()
                if s and not s.startswith(("//", "#", "/*", "*")):
                    lines.append(s)
            out[str(p.relative_to(root))] = lines
    return out


def cmd_snapshot(a):
    root = Path(a.root).resolve()
    views = a.views or ["resources/views", "public"]
    data, seo, n = collect(root, views)
    snap = {"views": views, "files": n, "data": data, "seo": seo,
            "guard": collect_guard(root, a.guard or ["routes"]), "guard_paths": a.guard or ["routes"]}
    Path(a.out).parent.mkdir(parents=True, exist_ok=True)
    Path(a.out).write_text(json.dumps(snap, indent=1, ensure_ascii=False), encoding="utf-8")
    print(f"Snapshot of {n} view files saved to {a.out}")
    for k in FAIL_KEYS + WARN_KEYS:
        print(f"  {k:<11}{len(data[k])}")
    print(f"  guarded files: {len(snap['guard'])}")


def cmd_diff(a):
    root = Path(a.root).resolve()
    before = json.loads(Path(a.before).read_text(encoding="utf-8"))
    allow = set()
    if a.allow:
        allow = {l.strip() for l in Path(a.allow).read_text(encoding="utf-8").splitlines() if l.strip()}
    data, seo, n = collect(root, before["views"])
    guard_now = collect_guard(root, before["guard_paths"])
    report = {"fail": {}, "warn": {}, "acknowledged": {}, "added": {}}

    def removed_of(key):
        return [x for x in before["data"][key] if x not in set(data[key])]

    for key in FAIL_KEYS + WARN_KEYS:
        gone = removed_of(key)
        ack = [x for x in gone if x in allow]
        gone = [x for x in gone if x not in allow]
        added = [x for x in data[key] if x not in set(before["data"][key])]
        if gone:
            report["fail" if key in FAIL_KEYS else "warn"][key] = gone
        if ack:
            report["acknowledged"][key] = ack
        if added:
            report["added"][key] = len(added)
    for path, lines in before["guard"].items():
        if path not in guard_now:
            report["fail"].setdefault("guard", []).append(f"FILE DELETED: {path}")
            continue
        cur = set(guard_now[path])
        gone = [l for l in lines if l not in cur and l not in allow]
        if gone:
            report["fail"].setdefault("guard", []).extend(f"{path}: {l}" for l in gone)
        extra = [l for l in guard_now[path] if l not in set(lines)]
        if extra:
            report["added"].setdefault("guard", 0)
            report["added"]["guard"] += len(extra)
    for path in guard_now:
        if path not in before["guard"]:
            report["added"].setdefault("guard", 0)
            report["added"]["guard"] += len(guard_now[path])

    lost_seo = [f for f, s in before["seo"].items() if f in seo
                and any(s[k] and not seo[f][k] for k in ("title", "description", "canonical"))]
    if lost_seo:
        report["warn"]["seo_meta_lost"] = lost_seo

    if a.json:
        print(json.dumps(report, indent=1, ensure_ascii=False))
    else:
        print(f"Compared {n} view files against {a.before}\n")
        for label, bucket in (("FAIL (must restore or justify)", "fail"), ("WARN (review)", "warn")):
            if report[bucket]:
                print(f"== {label}")
                for k, items in report[bucket].items():
                    print(f"  [{k}] {len(items)} missing")
                    for x in items[:40]:
                        print(f"     - {x}")
                    if len(items) > 40:
                        print(f"     ... +{len(items) - 40} more")
        if report["acknowledged"]:
            print("== ACKNOWLEDGED via --allow:", {k: len(v) for k, v in report["acknowledged"].items()})
        if report["added"]:
            print("== Added (info):", report["added"])
        if not report["fail"]:
            print("\nPARITY OK: no routes, links, forms, or fields were lost.")
    sys.exit(1 if report["fail"] else 0)


def cmd_colors(a):
    root = Path(a.root).resolve()
    hexes, tw, cssvars = Counter(), Counter(), {}
    patterns = ("*.css", "*.scss", "*.blade.php", "*.html", "*.js", "*.ts", "*.vue", "tailwind.config.*")
    for p in walk(root, ["."], patterns):
        if "public/build" in str(p).replace("\\", "/") or p.name.endswith(".min.css"):
            continue
        try:
            t = p.read_text(encoding="utf-8", errors="ignore")
        except OSError:
            continue
        for h in RE_HEX.findall(t):
            hexes[h.lower()] += 1
        for m in RE_TWCOLOR.finditer(t):
            tw[f"{m.group(1)}-{m.group(2)}"] += 1
        if p.suffix in (".css", ".scss", ".php", ".html"):
            for m in RE_CSSVAR.finditer(t):
                cssvars.setdefault(m.group(1), m.group(2).strip())
    print("Top hex colors:")
    for h, c in hexes.most_common(a.top):
        print(f"  {h:<10}{c}")
    print("\nTop Tailwind palette classes (family-shade):")
    for h, c in tw.most_common(a.top):
        print(f"  {h:<16}{c}")
    print(f"\nCSS custom properties defined ({len(cssvars)}):")
    for k, v in list(cssvars.items())[: a.top * 2]:
        print(f"  {k}: {v}")
    print("\nNext: read tailwind.config.* theme.extend.colors and the main layout to confirm "
          "which of these is the brand accent vs neutral surfaces.")


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    sub = ap.add_subparsers(dest="cmd", required=True)
    s = sub.add_parser("snapshot")
    s.add_argument("root")
    s.add_argument("--out", required=True)
    s.add_argument("--views", action="append", help="dir(s) relative to ROOT; default resources/views and public")
    s.add_argument("--guard", action="append", help="dir(s)/file(s) whose lines must not be removed; default routes")
    s.set_defaults(fn=cmd_snapshot)
    d = sub.add_parser("diff")
    d.add_argument("before")
    d.add_argument("root")
    d.add_argument("--allow")
    d.add_argument("--json", action="store_true")
    d.set_defaults(fn=cmd_diff)
    c = sub.add_parser("colors")
    c.add_argument("root")
    c.add_argument("--top", type=int, default=25)
    c.set_defaults(fn=cmd_colors)
    a = ap.parse_args()
    a.fn(a)


if __name__ == "__main__":
    main()
