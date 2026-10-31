import os, re

files_to_sample = [
    'resources/views/admin/subscriptions/show.blade.php',
    'resources/views/admin/businesses/index.blade.php',
    'resources/views/admin/templates/create.blade.php',
    'resources/views/admin/word_filters/index.blade.php',
    'resources/views/app/ai-studio/create.blade.php',
    'resources/views/app/warehouse/index.blade.php',
    'resources/views/app/tax/index.blade.php',
]

for p in files_to_sample:
    print(f"\n==================== {p} ====================")
    with open(p, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
    for idx, line in enumerate(lines, 1):
        # look for visible text inside tags like <p>, <span>, <div>, <label> that contains Indonesian
        # but does NOT have __() or @lang()
        stripped = line.strip()
        if re.search(r'<(p|span|div|label|small|li|td|th)\b[^>]*>([^<]+)</\1>', stripped):
            m = re.search(r'<(p|span|div|label|small|li|td|th)\b[^>]*>([^<]+)</\1>', stripped)
            text = m.group(2).strip()
            if not re.search(r'__\(|@lang\(|\{\{|\{!!', text):
                words = re.sub(r'[^a-zA-Z\s]', ' ', text.lower()).split()
                if len(words) >= 2 and any(w in words for w in ['yang', 'dan', 'untuk', 'dengan', 'tidak', 'pada', 'pilih', 'anda', 'wajib', 'semua', 'kategori']):
                    print(f"  Line {idx}: {stripped[:100]}")
