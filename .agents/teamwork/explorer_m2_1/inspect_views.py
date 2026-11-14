import re
import os

files = [
    'resources/views/landing.blade.php',
    'resources/views/public/business_landing.blade.php',
    'resources/views/public/about.blade.php',
    'resources/views/public/demo.blade.php',
    'resources/views/public/pricing.blade.php',
    'resources/views/public/privacy.blade.php',
    'resources/views/public/security.blade.php',
    'resources/views/public/sitemap.blade.php',
    'resources/views/public/support.blade.php',
    'resources/views/public/terms.blade.php',
]

def analyze_file(path):
    print(f"\n=======================================================")
    print(f"ANALYSIS: {path}")
    print(f"=======================================================")
    if not os.path.exists(path):
        print("FILE NOT FOUND!")
        return

    with open(path, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()

    # 1. Headings
    headings = []
    for idx, line in enumerate(lines):
        matches = re.finditer(r'<h([1-6])\b([^>]*)>', line, re.IGNORECASE)
        for m in matches:
            headings.append((idx + 1, int(m.group(1)), m.group(0)))

    print(f"\n--- HEADINGS ({len(headings)}) ---")
    prev_level = None
    for lno, level, tag in headings:
        skip_str = ""
        if prev_level is not None:
            if level > prev_level + 1:
                skip_str = f" [SKIP! H{prev_level} -> H{level}]"
        elif level > 1:
            skip_str = f" [FIRST HEADING IS H{level}, NOT H1!]"
        print(f"  Line {lno:4d}: H{level}{skip_str} | {tag[:80]}")
        prev_level = level

    # 2. Images & Media
    print(f"\n--- IMAGES & MEDIA ---")
    img_matches = []
    for idx, line in enumerate(lines):
        if '<img' in line.lower():
            # grab surrounding context
            snippet = "".join(lines[idx:min(idx+5, len(lines))])
            tag_match = re.search(r'<img\b[^>]*>', snippet, re.DOTALL | re.IGNORECASE)
            if tag_match:
                img_tag = tag_match.group(0)
                has_w = 'width=' in img_tag.lower()
                has_h = 'height=' in img_tag.lower()
                has_dec = 'decoding=' in img_tag.lower()
                has_fp = 'fetchpriority=' in img_tag.lower()
                has_loading = 'loading=' in img_tag.lower()
                img_matches.append((idx + 1, has_w, has_h, has_dec, has_fp, has_loading, img_tag))

    print(f"Total <img> tags: {len(img_matches)}")
    for lno, w, h, dec, fp, loading, tag in img_matches[:15]:
        status = []
        if not (w and h): status.append("MISSING_DIMENSIONS")
        if not dec: status.append("NO_DECODING")
        if not (fp or loading): status.append("NO_PRIORITY_OR_LAZY")
        print(f"  Line {lno:4d}: [{', '.join(status) if status else 'OPTIMIZED'}] {tag[:100].replace('\n', ' ')}")

    # 3. SEO tags
    print(f"\n--- SEO & STRUCTURED DATA ---")
    content = "".join(lines)
    has_canonical = 'canonical' in content.lower()
    has_meta_desc = 'meta_description' in content.lower() or 'name="description"' in content.lower() or 'name=\'description\'' in content.lower()
    has_og = 'og:' in content.lower() or 'seo_og' in content.lower() or 'bizogimage' in content.lower()
    has_schema = 'application/ld+json' in content.lower()
    has_seo_partial = 'public.partials.seo' in content

    print(f"  Includes public.partials.seo: {has_seo_partial}")
    print(f"  Canonical URL present: {has_canonical}")
    print(f"  Meta Description present: {has_meta_desc}")
    print(f"  OpenGraph tags present: {has_og}")
    print(f"  Schema.org JSON-LD present: {has_schema}")

analyze_file('resources/views/landing.blade.php')
for f in files[1:]:
    analyze_file(f)
