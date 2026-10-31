import os
import re
import sys
import subprocess
import json

def get_referenced_keys(directories):
    pattern = re.compile(r"""(?:__\s*\(\s*['"]([^'"]+)['"]|@lang\s*\(\s*['"]([^'"]+)['"])""")
    key_locations = {}
    for dirpath in directories:
        for root, _, files in os.walk(dirpath):
            for f in files:
                if f.endswith('.blade.php'):
                    filepath = os.path.join(root, f)
                    with open(filepath, 'r', encoding='utf-8', errors='ignore') as fp:
                        content = fp.read()
                    for m in pattern.finditer(content):
                        k = m.group(1) or m.group(2)
                        k = k.strip()
                        if k and not k.startswith('$') and not '{' in k:
                            if k not in key_locations:
                                key_locations[k] = []
                            line_no = content[:m.start()].count('\n') + 1
                            key_locations[k].append(f"{filepath}:{line_no}")
    return key_locations

def main():
    dirs = ['resources/views/app', 'resources/views/admin']
    referenced = get_referenced_keys(dirs)

    keys_list = list(referenced.keys())
    temp_json = '.agents/teamwork/auditor_2/referenced_keys.json'
    with open(temp_json, 'w', encoding='utf-8') as f:
        json.dump(keys_list, f)

    php_script = """
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
    $kernel->bootstrap();

    $translator = app('translator');
    $keys = json_decode(file_get_contents('.agents/teamwork/auditor_2/referenced_keys.json'), true);

    $missing_id = [];
    $missing_en = [];

    foreach ($keys as $k) {
        $translator->setLocale('id');
        if (!$translator->has($k, 'id')) {
            $missing_id[] = $k;
        }
        $translator->setLocale('en');
        if (!$translator->has($k, 'en')) {
            $missing_en[] = $k;
        }
    }

    echo json_encode([
        'total' => count($keys),
        'missing_id_count' => count($missing_id),
        'missing_en_count' => count($missing_en),
        'missing_id' => $missing_id,
        'missing_en' => $missing_en,
    ]);
    """

    res = subprocess.run(["php", "-r", php_script], capture_output=True, text=True, encoding='utf-8', errors='replace')
    if res.returncode != 0:
        print("PHP resolution script failed:", res.stderr)
        sys.exit(1)

    data = json.loads(res.stdout)
    print(f"Total distinct keys checked: {data['total']}")
    print(f"Missing in ID: {data['missing_id_count']}, Missing in EN: {data['missing_en_count']}")

    print("\n--- ALL UNRESOLVED KEYS ---")
    all_missing = sorted(list(set(data['missing_id'] + data['missing_en'])))
    for k in all_missing:
        locs = referenced.get(k, [])
        in_id = k not in data['missing_id']
        in_en = k not in data['missing_en']
        print(f"Key: '{k}' | in_id: {in_id} | in_en: {in_en}")
        for loc in locs[:3]:
            print(f"   -> {loc}")

if __name__ == '__main__':
    main()
