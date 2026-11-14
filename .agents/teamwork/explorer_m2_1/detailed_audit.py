import re
import os

target_files = [
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

def audit_file(filepath):
    print("=" * 70)
    print(f"AUDITING: {filepath}")
    print("=" * 70)
    
    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        lines = content.splitlines()

    # 1. Extends layout
    extends_match = re.search(r"@extends\(['\"]([^'\"]+)['\"]\)", content)
    layout = extends_match.group(1) if extends_match else "STANDALONE (No @extends)"
    print(f"Layout: {layout}")

    # 2. SEO Sections
    title_match = re.search(r"@section\(['\"]title['\"]\s*,\s*([^)]+)\)", content) or re.search(r"@section\(['\"]title['\"]\)[\r\n]+(.*?)(?:@endsection|@show)", content, re.DOTALL)
    desc_match = re.search(r"@section\(['\"](?:description|meta_description)['\"]\s*,\s*([^)]+)\)", content) or re.search(r"@section\(['\"](?:description|meta_description)['\"]\)[\r\n]+(.*?)(?:@endsection|@show)", content, re.DOTALL)
    canonical_match = re.search(r"@section\(['\"]canonical['\"]\s*,\s*([^)]+)\)", content) or re.search(r"@section\(['\"]canonical['\"]\)[\r\n]+(.*?)(?:@endsection|@show)", content, re.DOTALL)
    og_title_match = re.search(r"@section\(['\"]og_title['\"]", content)
    og_desc_match = re.search(r"@section\(['\"]og_description['\"]", content)
    og_img_match = re.search(r"@section\(['\"]og_image['\"]", content)
    
    print(f"SEO Title defined: {title_match.group(1).strip() if title_match else 'NONE'}")
    print(f"SEO Description defined: {desc_match.group(1).strip() if desc_match else 'NONE'}")
    print(f"SEO Canonical defined: {canonical_match.group(1).strip() if canonical_match else 'NONE'}")
    print(f"OG Sections: og_title={bool(og_title_match)}, og_desc={bool(og_desc_match)}, og_image={bool(og_img_match)}")

    # Schema JSON-LD
    schema_blocks = re.findall(r'<script type=["\']application/ld\+json["\']>(.*?)</script>', content, re.DOTALL)
    print(f"Schema.org JSON-LD blocks: {len(schema_blocks)}")
    for i, sb in enumerate(schema_blocks):
        print(f"  Schema #{i+1} snippet: {sb.strip()[:100]}...")

    # 3. Heading Hierarchy
    heading_matches = []
    # multiline regex for headings
    pattern = re.compile(r'<h([1-6])\b([^>]*)>(.*?)</h\1>', re.DOTALL | re.IGNORECASE)
    for m in pattern.finditer(content):
        # find line number
        start_pos = m.start()
        lno = content.count('\n', 0, start_pos) + 1
        level = int(m.group(1))
        attrs = m.group(2)
        inner = re.sub(r'\s+', ' ', m.group(3)).strip()
        heading_matches.append((lno, level, inner[:60], attrs))

    print(f"\nHeadings Found ({len(heading_matches)}):")
    h1_count = 0
    prev_level = None
    for lno, level, inner, attrs in heading_matches:
        if level == 1: h1_count += 1
        skip = ""
        if prev_level is not None and level > prev_level + 1:
            skip = f" <--- WARNING: SKIP H{prev_level} -> H{level}"
        print(f"  L{lno:4d} [H{level}]: {inner}{skip}")
        prev_level = level
    print(f"H1 Count: {h1_count} (Pass: {h1_count == 1})")

    # 4. Images & Above-the-fold Media
    img_pattern = re.compile(r'<img\b([^>]*)>', re.DOTALL | re.IGNORECASE)
    imgs = []
    for m in img_pattern.finditer(content):
        start_pos = m.start()
        lno = content.count('\n', 0, start_pos) + 1
        attrs = m.group(1)
        w = re.search(r'width=["\']?(\d+|[^"\'\s>]+)', attrs)
        h = re.search(r'height=["\']?(\d+|[^"\'\s>]+)', attrs)
        dec = 'decoding=' in attrs.lower()
        fp = 'fetchpriority=' in attrs.lower()
        loading = 'loading=' in attrs.lower()
        src = re.search(r'src=["\']([^"\']+)["\']', attrs) or re.search(r':src=["\']([^"\']+)["\']', attrs)
        src_val = src.group(1) if src else "unknown"
        imgs.append((lno, bool(w and h), dec, fp, loading, src_val, attrs))

    print(f"\nImages Found ({len(imgs)}):")
    for lno, has_dim, dec, fp, loading, src_val, attrs in imgs:
        issues = []
        if not has_dim: issues.append("NO_DIMENSIONS")
        if not dec: issues.append("NO_DECODING")
        if not (fp or loading): issues.append("NO_LAZY_OR_FETCHPRIORITY")
        status_str = f"[{', '.join(issues)}]" if issues else "[OK]"
        print(f"  L{lno:4d}: {status_str} src={src_val[:50]}")

    # 5. Interactive Touch Targets (Buttons, links)
    # Check for buttons or a tags with explicit small heights or paddings
    touch_issues = []
    btn_pattern = re.compile(r'<(a|button)\b([^>]*)>(.*?)</\1>', re.DOTALL | re.IGNORECASE)
    for m in btn_pattern.finditer(content):
        tag = m.group(1)
        attrs = m.group(2)
        inner = m.group(3)
        start_pos = m.start()
        lno = content.count('\n', 0, start_pos) + 1
        
        # Check class
        class_m = re.search(r'class=["\']([^"\']+)["\']', attrs)
        if class_m:
            cls = class_m.group(1)
            # check small height: h-6, h-7, h-8, h-9 (without min-h-[44px] or p-*)
            small_h = re.search(r'\b(h-[5-8]|h-9)\b', cls)
            small_w = re.search(r'\b(w-[5-8]|w-9)\b', cls)
            has_min44 = 'min-h-[44px]' in cls or 'min-h-11' in cls or 'min-w-[44px]' in cls or 'min-w-11' in cls or 'h-11' in cls or 'h-12' in cls or 'p-3' in cls or 'py-3' in cls
            if small_h and not has_min44:
                touch_issues.append((lno, tag, small_h.group(1), cls[:60]))

    print(f"\nPotential Touch Target Issues (<44px height): {len(touch_issues)}")
    for lno, tag, sh, cls in touch_issues[:8]:
        print(f"  L{lno:4d} <{tag}> class={cls} (found {sh})")

for f in target_files:
    audit_file(f)
