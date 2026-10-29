with open('routes/owner.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()
for i, line in enumerate(lines, 1):
    if "'pos'" in line or '"pos"' in line or '/pos' in line or 'PosTerminalWebController' in line:
        print(f"{i:4}: {line.strip()}")
