<?php
$dirs = ['lang/id', 'lang/en'];

function find_duplicates($file_path) {
    $code = file_get_contents($file_path);
    $tokens = token_get_all($code);
    $duplicates = [];
    $stack = [];
    $depth = 0;
    $count = count($tokens);
    
    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];
        if (is_array($token)) {
            $id = $token[0];
            $text = $token[1];
            $line = $token[2];
            if ($id === T_CONSTANT_ENCAPSED_STRING || $id === T_LNUMBER) {
                $j = $i + 1;
                while ($j < $count && is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
                    $j++;
                }
                if ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_DOUBLE_ARROW) {
                    $keyName = ($id === T_CONSTANT_ENCAPSED_STRING) ? substr($text, 1, -1) : $text;
                    if (isset($stack[$depth][$keyName])) {
                        $duplicates[] = [
                            'key' => $keyName,
                            'line' => $line,
                            'prev_line' => $stack[$depth][$keyName]
                        ];
                    } else {
                        $stack[$depth][$keyName] = $line;
                    }
                }
            }
        } else {
            if ($token === '[' || $token === '(') {
                $depth++;
                $stack[$depth] = [];
            } elseif ($token === ']' || $token === ')') {
                unset($stack[$depth]);
                $depth = max(0, $depth - 1);
            }
        }
    }
    return $duplicates;
}

$all = [];
foreach ($dirs as $dir) {
    foreach (glob("$dir/*.php") as $f) {
        $d = find_duplicates($f);
        if (!empty($d)) {
            $all[$f] = $d;
        }
    }
}

echo "Files with duplicate keys: " . count($all) . "\n";
foreach ($all as $f => $dups) {
    echo "$f: " . count($dups) . " duplicate keys\n";
    foreach ($dups as $dup) {
        echo "  - key: '{$dup['key']}' at line {$dup['line']} (first defined at {$dup['prev_line']})\n";
    }
}
