# scripts/build_keyword_registry.py
import glob
import os
import re
import json

def main():
    files = glob.glob('database/data/articles/*.md')
    existing_items = []
    seen_slugs = set()
    
    for f in sorted(files):
        try:
            content = open(f, encoding='utf-8').read()
        except Exception as e:
            continue
            
        m_slug = re.search(r'slug:\s*["\']?([^"\'\r\n]+)["\']?', content)
        m_title = re.search(r'title:\s*["\']?([^"\'\r\n]+)["\']?', content)
        m_kw = re.search(r'primary_keyword:\s*["\']?([^"\'\r\n]+)["\']?', content)
        m_cat = re.search(r'category:\s*["\']?([^"\'\r\n]+)["\']?', content)
        m_pub = re.search(r'published_at:\s*["\']?([^"\'\r\n]+)["\']?', content)
        
        slug = m_slug.group(1).strip() if m_slug else os.path.basename(f).replace('.md', '')
        # Remove numeric prefix from slug if any
        if re.match(r'^\d+-', slug):
            slug = re.sub(r'^\d+-', '', slug)
            
        title = m_title.group(1).strip() if m_title else ''
        kw = m_kw.group(1).strip() if m_kw else ''
        cat = m_cat.group(1).strip() if m_cat else 'Umum'
        pub = m_pub.group(1).strip() if m_pub else ''
        
        if slug in seen_slugs:
            continue
        seen_slugs.add(slug)
        
        existing_items.append({
            'file': os.path.basename(f),
            'slug': slug,
            'title': title,
            'primary_keyword': kw,
            'category': cat,
            'published_at': pub
        })
        
    reg_path = os.path.join('database', 'data', 'keyword-registry.json')
    with open(reg_path, 'w', encoding='utf-8') as out:
        json.dump(existing_items, out, indent=2, ensure_ascii=False)
        
    print(f"Total articles extracted: {len(existing_items)}")
    print(f"Registry written to: {reg_path}")

if __name__ == '__main__':
    main()
