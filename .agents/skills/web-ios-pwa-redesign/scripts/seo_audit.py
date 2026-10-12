#!/usr/bin/env python3
"""Per-page SEO / GEO / AEO / PWA audit on rendered HTML. Standard library only.

  seo_audit.py http://127.0.0.1:8000/ http://127.0.0.1:8000/blog page.html ...
  seo_audit.py --json URL

Audits what a crawler or AI fetcher actually receives (server-rendered HTML), so run
it against the running site, not against source templates. For URLs it also checks
robots.txt, sitemap, AI-crawler rules, and the web app manifest once per origin.
Exit code 1 if any check FAILs. It cannot judge content quality or guarantee rankings
or AI citations; treat results as hygiene, not outcomes.
"""
import argparse
import json
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser

UA = "Mozilla/5.0 (compatible; SeoAudit/1.0)"
AI_BOTS = ["GPTBot", "OAI-SearchBot", "ChatGPT-User", "ClaudeBot", "Claude-SearchBot", "Claude-User",
           "PerplexityBot", "Perplexity-User", "Google-Extended", "Applebot-Extended", "CCBot", "Bingbot", "Googlebot"]
REQ = {
    "Organization": ["name", "url"], "WebSite": ["name", "url"],
    "BlogPosting": ["headline", "datePublished", "author", "image"],
    "Article": ["headline", "datePublished", "author", "image"],
    "NewsArticle": ["headline", "datePublished", "author", "image"],
    "BreadcrumbList": ["itemListElement"], "FAQPage": ["mainEntity"],
    "SoftwareApplication": ["name", "applicationCategory"], "Product": ["name"],
}
VOID = {"img", "meta", "link", "br", "hr", "input", "source", "area", "base", "col", "embed", "wbr", "track"}


class Page(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.lang = None
        self.title = ""
        self.metas = {}
        self.links = []
        self.headings = []
        self.imgs = []
        self.anchors = []
        self.ld = []
        self.text = []
        self.tags = set()
        self.counts = {"ul": 0, "ol": 0, "table": 0, "details": 0, "time": 0}
        self._stack = []
        self._head = None
        self._a = None
        self._in_title = False
        self._ld = None

    def handle_starttag(self, tag, attrs):
        a = {k: (v or "") for k, v in attrs}
        self.tags.add(tag)
        if tag in self.counts:
            self.counts[tag] += 1
        if tag == "html":
            self.lang = a.get("lang")
        elif tag == "title":
            self._in_title = True
        elif tag == "meta":
            key = a.get("name") or a.get("property") or a.get("http-equiv")
            if key:
                self.metas[key.lower()] = a.get("content", "")
        elif tag == "link":
            self.links.append(a)
        elif tag == "img":
            self.imgs.append(a)
            if self._a is not None and a.get("alt"):
                self._a["text"] += " " + a["alt"]
        elif tag == "script" and "ld+json" in a.get("type", ""):
            self._ld = []
        elif re.fullmatch(r"h[1-6]", tag):
            self._head = {"level": int(tag[1]), "text": ""}
        elif tag == "a":
            self._a = {"href": a.get("href"), "text": a.get("aria-label", ""), "has_href": "href" in a}
        if tag not in VOID:
            self._stack.append(tag)

    def handle_endtag(self, tag):
        if tag == "title":
            self._in_title = False
        elif tag == "script" and self._ld is not None:
            self.ld.append("".join(self._ld))
            self._ld = None
        elif self._head and tag == f"h{self._head['level']}":
            self._head["text"] = " ".join(self._head["text"].split())
            self.headings.append(self._head)
            self._head = None
        elif tag == "a" and self._a is not None:
            self.anchors.append(self._a)
            self._a = None
        if tag in self._stack:
            while self._stack and self._stack.pop() != tag:
                pass

    def handle_data(self, data):
        if self._in_title:
            self.title += data
        if self._ld is not None:
            self._ld.append(data)
            return
        if any(t in self._stack for t in ("script", "style", "noscript", "template", "svg")):
            return
        if self._head is not None:
            self._head["text"] += data
        if self._a is not None:
            self._a["text"] += data
        self.text.append(data)


def fetch(url, timeout=15):
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept": "text/html,*/*"})
    with urllib.request.urlopen(req, timeout=timeout) as r:
        return r.geturl(), r.read().decode(r.headers.get_content_charset() or "utf-8", "ignore")


def flatten(node):
    out = []
    if isinstance(node, list):
        for n in node:
            out += flatten(n)
    elif isinstance(node, dict):
        if "@graph" in node:
            out += flatten(node["@graph"])
        else:
            out.append(node)
    return out


def types_of(n):
    t = n.get("@type", [])
    return t if isinstance(t, list) else [t]


def norm_text(s):
    return re.sub(r"\s+", " ", s.lower()).strip()


def audit_page(src, html, final_url, min_words):
    p = Page()
    p.feed(html)
    r = []

    def add(status, code, msg):
        r.append((status, code, msg))

    vis = norm_text(" ".join(p.text))
    words = len(vis.split())
    # --- document & crawl basics
    add("ok" if p.lang else "fail", "lang", f"<html lang> = {p.lang!r}" if p.lang else "missing <html lang> (use id / id-ID)")
    t = " ".join(p.title.split())
    if not t:
        add("fail", "title", "missing <title>")
    else:
        add("ok" if 25 <= len(t) <= 60 else "warn", "title", f"{len(t)} chars: {t[:70]}")
    d = p.metas.get("description", "").strip()
    if not d:
        add("fail", "description", "missing meta description")
    else:
        add("ok" if 70 <= len(d) <= 160 else "warn", "description", f"{len(d)} chars")
    canon = next((l.get("href") for l in p.links if "canonical" in l.get("rel", "").lower().split()), None)
    if not canon:
        add("fail", "canonical", "missing rel=canonical")
    else:
        same = True
        if final_url:
            a = urllib.parse.urlparse(canon)
            b = urllib.parse.urlparse(final_url)
            same = a.netloc == b.netloc and a.path.rstrip("/") == b.path.rstrip("/")
        add("ok" if canon.startswith("http") and same else "warn", "canonical",
            canon if same else f"{canon} differs from fetched URL")
    robots = p.metas.get("robots", "").lower()
    add("fail" if "noindex" in robots else "ok", "robots-meta", robots or "none (indexable)")
    vp = p.metas.get("viewport", "")
    add("ok" if "width=device-width" in vp else "fail", "viewport", vp or "missing viewport meta")
    if vp and "viewport-fit=cover" not in vp:
        add("warn", "viewport-fit", "add viewport-fit=cover for safe-area (notch) support in PWA")
    hreflang = [l for l in p.links if l.get("hreflang")]
    add("info", "hreflang", f"{len(hreflang)} alternates" if hreflang else "none (fine for single-language site)")
    # --- structure
    h1 = [h for h in p.headings if h["level"] == 1]
    add("ok" if len(h1) == 1 else ("fail" if not h1 else "warn"), "h1", f"{len(h1)} found" + (f": {h1[0]['text'][:60]}" if h1 else ""))
    jumps = [(a["level"], b["level"]) for a, b in zip(p.headings, p.headings[1:]) if b["level"] - a["level"] > 1]
    add("warn" if jumps else "ok", "heading-order", f"level skips: {jumps[:3]}" if jumps else "no skipped levels")
    add("ok" if "main" in p.tags else "warn", "landmarks", "main present" if "main" in p.tags else "missing <main>")
    add("ok" if words >= min_words else "warn", "content-depth", f"{words} visible words (min {min_words})")
    # --- social
    for k in ("og:title", "og:description", "og:image", "og:type", "twitter:card"):
        if not p.metas.get(k):
            add("warn", k, "missing")
    # --- images & links
    no_alt = [i for i in p.imgs if "alt" not in i]
    no_dim = [i for i in p.imgs if not (i.get("width") and i.get("height"))]
    add("fail" if no_alt else "ok", "img-alt", f"{len(no_alt)}/{len(p.imgs)} images without alt attribute (alt=\"\" is fine for decorative)")
    add("warn" if no_dim else "ok", "img-size", f"{len(no_dim)}/{len(p.imgs)} images without width/height (CLS risk)")
    bad = [a for a in p.anchors if a["has_href"] and (a["href"] in ("", "#") or str(a["href"]).startswith("javascript:"))]
    add("warn" if bad else "ok", "dead-links", f"{len(bad)} anchors with empty/#/javascript: href")
    noname = [a for a in p.anchors if not " ".join(a["text"].split())]
    add("warn" if noname else "ok", "link-names", f"{len(noname)} links without text or aria-label")
    # --- structured data
    nodes = []
    for raw in p.ld:
        try:
            nodes += flatten(json.loads(raw))
        except json.JSONDecodeError as e:
            add("fail", "jsonld", f"invalid JSON-LD: {e.msg} at char {e.pos}")
    seen = sorted({ty for n in nodes for ty in types_of(n)})
    add("ok" if seen else "warn", "jsonld-types", ", ".join(seen) or "no JSON-LD")
    for n in nodes:
        for ty in types_of(n):
            miss = [k for k in REQ.get(ty, []) if not n.get(k)]
            if miss:
                add("warn", f"jsonld:{ty}", f"missing {', '.join(miss)}")
            if ty in ("BlogPosting", "Article", "NewsArticle"):
                if not n.get("dateModified"):
                    add("warn", f"jsonld:{ty}", "missing dateModified (freshness signal)")
                if len(str(n.get("headline", ""))) > 110:
                    add("warn", f"jsonld:{ty}", "headline > 110 chars")
            if ty == "FAQPage":
                qs = n.get("mainEntity", [])
                qs = qs if isinstance(qs, list) else [qs]
                hidden = [q.get("name", "")[:50] for q in qs if norm_text(str(q.get("name", ""))) not in vis]
                add("fail" if hidden else "ok", "faq-visible",
                    f"FAQ questions not found in visible text (markup must match content): {hidden[:3]}" if hidden
                    else f"{len(qs)} FAQ questions match visible text")
            if ty in ("AggregateRating", "Review") or n.get("aggregateRating"):
                add("warn", "jsonld:rating", "rating markup present: must reflect real, visible, verifiable reviews")
    # --- AEO / GEO signals (informational)
    qh = [h for h in p.headings if h["level"] in (2, 3) and h["text"].endswith("?")]
    add("info", "aeo-question-headings", f"{len(qh)} question-style H2/H3")
    add("info", "aeo-structure", f"lists={p.counts['ul'] + p.counts['ol']} tables={p.counts['table']} details={p.counts['details']} time={p.counts['time']}")
    # --- PWA
    add("ok" if any("manifest" in l.get("rel", "") for l in p.links) else "warn", "pwa-manifest", "manifest linked" if any("manifest" in l.get("rel", "") for l in p.links) else "no manifest link")
    add("ok" if p.metas.get("theme-color") else "warn", "theme-color", p.metas.get("theme-color", "missing"))
    add("ok" if any("apple-touch-icon" in l.get("rel", "") for l in p.links) else "warn", "apple-touch-icon", "present" if any("apple-touch-icon" in l.get("rel", "") for l in p.links) else "missing")
    return r, {"words": words, "title": t, "canonical": canon}


def audit_origin(origin):
    r = []
    try:
        _, robots = fetch(origin + "/robots.txt")
        r.append(("ok", "robots.txt", "found"))
        groups, cur, uas = {}, [], []
        for line in robots.splitlines():
            line = line.split("#")[0].strip()
            if not line or ":" not in line:
                continue
            k, v = [x.strip() for x in line.split(":", 1)]
            if k.lower() == "user-agent":
                if cur and cur[-1] != "ua":
                    uas = []
                uas.append(v.lower())
                cur = cur + ["ua"] if not cur or cur[-1] != "ua" else cur
                groups.setdefault(v.lower(), [])
            elif k.lower() in ("allow", "disallow"):
                cur = cur + ["rule"]
                for u in uas:
                    groups.setdefault(u, []).append((k.lower(), v))
        blocked, allowed = [], []
        for b in AI_BOTS:
            rules = groups.get(b.lower(), groups.get("*", []))
            full_block = any(k == "disallow" and v == "/" for k, v in rules) and not any(k == "allow" and v == "/" for k, v in rules)
            (blocked if full_block else allowed).append(b)
        r.append(("info", "ai-crawlers", f"blocked (approx): {', '.join(blocked) or 'none'}"))
        r.append(("ok" if re.search(r"(?im)^sitemap:", robots) else "warn", "robots-sitemap", "Sitemap: line present" if re.search(r"(?im)^sitemap:", robots) else "no Sitemap: line"))
    except (urllib.error.URLError, OSError) as e:
        r.append(("fail", "robots.txt", f"not reachable: {e}"))
    try:
        _, sm = fetch(origin + "/sitemap.xml")
        n = len(re.findall(r"<loc>", sm))
        r.append(("ok" if n else "warn", "sitemap.xml", f"{n} URLs"))
    except (urllib.error.URLError, OSError) as e:
        r.append(("warn", "sitemap.xml", f"not reachable at /sitemap.xml: {e}"))
    return r


def audit_manifest(url):
    r = []
    try:
        _, body = fetch(url)
        m = json.loads(body)
        miss = [k for k in ("name", "short_name", "start_url", "display", "icons", "theme_color", "background_color") if not m.get(k)]
        r.append(("fail" if miss else "ok", "manifest", f"missing {miss}" if miss else "required members present"))
        sizes = {i.get("sizes") for i in m.get("icons", [])}
        r.append(("ok" if {"192x192", "512x512"} <= sizes else "fail", "manifest-icons", f"sizes: {sorted(s for s in sizes if s)}"))
        r.append(("ok" if any("maskable" in i.get("purpose", "") for i in m.get("icons", [])) else "warn", "manifest-maskable", "maskable icon present" if any("maskable" in i.get("purpose", "") for i in m.get("icons", [])) else "no maskable icon"))
        r.append(("ok" if m.get("display") in ("standalone", "fullscreen", "minimal-ui") else "warn", "manifest-display", str(m.get("display"))))
    except Exception as e:  # noqa: BLE001
        r.append(("fail", "manifest", f"cannot load/parse {url}: {e}"))
    return r


ICON = {"ok": "✔", "warn": "⚠", "fail": "✖", "info": "·"}


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("targets", nargs="+", help="URLs or local .html files")
    ap.add_argument("--json", action="store_true")
    ap.add_argument("--min-words", type=int, default=250)
    a = ap.parse_args()
    results, origins, fail = [], set(), False
    for tgt in a.targets:
        try:
            if re.match(r"https?://", tgt):
                final, html = fetch(tgt)
                origins.add("{0.scheme}://{0.netloc}".format(urllib.parse.urlparse(final)))
            else:
                final, html = None, open(tgt, encoding="utf-8", errors="ignore").read()
        except Exception as e:  # noqa: BLE001
            results.append({"target": tgt, "checks": [("fail", "fetch", str(e))]})
            continue
        checks, meta = audit_page(tgt, html, final, a.min_words)
        if final:
            m = re.search(r"""<link[^>]+rel=["']manifest["'][^>]*href=["']([^"']+)["']""", html, re.I) or \
                re.search(r"""<link[^>]+href=["']([^"']+)["'][^>]*rel=["']manifest["']""", html, re.I)
            if m and not any(x.get("manifest_checked") for x in results):
                checks += audit_manifest(urllib.parse.urljoin(final, m.group(1)))
                results.append({"manifest_checked": True, "target": "(manifest)", "checks": []})
        results.append({"target": tgt, "meta": meta, "checks": checks})
    for o in sorted(origins):
        results.append({"target": o + " (site-level)", "checks": audit_origin(o)})
    for x in results:
        if any(c[0] == "fail" for c in x["checks"]):
            fail = True
    if a.json:
        print(json.dumps([x for x in results if x["checks"]], indent=1, ensure_ascii=False))
    else:
        for x in results:
            if not x["checks"]:
                continue
            c = {k: sum(1 for s in x["checks"] if s[0] == k) for k in ICON}
            print(f"\n{x['target']}   ✔{c['ok']} ⚠{c['warn']} ✖{c['fail']}")
            for s, code, msg in x["checks"]:
                if s != "ok" or code in ("title", "description", "h1", "jsonld-types"):
                    print(f"  {ICON[s]} {code:<22}{msg}")
    sys.exit(1 if fail else 0)


if __name__ == "__main__":
    main()
