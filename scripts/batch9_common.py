# scripts/batch9_common.py
import json, re

def make_json_ld(art):
    slug = art['slug']
    title = art['title']
    desc = art['meta_description']
    canonical = f"https://cooca.id/blog/{slug}"
    cover = art['cover_image']
    pub_date = art.get('published_at', '2026-11-12T06:00:00+07:00')
    
    faq_match = re.search(r"## FAQ.*?\n+(.*)", art['body'], re.DOTALL)
    if not faq_match:
        faq_match = re.search(r"## Pertanyaan Seputar.*?\n+(.*)", art['body'], re.DOTALL)
        
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
            "description": desc,
            "image": cover,
            "datePublished": pub_date,
            "dateModified": pub_date,
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
