import subprocess
import json

php_code = r'''
$files = ['common', 'pos', 'sales', 'purchasing', 'inventory', 'finance', 'settings', 'ai', 'storefront'];
$out = [];
foreach ($files as $f) {
    $arr = @include "lang/id/$f.php";
    $keys = array_keys(is_array($arr) ? $arr : []);
    $first3 = array_slice($keys, 0, 5);
    $out[$f] = [
        'count' => count($arr ?: []),
        'top_level_sample' => $first3,
        'has_nested' => is_array($arr ? reset($arr) : null),
        'sample_nested' => is_array($arr ? reset($arr) : null) ? array_keys(reset($arr)) : null
    ];
}
echo json_encode($out, JSON_PRETTY_PRINT);
'''

p = subprocess.run(['php', '-r', php_code], capture_output=True, text=True, encoding='utf-8')
print(p.stdout)
