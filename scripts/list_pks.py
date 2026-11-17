import json

with open("database/data/articles/keyword-research-2026-11-17.json", "r", encoding="utf-8") as f:
    topics = json.load(f)["topics"]

print("Listing 40 topics and their primary keywords:")
for t in topics:
    print(f"ID {t['id']:02d}: PK='{t['primary_keyword']}' | Title='{t['title']}' | MT='{t['meta_title']}'")
