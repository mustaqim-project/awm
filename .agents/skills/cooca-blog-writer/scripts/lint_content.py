#!/usr/bin/env python3
"""Lint artikel blog Cooca.

Pemakaian: python lint_content.py artikel.md --links blog_links.json [--min-words 1200] [--min-links 3]
Exit code 1 jika ada FAIL.
"""
import argparse, json, re, sys

EM = chr(0x2014)
EN = chr(0x2013)
URL_RE = re.compile(r"https?://[^\s)\]>\"'<]+")
MD_LINK_RE = re.compile(r"\[[^\]]*\]\(([^)\s]+)[^)]*\)")
HREF_RE = re.compile(r"href=[\"']([^\"']+)[\"']", re.I)
BLOG_RE = re.compile(r"^https?://(?:www\.)?cooca\.id/blog/([A-Za-z0-9][A-Za-z0-9_\-]*)/?$")


def norm(u):
    u = u.strip().rstrip("/")
    if u.startswith("/blog/"):
        u = "https://cooca.id" + u
    return u.replace("://www.", "://")


def split_frontmatter(text):
    m = re.match(r"^---\n(.*?)\n---\n?(.*)$", text, re.S)
    if not m:
        return {}, text
    fm = {}
    for line in m.group(1).splitlines():
        if ":" in line:
            k, v = line.split(":", 1)
            fm[k.strip()] = v.strip().strip("\"'")
    return fm, m.group(2)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("file")
    ap.add_argument("--links", help="blog_links.json dari fetch_blog_links.py")
    ap.add_argument("--min-words", type=int, default=1200)
    ap.add_argument("--min-links", type=int, default=3)
    a = ap.parse_args()

    text = open(a.file, encoding="utf-8").read()
    fm, body = split_frontmatter(text)
    res = []

    def add(level, msg):
        res.append((level, msg))

    # 1. Dash
    n_em, n_en = text.count(EM), text.count(EN)
    add("FAIL" if n_em else "PASS", f"em dash: {n_em} ditemukan")
    add("FAIL" if n_en else "PASS", f"en dash: {n_en} ditemukan")

    # 2. Links
    allowed = set()
    if a.links:
        data = json.load(open(a.links, encoding="utf-8"))
        allowed = {norm(l["url"]) for l in data.get("links", [])}
    found = [norm(x) for x in MD_LINK_RE.findall(text) + HREF_RE.findall(text)]
    found += [norm(x) for x in URL_RE.findall(text)]
    self_url = norm("https://cooca.id/blog/" + fm.get("slug", "")) if fm.get("slug") else None
    used_internal, bad = set(), set()
    for u in found:
        if u.startswith("#") or u.startswith("mailto:"):
            continue
        if u == self_url:
            continue  # URL kanonis artikel ini di JSON-LD
        if u in ("https://schema.org", "http://schema.org"):
            continue  # Context schema.org di JSON-LD
        if "images.unsplash.com" in u or "unsplash.com" in u or u.startswith("https://cooca.id/storage") or u.startswith("https://cooca.id/images"):
            continue  # Valid cover / article asset image
        if BLOG_RE.match(u) and u in allowed:
            used_internal.add(u)
        else:
            bad.add(u)
    add("FAIL" if bad else "PASS", f"link tidak diizinkan: {sorted(bad) if bad else 'tidak ada'}")
    if not allowed:
        add("WARN", "daftar blog kosong: artikel tanpa internal link. Pastikan ada bagian 'Rencana internal link' dan jelaskan ke user.")
    else:
        need = min(a.min_links, len(allowed))
        add("PASS" if len(used_internal) >= need else "FAIL",
            f"internal link unik: {len(used_internal)} (minimal {need})")

    # 3. Frontmatter & meta
    for k in ("title", "meta_title", "meta_description", "slug", "primary_keyword"):
        add("PASS" if fm.get(k) else "FAIL", f"frontmatter '{k}' {'ada' if fm.get(k) else 'hilang'}")
    mt, md = fm.get("meta_title", ""), fm.get("meta_description", "")
    if mt:
        add("PASS" if len(mt) <= 60 else "FAIL", f"meta_title {len(mt)} karakter (maks 60)")
    if md:
        add("PASS" if 120 <= len(md) <= 160 else "WARN", f"meta_description {len(md)} karakter (120-160)")
    slug = fm.get("slug", "")
    if slug:
        add("PASS" if re.fullmatch(r"[a-z0-9]+(-[a-z0-9]+)*", slug) else "FAIL", f"slug '{slug}' format valid")

    # 4. Heading
    h1 = re.findall(r"^# (?!#)(.+)$", body, re.M)
    h2 = re.findall(r"^## (?!#)(.+)$", body, re.M)
    add("PASS" if len(h1) == 1 else "FAIL", f"jumlah H1: {len(h1)} (harus 1)")
    add("PASS" if 4 <= len(h2) <= 10 else "WARN", f"jumlah H2: {len(h2)} (4-8 ideal)")
    faq = any(re.search(r"faq|pertanyaan", h, re.I) for h in h2)
    add("PASS" if faq else "FAIL", "bagian FAQ " + ("ada" if faq else "tidak ada"))

    # 5. Keyword
    pk = fm.get("primary_keyword", "").lower()
    if pk:
        words = re.findall(r"\w+", re.sub(r"```.*?```", "", body, flags=re.S))
        first100 = " ".join(words[:100]).lower()
        add("PASS" if pk in first100 else "FAIL", "primary keyword di 100 kata pertama")
        add("PASS" if h1 and pk in h1[0].lower() else "FAIL", "primary keyword di H1")
        add("PASS" if mt and pk in mt.lower() else "WARN", "primary keyword di meta_title")
        add("PASS" if md and pk in md.lower() else "WARN", "primary keyword di meta_description")
        add("PASS" if any(pk in h.lower() for h in h2) else "WARN", "primary keyword di salah satu H2")
        total = len(words)
        cnt = body.lower().count(pk)
        dens = (cnt * len(pk.split())) / total * 100 if total else 0
        add("PASS" if 0.4 <= dens <= 2.5 else "WARN", f"kepadatan keyword {dens:.2f}% ({cnt}x)")

    # 6. Panjang
    prose = re.sub(r"```.*?```", "", body, flags=re.S)
    wc = len(re.findall(r"\w+", prose))
    add("PASS" if wc >= a.min_words else "WARN", f"jumlah kata {wc} (minimal {a.min_words})")

    # 7. Pola AI umum
    cliches = ["di era digital saat ini", "tidak dapat dipungkiri", "dalam dunia bisnis yang dinamis"]
    hit = [c for c in cliches if c in text.lower()]
    add("WARN" if hit else "PASS", f"klise pembuka: {hit if hit else 'tidak ada'}")

    order = {"FAIL": 0, "WARN": 1, "PASS": 2}
    for lvl, msg in sorted(res, key=lambda x: order[x[0]]):
        print(f"[{lvl}] {msg}")
    fails = sum(1 for l, _ in res if l == "FAIL")
    print(f"\nRingkasan: {fails} FAIL, {sum(1 for l,_ in res if l=='WARN')} WARN")
    sys.exit(1 if fails else 0)


if __name__ == "__main__":
    main()
