import os
import re

controllers = [
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Web\Ai\AiCompanyWebController.php",
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Web\Ai\PosAiWebController.php",
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Web\AiStudio\OwnerAiStudioWebController.php",
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Web\AiStudio\OwnerInspirationWebController.php",
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Admin\AdminAiTokenController.php",
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Admin\AdminAiStudioVisualController.php",
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Admin\AdminAiStudioSourceController.php",
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Admin\AdminAiStudioPricingController.php",
    r"c:\laragon\www\cooca_core\app\Http\Controllers\Admin\AdminInspirationManagementController.php",
]

for c in controllers:
    print(f"=== Controller: {os.path.basename(c)} ===")
    if not os.path.exists(c):
        print("  File not found!")
        continue
    with open(c, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    # Check HasMiddleware
    implements_hm = "implements HasMiddleware" in content
    print(f"  HasMiddleware: {implements_hm}")
    
    # Check constructor
    has_construct = "__construct" in content
    print(f"  Has __construct: {has_construct}")
    
    # Check middleware references
    middlewares = re.findall(r"middleware\([^\)]+\)", content)
    print(f"  Middlewares in controller: {middlewares}")
    
    # Check permission references
    permissions = re.findall(r"hasPermission\([^\)]+\)|require\.permission:[^\'\"]+", content)
    print(f"  Permission checks: {permissions}")
    
    # Check authorize or abort
    authorizes = re.findall(r"(?:authorize|abort_unless|abort_if)\([^\)]+\)", content)
    print(f"  Authorizes/Aborts: {authorizes[:5]}")
    
    # List public methods
    methods = re.findall(r"public\s+function\s+([a-zA-Z0-9_]+)\s*\(", content)
    print(f"  Public methods ({len(methods)}): {', '.join(methods)}")
    print()
