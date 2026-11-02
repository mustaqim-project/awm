#!/usr/bin/env python3
"""Ambil semua URL https://cooca.id/blog/(slug) dari sitemap_blog.xml.

Pemakaian:
  python fetch_blog_links.py --out blog_links.json
  python fetch_blog_links.py --from-file sitemap_blog.xml --out blog_links.json
  python fetch_blog_links.py --out blog_links.json --with-titles
"""
import argparse, json, re, sys, urllib.request
import xml.etree.ElementTree as ET

SITEMAP = "https://cooca.id/sitemap_blog.xml"
# Host sengaja diabaikan: sitemap_blog.xml pernah memuat <loc> dengan host lokal
# (http://127.0.0.1:PORT/blog/slug). Yang divalidasi adalah path /blog/(slug),
# lalu URL disusun ulang ke domain kanonis https://cooca.id.
BLOG_RE = re.compile(r"^https?://[^/\s]+/blog/([A-Za-z0-9][A-Za-z0-9_\-]*)/?$")
UA = {"User-Agent": "Mozilla/5.0 (cooca-blog-writer)"}


def get(url):
    req = urllib.request.Request(url, headers=UA)
    with urllib.request.urlopen(req, timeout=20) as r:
        return r.read().decode("utf-8", "replace")


def local(tag):
    return tag.rsplit("}", 1)[-1]


def parse(xml_text, depth=0):
    """Return list of (loc, lastmod). Follow sitemap index children."""
    root = ET.fromstring(xml_text)
    out = []
    if local(root.tag) == "sitemapindex" and depth < 3:
        for sm in root:
            loc = next((c.text.strip() for c in sm if local(c.tag) == "loc" and c.text), None)
            if loc:
                try:
                    out += parse(get(loc), depth + 1)
                except Exception as e:
                    print(f"WARN: gagal membaca {loc}: {e}", file=sys.stderr)
        return out
    for u in root:
        loc = lastmod = None
        for c in u:
            if local(c.tag) == "loc" and c.text:
                loc = c.text.strip()
            elif local(c.tag) == "lastmod" and c.text:
                lastmod = c.text.strip()
        if loc:
            out.append((loc, lastmod))
    return out


def title_of(url):
    try:
        html = get(url)
        m = re.search(r"<title[^>]*>(.*?)</title>", html, re.S | re.I)
        return re.sub(r"\s+", " ", m.group(1)).strip() if m else None
    except Exception:
        return None


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--from-file", help="path sitemap_blog.xml yang sudah disimpan")
    ap.add_argument("--out", default="blog_links.json")
    ap.add_argument("--with-titles", action="store_true")
    a = ap.parse_args()

    xml_text = open(a.from_file, encoding="utf-8").read() if a.from_file else get(SITEMAP)
    seen, links = set(), []
    for loc, lastmod in parse(xml_text):
        m = BLOG_RE.match(loc)
        if not m:
            continue
        slug = m.group(1)
        if slug in seen:
            continue
        seen.add(slug)
        url = f"https://cooca.id/blog/{slug}"
        item = {"url": url, "slug": slug, "lastmod": lastmod,
                "topic_from_slug": slug.replace("-", " ")}
        if a.with_titles:
            item["title"] = title_of(url)
        links.append(item)

    json.dump({"count": len(links), "links": links}, open(a.out, "w", encoding="utf-8"),
              ensure_ascii=False, indent=2)
    print(f"{len(links)} artikel blog ditemukan -> {a.out}")
    if not links:
        print("PERINGATAN: sitemap belum memuat /blog/(slug). Jangan mengarang link.")


if __name__ == "__main__":
    main()
