import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open(r"c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\survey_summary_v2.json", "r", encoding="utf-8") as f:
    data = json.load(f)

print("=== 1. MODALS MISSING TELEPORT ===")
for p in data["architectural_patterns"]["modals_missing_teleport"]:
    print(" -", p)

print("\n=== 2. TABLES MISSING OVERFLOW WRAPPER ===")
for p in data["architectural_patterns"]["tables_missing_overflow_wrapper"]:
    print(" -", p)

print("\n=== 3. HARDCODED UNRESPONSIVE GRIDS (SAMPLE) ===")
for p, grids in data["architectural_patterns"]["views_with_hardcoded_grid_cols"][:15]:
    print(f" - {p}: {grids}")

print("\n=== 4. VIEWS MISSING MOBILE BOTTOM PADDING (SAMPLE) ===")
for p in data["architectural_patterns"]["views_without_mobile_bottom_padding"][:15]:
    print(" -", p)

print("\n=== 5. RAW EMOJIS IN VIEWS ===")
for p, emojis in data["architectural_patterns"]["emoji_in_views"]:
    print(f" - {p}: {' '.join(emojis)}")

print("\n=== 6. DECORATIVE PULSE IN VIEWS (SAMPLE) ===")
for p, count in data["architectural_patterns"]["animate_pulse_in_views"][:15]:
    print(f" - {p}: {count} pulse instances")

print("\n=== 7. DOUBLE HEADERS (SAMPLE) ===")
for p in data["architectural_patterns"]["double_headers"][:15]:
    print(" -", p)
