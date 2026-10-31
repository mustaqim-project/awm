import os, glob

dirs_to_check = [
    'products',
    'sales',
    'purchasing',
    'billing',
    'roles',
    'ai',
    'whatsapp',
    'hrm',
    'finance',
    'materials'
]

for d in dirs_to_check:
    full_d = os.path.join('resources', 'views', 'app', d)
    if os.path.exists(full_d):
        files = [os.path.relpath(p, full_d) for p in glob.glob(os.path.join(full_d, '**', '*.blade.php'), recursive=True)]
        print(f"Directory: {d} -> {files}")
    else:
        print(f"Directory: {d} DOES NOT EXIST")
