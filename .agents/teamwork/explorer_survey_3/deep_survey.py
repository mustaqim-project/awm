import os
import sys
import json
import subprocess
import re
from collections import defaultdict, Counter

APP_VIEWS_DIR = r"c:\laragon\www\cooca_core\resources\views\app"
LANG_DIR = r"c:\laragon\www\cooca_core\lang"
AUDIT_SCRIPT = r"c:\laragon\www\cooca_core\scratch\deep_audit_v2.py"

def flatten_dict(d, parent_key=''):
    items = []
    if isinstance(d, dict):
        for k, v in d.items():
            new_key = f"{parent_key}.{k}" if parent_key else str(k)
            if isinstance(v, dict):
                items.extend(flatten_dict(v, new_key))
            else:
                items.append((new_key, v))
    return items

def analyze_translations():
    id_dir = os.path.join(LANG_DIR, "id")
    en_dir = os.path.join(LANG_DIR, "en")
    
    files = sorted([f for f in os.listdir(id_dir) if f.endswith('.php')])
    
    php_dump_script = r'''
    $dir = $argv[1];
    $res = [];
    foreach (scandir($dir) as $f) {
        if (substr($f, -4) === '.php') {
            $base = substr($f, 0, -4);
            $val = @include "$dir/$f";
            if (is_array($val)) {
                $res[$base] = $val;
            } else {
                $res[$base] = [];
            }
        }
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    '''
    
    # Run PHP to load id
    p_id = subprocess.run(['php', '-r', php_dump_script, id_dir], capture_output=True, text=True, encoding='utf-8')
    data_id = json.loads(p_id.stdout)
    
    # Run PHP to load en
    p_en = subprocess.run(['php', '-r', php_dump_script, en_dir], capture_output=True, text=True, encoding='utf-8')
    data_en = json.loads(p_en.stdout)
    
    file_stats = []
    all_id_keys = set()
    all_en_keys = set()
    
    max_depth_by_file = {}
    
    for f in sorted(list(set(data_id.keys()) | set(data_en.keys()))):
        d_id = data_id.get(f, {})
        d_en = data_en.get(f, {})
        
        flat_id = dict(flatten_dict(d_id, f))
        flat_en = dict(flatten_dict(d_en, f))
        
        id_keys = set(flat_id.keys())
        en_keys = set(flat_en.keys())
        
        all_id_keys |= id_keys
        all_en_keys |= en_keys
        
        # calculate max depth
        def get_max_depth(d, cur=1):
            if not isinstance(d, dict) or not d:
                return cur
            return max(get_max_depth(v, cur + 1) for v in d.values()) if d else cur
            
        depth_id = get_max_depth(d_id)
        depth_en = get_max_depth(d_en)
        
        missing_in_en = len(id_keys - en_keys)
        missing_in_id = len(en_keys - id_keys)
        common = len(id_keys & en_keys)
        
        file_stats.append({
            'file': f"{f}.php",
            'id_keys': len(id_keys),
            'en_keys': len(en_keys),
            'common_keys': common,
            'missing_in_en': missing_in_en,
            'missing_in_id': missing_in_id,
            'max_depth': max(depth_id, depth_en),
            'parity_pct': (common / len(id_keys) * 100) if id_keys else 100.0
        })
        
    return {
        'total_files': len(files),
        'file_stats': file_stats,
        'total_id_keys': len(all_id_keys),
        'total_en_keys': len(all_en_keys),
        'total_common_keys': len(all_id_keys & all_en_keys),
        'total_missing_in_en': len(all_id_keys - all_en_keys),
        'total_missing_in_id': len(all_en_keys - all_id_keys),
        'overall_parity_pct': (len(all_id_keys & all_en_keys) / len(all_id_keys) * 100) if all_id_keys else 100.0,
        'sample_missing_in_en': list(all_id_keys - all_en_keys)[:20],
        'sample_missing_in_id': list(all_en_keys - all_id_keys)[:20]
    }

def analyze_views_and_issues():
    # Run deep_audit_v2.py to get JSON output
    cmd = ['python', AUDIT_SCRIPT, r'resources/views/app']
    p = subprocess.run(cmd, capture_output=True, text=True, encoding='utf-8')
    audit_data = json.loads(p.stdout)
    app_audit = audit_data.get('resources/views/app', {})
    
    file_reports = app_audit.get('files', [])
    
    submodule_summary = defaultdict(lambda: {
        'total_files': 0,
        'clean_files': 0,
        'files_with_issues': 0,
        'total_lines': 0,
        'total_i18n_calls': 0,
        'total_issues': 0,
        'issues_by_domain': Counter(),
        'issues_by_type': Counter(),
        'file_details': []
    })
    
    total_domain_counts = Counter()
    total_type_counts = Counter()
    
    for f in file_reports:
        fp = f['filepath'].replace('\\', '/')
        # extract submodule
        rel = fp.replace('resources/views/app/', '')
        parts = rel.split('/')
        submod = 'root' if len(parts) == 1 else parts[0]
        
        s = submodule_summary[submod]
        s['total_files'] += 1
        s['total_lines'] += f['total_lines']
        s['total_i18n_calls'] += f['i18n_calls']
        s['total_issues'] += f['issue_count']
        if f['issue_count'] == 0:
            s['clean_files'] += 1
        else:
            s['files_with_issues'] += 1
            
        for iss in f['issues']:
            dom = iss['domain']
            itype = iss['type']
            s['issues_by_domain'][dom] += 1
            s['issues_by_type'][itype] += 1
            total_domain_counts[dom] += 1
            total_type_counts[itype] += 1
            
        s['file_details'].append({
            'file': rel,
            'lines': f['total_lines'],
            'i18n_calls': f['i18n_calls'],
            'issues': f['issue_count'],
            'issue_types': list(Counter([iss['type'] for iss in f['issues']]).items())
        })
        
    return {
        'submodule_summary': {k: {
            'total_files': v['total_files'],
            'clean_files': v['clean_files'],
            'files_with_issues': v['files_with_issues'],
            'total_lines': v['total_lines'],
            'total_i18n_calls': v['total_i18n_calls'],
            'total_issues': v['total_issues'],
            'issues_by_domain': dict(v['issues_by_domain']),
            'issues_by_type': dict(v['issues_by_type']),
            'file_details': v['file_details']
        } for k, v in submodule_summary.items()},
        'total_domain_counts': dict(total_domain_counts),
        'total_type_counts': dict(total_type_counts)
    }

def analyze_patterns_and_alpine():
    results = {
        'total_alpine_x_data': 0,
        'files_with_alpine': 0,
        'alpine_files': [],
        'translation_prefixes_used': Counter(),
        'complex_ui_features': defaultdict(list),
        'formatting_helpers': Counter()
    }
    
    for root, dirs, files in os.walk(APP_VIEWS_DIR):
        for f in files:
            if not f.endswith('.blade.php'):
                continue
            fpath = os.path.join(root, f)
            rel = os.path.relpath(fpath, APP_VIEWS_DIR).replace('\\', '/')
            with open(fpath, 'r', encoding='utf-8', errors='ignore') as fp:
                content = fp.read()
                
            # Alpine
            x_data_matches = len(re.findall(r'x-data\s*=', content))
            if x_data_matches > 0:
                results['total_alpine_x_data'] += x_data_matches
                results['files_with_alpine'] += 1
                results['alpine_files'].append(rel)
                
            # Translation prefixes
            # find all __('prefix.key' or @lang('prefix.key'
            matches = re.findall(r"(?:__|@lang)\s*\(\s*['\"]([a-zA-Z0-9_\-]+)\.", content)
            for m in matches:
                results['translation_prefixes_used'][m] += 1
                
            # Formatting helpers
            if 'number_format(' in content:
                results['formatting_helpers']['number_format'] += len(re.findall(r'number_format\(', content))
            if 'formatRupiah' in content:
                results['formatting_helpers']['formatRupiah'] += len(re.findall(r'formatRupiah', content))
            if 'Carbon' in content or '->format(' in content or '->translatedFormat(' in content:
                results['formatting_helpers']['date_formatting'] += len(re.findall(r'->(?:translatedFormat|format)\(', content))
                
            # Complex UI constructs
            has_canvas = '<canvas' in content
            has_chart = 'chart' in content.lower() or 'chart.js' in content.lower()
            has_modal = 'x-show="show' in content or 'x-show="open' in content or 'x-show="modal' in content or 'role="dialog"' in content
            has_table = '<table' in content
            has_dynamic_js_strings = '`' in content and '${' in content
            
            features = []
            if has_canvas: features.append('canvas_3d_or_graph')
            if has_chart: features.append('charting')
            if has_modal: features.append('alpine_modal_dialogs')
            if has_table: features.append('complex_table')
            if has_dynamic_js_strings: features.append('js_template_literals')
            
            if features:
                results['complex_ui_features'][rel] = features
                
    return results

if __name__ == '__main__':
    print("Starting deep survey...")
    trans = analyze_translations()
    print(f"Translations analyzed: {trans['total_files']} files, ID keys: {trans['total_id_keys']}, EN keys: {trans['total_en_keys']}, Parity: {trans['overall_parity_pct']:.2f}%")
    
    views_issues = analyze_views_and_issues()
    print(f"Views and issues analyzed across {len(views_issues['submodule_summary'])} submodules")
    
    patterns = analyze_patterns_and_alpine()
    print(f"Alpine files: {patterns['files_with_alpine']}, Total x-data: {patterns['total_alpine_x_data']}")
    
    output = {
        'translations': trans,
        'views_issues': views_issues,
        'patterns': {
            'total_alpine_x_data': patterns['total_alpine_x_data'],
            'files_with_alpine': patterns['files_with_alpine'],
            'translation_prefixes_top30': patterns['translation_prefixes_used'].most_common(30),
            'formatting_helpers': dict(patterns['formatting_helpers']),
            'total_files_with_complex_ui': len(patterns['complex_ui_features']),
            'sample_complex_files': list(patterns['complex_ui_features'].items())[:15]
        }
    }
    
    with open(r'c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\survey_data.json', 'w', encoding='utf-8') as out:
        json.dump(output, out, indent=2, ensure_ascii=False)
        
    print("Survey data saved to survey_data.json successfully.")
