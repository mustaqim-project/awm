import re
from collections import defaultdict

raw = open('scan_report.txt', 'rb').read().decode('utf-16le', errors='ignore')
blocks = re.findall(r'\[([A-Z\s]+)\]\s+([^\n]+)\s*\n\s+([^\n]+)(?:\s+([^\n]+))?', raw)

controllers = defaultdict(lambda: {'guarded': 0, 'unguarded': 0, 'route_only': 0, 'role_only': 0, 'closure': 0})
items_by_status = defaultdict(list)

for status, target, reason, file_loc in blocks:
    status = status.strip()
    target = target.strip()
    reason = reason.strip()
    parts = target.split()
    route = parts[1] if len(parts) > 1 else ''
    action = parts[0]
    
    ctrl = action.split('@')[0] if '@' in action else 'Closure'
    method = action.split('@')[1] if '@' in action else action
    
    status_key = status.lower().replace(' ', '_')
    if status_key in controllers[ctrl]:
        controllers[ctrl][status_key] += 1
    items_by_status[status].append({
        'target': target,
        'action': action,
        'ctrl': ctrl,
        'method': method,
        'route': route,
        'reason': reason,
        'file_loc': file_loc.strip() if file_loc else ''
    })

print(f"Total controllers analyzed: {len(controllers)}")
total_items = sum(len(v) for v in items_by_status.values())
print(f"Total endpoints/methods: {total_items}")
for st, lst in items_by_status.items():
    print(f"  [{st}]: {len(lst)}")

ns_summary = defaultdict(lambda: {'total': 0, 'guarded': 0, 'unguarded': 0, 'route_only': 0, 'role_only': 0, 'closure': 0})
for ctrl, counts in controllers.items():
    if ctrl.startswith('App\\Http\\Controllers\\Admin\\'):
        ns = 'Admin'
    elif ctrl.startswith('App\\Http\\Controllers\\Web\\'):
        sub = ctrl.replace('App\\Http\\Controllers\\Web\\', '').split('\\')[0]
        ns = f'Web/{sub}'
    elif ctrl.startswith('App\\Http\\Controllers\\Api\\'):
        sub = ctrl.replace('App\\Http\\Controllers\\Api\\', '').split('\\')[0]
        ns = f'Api/{sub}'
    elif ctrl.startswith('App\\Http\\Controllers\\Auth\\'):
        ns = 'Auth'
    else:
        ns = 'Other'
        
    for k, v in counts.items():
        ns_summary[ns][k] += v
    ns_summary[ns]['total'] += sum(counts.values())

print("\n=== NAMESPACE SUMMARY ===")
for ns, s in sorted(ns_summary.items(), key=lambda x: x[1]['total'], reverse=True):
    print(f"{ns:28} Total: {s['total']:3} | Guarded: {s['guarded']:3} | Unguarded: {s['unguarded']:3} | Route-Only: {s['route_only']:3} | Role-Only: {s['role_only']:3} | Closure: {s['closure']:3}")

print("\n=== DETAIL UNGUARDED DI MODUL PRIORITAS ===")
priority_keywords = ['Admin\\', 'Web\\Pos', 'Web\\Commerce', 'Web\\Hrm', 'Web\\Marketplace', 'CommunityWebController']
for item in items_by_status['UNGUARDED']:
    act = item['action']
    if any(k in act for k in priority_keywords):
        print(f"[{item['route']:35}] {item['action']:60} :: {item['reason']}")
