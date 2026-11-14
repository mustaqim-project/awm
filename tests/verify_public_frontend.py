#!/usr/bin/env python3
"""
Static AST & Content Verification Engine for COOCA Public Frontend
Validates:
1. Absence of banned trial terms in Blade views and language dictionaries
2. Canonical CTA links (https://cooca.id/register and https://cooca.id/pricing)
3. 100% dictionary key parity between lang/id/ and lang/en/
4. Zero missing keys in public Blade templates (raw string fallbacks)
5. Parameter placeholder symmetry and flat array structure
"""

import os
import sys
import re
import json
import argparse
import subprocess
from pathlib import Path
from typing import Dict, List, Set, Tuple, Any

# ANSI Color Codes
GREEN = "\033[92m"
RED = "\033[91m"
YELLOW = "\033[93m"
CYAN = "\033[96m"
BOLD = "\033[1m"
RESET = "\033[0m"

# Target Directories & Standalone Files per Project Specification
TARGET_PUBLIC_DIRS = [
    "resources/views/public/blog",
    "resources/views/public/business-operating-system",
    "resources/views/public/calculators",
    "resources/views/public/contact",
    "resources/views/public/content-automation",
    "resources/views/public/discovery",
    "resources/views/public/home",
    "resources/views/public/landing_commercial",
    "resources/views/public/marketplace",
    "resources/views/public/omnichannel",
    "resources/views/public/omnichannel-erp",
    "resources/views/public/partials",
    "resources/views/public/resources",
    "resources/views/public/solutions",
    "resources/views/public/templates",
]

TARGET_STANDALONE_FILES = [
    "resources/views/public/about.blade.php",
    "resources/views/public/business_landing.blade.php",
    "resources/views/public/demo.blade.php",
    "resources/views/public/pricing.blade.php",
    "resources/views/public/privacy.blade.php",
    "resources/views/public/security.blade.php",
    "resources/views/public/sitemap.blade.php",
    "resources/views/public/support.blade.php",
    "resources/views/public/terms.blade.php",
    "resources/views/landing.blade.php",
    "resources/views/layouts/public_marketing.blade.php",
]

TARGET_LANG_FILES = [
    "public_blog.php",
    "public_bos.php",
    "public_calculators.php",
    "public_commercial.php",
    "public_contact.php",
    "public_content.php",
    "public_discovery.php",
    "public_erp.php",
    "public_home.php",
    "public_marketing.php",
    "public_marketplace.php",
    "public_omnichannel.php",
    "public_resources.php",
    "public_solutions.php",
    "public_templates.php",
    "common.php",
    "storefront.php",
]

BANNED_PATTERNS = [
    (r"\bfree\s+trial\b", "Free Trial"),
    (r"\bstart\s+free\s+trial\b", "Start Free Trial"),
    (r"\btry\s+for\s+free\b", "Try for Free"),
    (r"\btrial\s+gratis\b", "Trial Gratis"),
    (r"\bcoba\s+gratis\b", "Coba Gratis"),
    (r"\buji\s+coba\s+gratis\b", "Uji Coba Gratis"),
    (r"\bfree[-_]trial\b", "free-trial"),
    (r"\btrial[-_]gratis\b", "trial-gratis"),
    (r"\bcoba[-_]gratis\b", "coba-gratis"),
    (r"free&nbsp;trial", "Free&nbsp;Trial"),
    (r"start&#32;free&#32;trial", "Start&#32;Free&#32;Trial"),
]

# Regex patterns
BLADE_COMMENT_REGEX = re.compile(r"\{\{--.*?--\}\}", re.DOTALL)
PHP_BLOCK_COMMENT_REGEX = re.compile(r"/\*.*?\*/", re.DOTALL)
PHP_LINE_COMMENT_REGEX = re.compile(r"//.*?$|#.*?$", re.MULTILINE)
TRANSLATION_HELPER_REGEX = re.compile(
    r"""(?:__|\@lang|trans|Lang::get)\s*\(\s*(['"])(.*?)\1""",
    re.MULTILINE
)
PLACEHOLDER_REGEX = re.compile(r":([a-zA-Z_][a-zA-Z0-9_]*)")

CANONICAL_REGISTER = "https://cooca.id/register"
CANONICAL_PRICING = "https://cooca.id/pricing"


class FrontendVerificationEngine:
    def __init__(self, workspace_root: str):
        self.root = Path(workspace_root).resolve()
        self.dictionaries: Dict[str, Dict[str, Dict[str, Any]]] = {"id": {}, "en": {}}
        self.dict_line_maps: Dict[str, Dict[str, Dict[str, int]]] = {"id": {}, "en": {}}
        self.dict_raw_contents: Dict[str, Dict[str, str]] = {"id": {}, "en": {}}
        
        # Results collections
        self.blade_files: List[Path] = []
        self.banned_term_violations: List[Dict[str, Any]] = []
        self.parity_violations: List[Dict[str, Any]] = []
        self.missing_key_violations: List[Dict[str, Any]] = []
        self.cta_violations: List[Dict[str, Any]] = []
        self.duplicate_key_violations: List[Dict[str, Any]] = []
        self.empty_value_violations: List[Dict[str, Any]] = []
        self.placeholder_violations: List[Dict[str, Any]] = []
        self.total_keys_validated = 0
        self.total_helpers_checked = 0

    def load_dictionaries(self) -> None:
        """Load all language dictionaries using PHP CLI for exact array representation."""
        # Use single PHP invocation for high speed
        php_script = """
        $root = $argv[1];
        $locales = ['id', 'en'];
        $result = ['id' => [], 'en' => []];
        foreach ($locales as $loc) {
            $dir = $root . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $loc;
            if (!is_dir($dir)) continue;
            $files = scandir($dir);
            foreach ($files as $file) {
                if (str_ends_with($file, '.php')) {
                    $domain = substr($file, 0, -4);
                    $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
                    try {
                        $arr = require $fullPath;
                        if (is_array($arr)) {
                            $result[$loc][$domain] = $arr;
                        }
                    } catch (Throwable $e) {}
                }
            }
        }
        echo json_encode($result);
        """
        try:
            res = subprocess.run(
                ["php", "-r", php_script, str(self.root)],
                capture_output=True,
                text=True,
                encoding="utf-8",
                check=True
            )
            data = json.loads(res.stdout)
            self.dictionaries = data
        except Exception as e:
            print(f"{RED}Failed to load dictionaries via PHP CLI: {e}{RESET}", file=sys.stderr)
            raise

        # Map line numbers and detect duplicate keys in target language files
        for locale in ["id", "en"]:
            lang_dir = self.root / "lang" / locale
            if not lang_dir.exists():
                continue
            for lang_filename in TARGET_LANG_FILES:
                if lang_filename == "storefront.php":
                    continue
                php_file = lang_dir / lang_filename
                if not php_file.exists():
                    continue
                domain = php_file.stem
                self.dict_line_maps[locale][domain] = {}
                try:
                    content = php_file.read_text(encoding="utf-8", errors="replace")
                    self.dict_raw_contents[locale][domain] = content
                    seen_keys = set()
                    for line_num, line in enumerate(content.splitlines(), start=1):
                        stripped = line.strip()
                        # Match 'key' => ... or "key" => ...
                        m = re.match(r"""^['"]([a-zA-Z0-9_\-]+)['"]\s*=>""", stripped)
                        if m:
                            k = m.group(1)
                            if k in seen_keys:
                                self.duplicate_key_violations.append({
                                    "file": str(php_file.relative_to(self.root)),
                                    "line": line_num,
                                    "key": k,
                                    "locale": locale
                                })
                            seen_keys.add(k)
                            self.dict_line_maps[locale][domain][k] = line_num
                except Exception as ex:
                    print(f"{YELLOW}Warning reading {php_file}: {ex}{RESET}", file=sys.stderr)

    def discover_blade_files(self, target_filter: str = None) -> None:
        """Find all in-scope Blade files across the 15 directories and 10 standalone files."""
        files_set: Set[Path] = set()

        if target_filter:
            target_path = (self.root / target_filter).resolve() if not os.path.isabs(target_filter) else Path(target_filter).resolve()
            if target_path.is_file():
                self.blade_files = [target_path]
                return
            elif target_path.is_dir():
                for blade_file in target_path.rglob("*.blade.php"):
                    files_set.add(blade_file)
                self.blade_files = sorted(list(files_set))
                return

        for rel_dir in TARGET_PUBLIC_DIRS:
            dir_path = self.root / rel_dir
            if dir_path.exists():
                for blade_file in dir_path.rglob("*.blade.php"):
                    files_set.add(blade_file)

        for rel_file in TARGET_STANDALONE_FILES:
            file_path = self.root / rel_file
            if file_path.exists():
                files_set.add(file_path)

        self.blade_files = sorted(list(files_set))

    def scan_banned_terms(self) -> None:
        """Scan views and dictionary files for banned trial terminology."""
        # 1. Scan Blade views
        for b_path in self.blade_files:
            rel_path = str(b_path.relative_to(self.root))
            raw_text = b_path.read_text(encoding="utf-8", errors="replace")
            # Strip Blade comments to avoid false positives in comments
            text_clean = BLADE_COMMENT_REGEX.sub("", raw_text)

            for line_idx, line in enumerate(text_clean.splitlines(), start=1):
                for pattern, term_name in BANNED_PATTERNS:
                    if re.search(pattern, line, re.IGNORECASE):
                        # Avoid matching route names like 'commercial.kasir-gratis' or route definitions
                        # But flag any user-facing text, button text, or attribute
                        self.banned_term_violations.append({
                            "type": "view",
                            "file": rel_path,
                            "line": line_idx,
                            "term": term_name,
                            "snippet": line.strip()[:120]
                        })

        # 2. Scan Language Dictionaries
        for locale in ["id", "en"]:
            lang_dir = self.root / "lang" / locale
            if not lang_dir.exists():
                continue
            for lang_file in TARGET_LANG_FILES:
                php_path = lang_dir / lang_file
                if not php_path.exists():
                    continue
                rel_path = str(php_path.relative_to(self.root))
                content = php_path.read_text(encoding="utf-8", errors="replace")
                # Remove block comments & line comments
                content_clean = PHP_BLOCK_COMMENT_REGEX.sub("", content)
                lines = content_clean.splitlines()
                for line_idx, line in enumerate(lines, start=1):
                    # Skip comment lines
                    stripped = line.strip()
                    if stripped.startswith("//") or stripped.startswith("#") or stripped.startswith("*"):
                        continue
                    for pattern, term_name in BANNED_PATTERNS:
                        if re.search(pattern, line, re.IGNORECASE):
                            self.banned_term_violations.append({
                                "type": "dictionary",
                                "file": rel_path,
                                "line": line_idx,
                                "term": term_name,
                                "snippet": stripped[:120]
                            })

    def verify_dictionary_parity(self) -> None:
        """Verify 100% key parity, placeholders, empty values, and 1D structure between lang/id and lang/en."""
        # Only 15 modular public language files + common.php must adhere to 1D flat dictionary parity
        checked_domains = [f.replace(".php", "") for f in TARGET_LANG_FILES if f != "storefront.php"]

        for domain in checked_domains:
            id_dict = self.dictionaries.get("id", {}).get(domain, {})
            en_dict = self.dictionaries.get("en", {}).get(domain, {})

            id_keys = set(id_dict.keys())
            en_keys = set(en_dict.keys())

            # Keys in ID but missing in EN
            missing_in_en = id_keys - en_keys
            for k in missing_in_en:
                line = self.dict_line_maps.get("id", {}).get(domain, {}).get(k, 1)
                self.parity_violations.append({
                    "domain": domain,
                    "key": k,
                    "missing_in": "en",
                    "file": f"lang/en/{domain}.php",
                    "defined_in": f"lang/id/{domain}.php:{line}"
                })

            # Keys in EN but missing in ID
            missing_in_id = en_keys - id_keys
            for k in missing_in_id:
                line = self.dict_line_maps.get("en", {}).get(domain, {}).get(k, 1)
                self.parity_violations.append({
                    "domain": domain,
                    "key": k,
                    "missing_in": "id",
                    "file": f"lang/id/{domain}.php",
                    "defined_in": f"lang/en/{domain}.php:{line}"
                })

            # Check shared keys for non-empty values, 1D structure, placeholder consistency
            shared_keys = id_keys & en_keys
            self.total_keys_validated += len(shared_keys)

            for k in shared_keys:
                val_id = id_dict[k]
                val_en = en_dict[k]

                # 1D structure check
                if isinstance(val_id, dict) or isinstance(val_en, dict):
                    self.parity_violations.append({
                        "domain": domain,
                        "key": k,
                        "error": "Nested array detected (must be 1D flat associative array)"
                    })
                    continue

                # Empty value check
                if str(val_id).strip() == "":
                    self.empty_value_violations.append({
                        "file": f"lang/id/{domain}.php",
                        "key": k,
                        "locale": "id"
                    })
                if str(val_en).strip() == "":
                    self.empty_value_violations.append({
                        "file": f"lang/en/{domain}.php",
                        "key": k,
                        "locale": "en"
                    })

                # Parameter placeholder check (:attr)
                str_id = str(val_id)
                str_en = str(val_en)
                ph_id = set(PLACEHOLDER_REGEX.findall(str_id))
                ph_en = set(PLACEHOLDER_REGEX.findall(str_en))
                if ph_id != ph_en:
                    self.placeholder_violations.append({
                        "domain": domain,
                        "key": k,
                        "id_placeholders": sorted(list(ph_id)),
                        "en_placeholders": sorted(list(ph_en))
                    })

    def scan_blade_views_for_missing_keys(self) -> None:
        """Extract all __() and @lang() calls from Blade views and ensure they exist in both ID and EN dictionaries."""
        all_id_dicts = self.dictionaries.get("id", {})
        all_en_dicts = self.dictionaries.get("en", {})

        for b_path in self.blade_files:
            rel_path = str(b_path.relative_to(self.root))
            content = b_path.read_text(encoding="utf-8", errors="replace")
            # Strip Blade comments
            clean_content = BLADE_COMMENT_REGEX.sub("", content)

            lines = clean_content.splitlines()
            for line_idx, line in enumerate(lines, start=1):
                matches = TRANSLATION_HELPER_REGEX.findall(line)
                for quote, full_key in matches:
                    self.total_helpers_checked += 1
                    full_key = full_key.strip()
                    # Skip dynamic variables like $var or expression concatenations
                    if "$" in full_key or "{" in full_key or "}" in full_key:
                        continue
                    # Check if domain is present
                    if "." not in full_key:
                        # Raw key without domain
                        self.missing_key_violations.append({
                            "file": rel_path,
                            "line": line_idx,
                            "key": full_key,
                            "issue": "Missing domain prefix (must follow 'domain.key')"
                        })
                        continue

                    parts = full_key.split(".", 1)
                    domain = parts[0]
                    key = parts[1]

                    # Check ID
                    id_domain_dict = all_id_dicts.get(domain)
                    if id_domain_dict is None:
                        self.missing_key_violations.append({
                            "file": rel_path,
                            "line": line_idx,
                            "key": full_key,
                            "issue": f"Domain '{domain}' does not exist in lang/id/"
                        })
                    elif key not in id_domain_dict:
                        self.missing_key_violations.append({
                            "file": rel_path,
                            "line": line_idx,
                            "key": full_key,
                            "issue": f"Key '{key}' missing from lang/id/{domain}.php"
                        })

                    # Check EN
                    en_domain_dict = all_en_dicts.get(domain)
                    if en_domain_dict is None:
                        self.missing_key_violations.append({
                            "file": rel_path,
                            "line": line_idx,
                            "key": full_key,
                            "issue": f"Domain '{domain}' does not exist in lang/en/"
                        })
                    elif key not in en_domain_dict:
                        self.missing_key_violations.append({
                            "file": rel_path,
                            "line": line_idx,
                            "key": full_key,
                            "issue": f"Key '{key}' missing from lang/en/{domain}.php"
                        })

    def verify_canonical_ctas(self) -> None:
        """Verify that CTA links conform to canonical URLs."""
        # Find <a> tags with hrefs pointing to register or pricing
        for b_path in self.blade_files:
            rel_path = str(b_path.relative_to(self.root))
            content = b_path.read_text(encoding="utf-8", errors="replace")
            clean_content = BLADE_COMMENT_REGEX.sub("", content)

            lines = clean_content.splitlines()
            for line_idx, line in enumerate(lines, start=1):
                # Check for insecure http://cooca.id
                if "http://cooca.id" in line:
                    self.cta_violations.append({
                        "file": rel_path,
                        "line": line_idx,
                        "issue": "Insecure HTTP URL found: 'http://cooca.id'",
                        "snippet": line.strip()[:120]
                    })
                
                # Check for relative /register or /pricing in primary CTA buttons
                # e.g. <a href="/register" or href='/pricing'
                if re.search(r"""href\s*=\s*['"]/(register|pricing)['"]""", line, re.IGNORECASE):
                    self.cta_violations.append({
                        "file": rel_path,
                        "line": line_idx,
                        "issue": "Relative CTA href found instead of canonical absolute URL",
                        "snippet": line.strip()[:120]
                    })

    def run_all(self, target_filter: str = None, fix_check: bool = False) -> bool:
        """Run all verification checks."""
        self.load_dictionaries()
        self.discover_blade_files(target_filter)
        self.scan_banned_terms()
        if fix_check and len(self.banned_term_violations) > 0:
            return False

        self.verify_dictionary_parity()
        if fix_check and (len(self.parity_violations) > 0 or len(self.duplicate_key_violations) > 0):
            return False

        self.scan_blade_views_for_missing_keys()
        if fix_check and len(self.missing_key_violations) > 0:
            return False

        self.verify_canonical_ctas()

        total_violations = (
            len(self.banned_term_violations)
            + len(self.parity_violations)
            + len(self.missing_key_violations)
            + len(self.cta_violations)
            + len(self.duplicate_key_violations)
            + len(self.empty_value_violations)
            + len(self.placeholder_violations)
        )
        return total_violations == 0

    def print_report(self, summary_only: bool = False) -> None:
        """Print human-readable ANSI colored diagnostic report."""
        print(f"\n{BOLD}{CYAN}========================================================================{RESET}")
        print(f"{BOLD}{CYAN}      COOCA PUBLIC FRONTEND CTA & I18N VERIFICATION REPORT             {RESET}")
        print(f"{BOLD}{CYAN}========================================================================{RESET}\n")

        print(f"Scanned Blade Files:        {BOLD}{len(self.blade_files)}{RESET} files")
        print(f"Validated Translation Keys: {BOLD}{self.total_keys_validated}{RESET} keys")
        print(f"Checked Helper Calls:       {BOLD}{self.total_helpers_checked}{RESET} invocations\n")

        # 1. Banned Terms
        if self.banned_term_violations:
            print(f"{BOLD}{RED}[FAIL] Banned Trial Terminology Violations: {len(self.banned_term_violations)}{RESET}")
            if not summary_only:
                for v in self.banned_term_violations[:30]:
                    print(f"  • {v['file']}:{v['line']} -> Term '{RED}{v['term']}{RESET}': {v['snippet']}")
                if len(self.banned_term_violations) > 30:
                    print(f"  ... and {len(self.banned_term_violations) - 30} more violations.")
        else:
            print(f"{BOLD}{GREEN}[PASS] Banned Trial Terminology: 0 violations{RESET}")

        # 2. Key Parity
        if self.parity_violations:
            print(f"{BOLD}{RED}[FAIL] Dictionary Key Parity Violations: {len(self.parity_violations)}{RESET}")
            if not summary_only:
                for v in self.parity_violations[:30]:
                    if "missing_in" in v:
                        print(f"  • Key '{YELLOW}{v['key']}{RESET}' in domain '{v['domain']}' missing in {BOLD}{v['missing_in'].upper()}{RESET} ({v['file']})")
                    else:
                        print(f"  • Domain '{v.get('domain')}': {v.get('error')}")
                if len(self.parity_violations) > 30:
                    print(f"  ... and {len(self.parity_violations) - 30} more parity gaps.")
        else:
            print(f"{BOLD}{GREEN}[PASS] Dictionary Key Parity (lang/id ↔ lang/en): 100% symmetric{RESET}")

        # 3. Missing Keys in Blade Views
        if self.missing_key_violations:
            print(f"{BOLD}{RED}[FAIL] Blade View Missing Keys (Raw Fallbacks): {len(self.missing_key_violations)}{RESET}")
            if not summary_only:
                for v in self.missing_key_violations[:30]:
                    print(f"  • {v['file']}:{v['line']} -> Key '{YELLOW}{v['key']}{RESET}': {v['issue']}")
                if len(self.missing_key_violations) > 30:
                    print(f"  ... and {len(self.missing_key_violations) - 30} more missing keys.")
        else:
            print(f"{BOLD}{GREEN}[PASS] Blade View Helper Keys: 0 missing keys{RESET}")

        # 4. Canonical CTAs
        if self.cta_violations:
            print(f"{BOLD}{RED}[FAIL] Canonical CTA Violations: {len(self.cta_violations)}{RESET}")
            if not summary_only:
                for v in self.cta_violations[:20]:
                    print(f"  • {v['file']}:{v['line']} -> {v['issue']}: {v['snippet']}")
        else:
            print(f"{BOLD}{GREEN}[PASS] Canonical CTA URLs: 0 violations{RESET}")

        # 5. Duplicates, Empty Values, Placeholders
        extra_issues = len(self.duplicate_key_violations) + len(self.empty_value_violations) + len(self.placeholder_violations)
        if extra_issues > 0:
            print(f"{BOLD}{YELLOW}[WARN] Dictionary Integrity Issues: {extra_issues}{RESET}")
            if self.duplicate_key_violations:
                print(f"  • Duplicates: {len(self.duplicate_key_violations)} duplicate key declarations")
            if self.empty_value_violations:
                print(f"  • Empty values: {len(self.empty_value_violations)} keys with empty string values")
            if self.placeholder_violations:
                print(f"  • Placeholders: {len(self.placeholder_violations)} placeholder mismatches")
        else:
            print(f"{BOLD}{GREEN}[PASS] Dictionary Integrity: 0 duplicates, 0 empty values, 0 placeholder mismatches{RESET}")

        print(f"\n{BOLD}{CYAN}------------------------------------------------------------------------{RESET}")
        total = (
            len(self.banned_term_violations)
            + len(self.parity_violations)
            + len(self.missing_key_violations)
            + len(self.cta_violations)
        )
        if total == 0:
            print(f"{BOLD}{GREEN}OVERALL STATUS: PASS (Production Ready - 0 Regressions){RESET}")
        else:
            print(f"{BOLD}{RED}OVERALL STATUS: FAIL ({total} Critical Violations Found){RESET}")
        print(f"{BOLD}{CYAN}========================================================================{RESET}\n")

    def to_json(self) -> str:
        """Export full findings to JSON."""
        return json.dumps({
            "scanned_blade_files_count": len(self.blade_files),
            "total_keys_validated": self.total_keys_validated,
            "total_helpers_checked": self.total_helpers_checked,
            "banned_term_violations": self.banned_term_violations,
            "parity_violations": self.parity_violations,
            "missing_key_violations": self.missing_key_violations,
            "cta_violations": self.cta_violations,
            "duplicate_key_violations": self.duplicate_key_violations,
            "empty_value_violations": self.empty_value_violations,
            "placeholder_violations": self.placeholder_violations,
            "passed": (
                len(self.banned_term_violations) == 0
                and len(self.parity_violations) == 0
                and len(self.missing_key_violations) == 0
                and len(self.cta_violations) == 0
            )
        }, indent=2)


def main():
    parser = argparse.ArgumentParser(description="COOCA Public Frontend CTA & i18n Static Verification Engine")
    parser.add_argument("--root", default=os.getcwd(), help="Workspace root directory")
    parser.add_argument("--file", help="Scan a single specific file or subdirectory only")
    parser.add_argument("--json", action="store_true", help="Output machine-readable JSON")
    parser.add_argument("--summary", action="store_true", help="Output high-level summary only")
    parser.add_argument("--fix-check", action="store_true", help="Fast exit on first violation")
    args = parser.parse_args()

    engine = FrontendVerificationEngine(args.root)
    passed = engine.run_all(target_filter=args.file, fix_check=args.fix_check)

    if args.json:
        print(engine.to_json())
    else:
        engine.print_report(summary_only=args.summary)

    sys.exit(0 if passed else 1)


if __name__ == "__main__":
    main()
