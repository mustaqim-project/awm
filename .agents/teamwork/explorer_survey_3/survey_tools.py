import os
import json
import glob
from collections import defaultdict, Counter

APP_VIEWS_DIR = r"c:\laragon\www\cooca_core\resources\views\app"
LANG_DIR = r"c:\laragon\www\cooca_core\lang"

def survey_views():
    all_files = []
    submodule_counts = defaultdict(list)
    
    for root, dirs, files in os.walk(APP_VIEWS_DIR):
        for f in files:
            if f.endswith('.blade.php'):
                full_path = os.path.join(root, f)
                rel_path = os.path.relpath(full_path, APP_VIEWS_DIR)
                all_files.append(rel_path)
                parts = rel_path.split(os.sep)
                if len(parts) == 1:
                    submodule = "root"
                else:
                    submodule = parts[0]
                submodule_counts[submodule].append(rel_path)
                
    return all_files, submodule_counts

def survey_lang():
    id_dir = os.path.join(LANG_DIR, "id")
    en_dir = os.path.join(LANG_DIR, "en")
    
    id_files = [f for f in os.listdir(id_dir) if f.endswith('.php')] if os.path.exists(id_dir) else []
    en_files = [f for f in os.listdir(en_dir) if f.endswith('.php')] if os.path.exists(en_dir) else []
    
    json_files = [f for f in os.listdir(LANG_DIR) if f.endswith('.json')] if os.path.exists(LANG_DIR) else []
    
    return id_files, en_files, json_files

if __name__ == "__main__":
    views, submodules = survey_views()
    print(f"Total Blade files in resources/views/app: {len(views)}")
    print("\nSubmodule distribution:")
    for sub, flist in sorted(submodules.items(), key=lambda x: len(x[1]), reverse=True):
        print(f"  - {sub}: {len(flist)} files")
        
    id_files, en_files, json_files = survey_lang()
    print(f"\nLang id PHP files: {len(id_files)}")
    print(f"Lang en PHP files: {len(en_files)}")
    print(f"Lang JSON files: {json_files}")
    
    id_set = set(id_files)
    en_set = set(en_files)
    
    only_in_id = id_set - en_set
    only_in_en = en_set - id_set
    common_php = id_set & en_set
    print(f"Common PHP translation files: {len(common_php)}")
    if only_in_id:
        print(f"Only in id: {only_in_id}")
    if only_in_en:
        print(f"Only in en: {only_in_en}")
