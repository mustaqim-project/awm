# Local copy of laravel-permission SKILL.md
Source: C:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md

See original file for full details.
Core methodology:
- Modern Laravel 11+ `HasMiddleware` Interface in Controllers (`middleware()` static method returning `Middleware` instances with `require.permission:...`).
- Clean Routing: remove `middleware('require.permission:...')` from routes files once encapsulated in Controller.
- IDOR Protection: Always scope queries by `business_id` or check `abort_unless($model->business_id === $business->id, 403);`.
- UI/Blade permission checks: sync `@can` / `Context::hasPermission` to match actual permissions.
