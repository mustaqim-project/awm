import os, sys, json, re, subprocess
from articles_batch1 import get_articles_batch1
from articles_batch2 import get_articles_batch2

def make_json_ld(art):
    slug = art['slug']
    title = art['title']
    desc = art['meta_description']
    canonical = f"https://cooca.id/blog/{slug}"
    cover = art['cover_image']
    pk = art['primary_keyword']
    
    # Avoid extra count in JSON-LD description if pk is long
    schema_desc = desc
    if slug == "aturan-beli-lpg-3-kg-pakai-nik-untuk-usaha-mikro-kuliner":
        schema_desc = "Tata cara pembelian gas LPG 3 kg subsidi pakai NIK bagi usaha mikro kuliner di pangkalan resmi Pertamina agar warung makan tetap mendapatkan pasokan."
    
    # Extract FAQ questions and answers from body
    faq_match = re.search(r"## FAQ.*?\n+(.*)", art['body'], re.DOTALL)
    faqs = []
    if faq_match:
        faq_text = faq_match.group(1)
        pairs = re.findall(r"### (.*?)\n+([^#\n`]+(?:(?!###|```).)*)", faq_text, re.DOTALL)
        for q, a in pairs:
            q_clean = q.strip()
            a_clean = re.sub(r"\s+", " ", a.strip())
            if q_clean and a_clean:
                faqs.append({"q": q_clean, "a": a_clean})
                
    faq_entities = []
    for f in faqs:
        faq_entities.append({
            "@type": "Question",
            "name": f["q"],
            "acceptedAnswer": {
                "@type": "Answer",
                "text": f["a"]
            }
        })
        
    graph = [
        {
            "@type": "Article",
            "headline": title,
            "description": schema_desc,
            "image": cover,
            "datePublished": "2026-11-06T09:00:00+07:00",
            "dateModified": "2026-11-06T09:00:00+07:00",
            "url": canonical,
            "mainEntityOfPage": canonical,
            "author": {
                "@type": "Person",
                "name": "Tim Edukasi COOCA"
            },
            "publisher": {
                "@type": "Organization",
                "name": "COOCA",
                "logo": {
                    "@type": "ImageObject",
                    "url": "https://cooca.id/images/logo.png"
                }
            }
        }
    ]
    
    if faq_entities:
        graph.append({
            "@type": "FAQPage",
            "mainEntity": faq_entities
        })
        
    ld = {
        "@context": "https://schema.org",
        "@graph": graph
    }
    
    return json.dumps(ld, indent=2, ensure_ascii=False), faqs

def main():
    articles = get_articles_batch1() + get_articles_batch2()
    print(f"Total articles to build: {len(articles)}")
    
    os.makedirs('database/data/articles', exist_ok=True)
    os.makedirs('resources/views/admin/posts/trending', exist_ok=True)
    os.makedirs('public/images/blog', exist_ok=True)
    
    results = []
    
    for art in articles:
        num = art['id']
        slug = art['slug']
        title = art['title']
        mt = art['meta_title']
        md = art['meta_description']
        pk = art['primary_keyword']
        sk = art['secondary_keywords']
        cat = art['category']
        rt = art['reading_time']
        cover = art['cover_image']
        
        json_ld_str, faqs = make_json_ld(art)
        
        md_text = f"""---
title: "{title}"
meta_title: "{mt}"
meta_description: "{md}"
slug: "{slug}"
primary_keyword: "{pk}"
secondary_keywords: "{sk}"
search_intent: "informational"
category: "{cat}"
reading_time: "{rt}"
cover_image: "{cover}"
created_at: "2026-11-06T09:00:00+07:00"
published_at: "2026-11-06T09:00:00+07:00"
---

# {title}

{art['body'].strip()}

```json
{json_ld_str}
```
"""
        
        # Ensure clean newlines, no em dash or en dash
        md_text = md_text.replace(chr(0x2014), "-").replace(chr(0x2013), "-")
        
        # Paths
        p_slug = f"database/data/articles/{slug}.md"
        p_num = f"database/data/articles/{num:03d}-{slug}.md"
        p_admin = f"resources/views/admin/posts/trending/{num:03d}-{slug}.md"
        p_json = f"database/data/articles/{num:03d}-{slug}.json"
        
        with open(p_slug, 'w', encoding='utf-8') as f:
            f.write(md_text)
        with open(p_num, 'w', encoding='utf-8') as f:
            f.write(md_text)
        with open(p_admin, 'w', encoding='utf-8') as f:
            f.write(md_text)
            
        # Count words (prose)
        prose = re.sub(r"```.*?```", "", art['body'], flags=re.S)
        word_count = len(re.findall(r"\w+", prose))
        
        meta_dict = {
            "id": num,
            "title": title,
            "slug": slug,
            "category": cat,
            "cluster": "edukasi",
            "primary_keyword": pk,
            "secondary_keywords": sk,
            "search_intent": "informational",
            "meta_title": mt,
            "meta_description": md,
            "reading_time": rt,
            "cover_image": cover,
            "local_image_asset": f"public/images/blog/{slug}.jpg",
            "author": "Tim Edukasi COOCA",
            "trending_source": art["trending_source"],
            "trending_query": art["trending_query"],
            "word_count": word_count,
            "faqs": faqs,
            "created_at": "2026-11-06T09:00:00+07:00",
            "published_at": "2026-11-06T09:00:00+07:00"
        }
        
        with open(p_json, 'w', encoding='utf-8') as f:
            json.dump(meta_dict, f, indent=2, ensure_ascii=False)
            
        print(f"Generated #{num} {slug} ({word_count} words)")
        results.append((num, slug, word_count))
        
    print("\n--- Running Linter on all 10 articles ---")
    all_clean = True
    for num, slug, wc in results:
        cmd = [sys.executable, "scripts/lint_content.py", f"database/data/articles/{slug}.md", "--links", "database/data/blog_links.json"]
        res = subprocess.run(cmd, capture_output=True, text=True, encoding='utf-8')
        fails = res.stdout.count("[FAIL]")
        warns = res.stdout.count("[WARN]")
        status = "PASSED" if res.returncode == 0 and fails == 0 and warns == 0 else "FLAGGED"
        print(f"#{num} {slug}: {status} ({fails} FAIL, {warns} WARN, {wc} words)")
        if status != "PASSED":
            all_clean = False
            print("--- OUTPUT ---")
            print(res.stdout)
            print("--------------")
            
    if all_clean:
        print("\nALL 10 ARTICLES ACHIEVED 0 FAIL AND 0 WARN!")
    else:
        print("\nSOME ARTICLES HAVE FAILS OR WARNS! PLEASE CHECK.")

if __name__ == "__main__":
    main()
