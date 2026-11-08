# LARAVEL RBAC, PERMISSION AUDIT & CONTROLLER-LEVEL AUTHORIZATION SKILL (Local Copy)
Source: C:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md

Refer to original file for complete reference guides.
Key methodologies:
- HasMiddleware implementation with Illuminate\Routing\Controllers\Middleware
- require.permission:a,b,c (comma-separated OR logic in custom RequirePermission middleware)
- RbacSeeder.php permissionMatrix and roleDefinitions updates
- Route cleanup (removing redundant route-level middleware)
- Idempotent seeding: php artisan db:seed --class=RbacSeeder --force
