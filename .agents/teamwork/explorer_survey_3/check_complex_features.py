import re

complex_files = [
    r"resources/views/app/landing_page/edit.blade.php",
    r"resources/views/app/import/index.blade.php",
    r"resources/views/app/ai/partials/virtual_office_canvas.blade.php",
    r"resources/views/app/pos/terminal.blade.php",
    r"resources/views/app/social_media/inbox.blade.php",
    r"resources/views/app/labor-machines/index.blade.php",
    r"resources/views/app/master-data/index.blade.php"
]

for cf in complex_files:
    with open(cf, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    print(f"\n==============================")
    print(f"FILE: {cf}")
    print(f"Lines: {len(content.splitlines())}")
    
    # Alpine states
    x_data = re.findall(r'x-data\s*=\s*([^\n>]+)', content)
    print(f"Alpine x-data declarations ({len(x_data)}):")
    for xd in x_data[:3]:
        print(f"  - {xd[:80]}")
        
    # Attribute bindings with text: :title, :placeholder, :aria-label
    attr_bindings = re.findall(r':(title|placeholder|aria-label)\s*=\s*([^\n>]+)', content)
    print(f"Dynamic attribute bindings count: {len(attr_bindings)}")
    for ab in attr_bindings[:3]:
        print(f"  - :{ab[0]}={ab[1][:60]}")
        
    # Blade conditionals with auth / bypass
    bypasses = re.findall(r'bypass[^\n;]+', content, re.I)
    if bypasses:
        print(f"Bypass / testing mentions: {len(bypasses)}")
        for bp in bypasses[:2]:
            print(f"  - {bp[:80]}")
            
    # Number / date formatting
    formats = re.findall(r'(number_format|formatRupiah|translatedFormat)[^\n;)]+\)', content)
    print(f"Formatting calls: {len(formats)}")
