Refactor and improve the WhatsApp module views in resources/views/app/whatsapp for production, simplifying user interface layouts and enforcing role-based access control (RBAC).

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. UI Simplification for WhatsApp Views
Simplify and declutter the user interface across all views in `resources/views/app/whatsapp` (`index.blade.php`, `broadcast.blade.php`, `broadcast_detail.blade.php`, `logs.blade.php`). Reorganize information hierarchy, eliminate overlapping buttons or elements, and establish a clear, structured action button hierarchy for production.

### R2. Role-Based Access Control (RBAC) Enforcement
Enforce Laravel RBAC authorization for WhatsApp module functionality. Implement controller-level permission guards across WhatsApp controllers, restrict UI elements and action buttons in Blade views using `@can` directives, and ensure required permissions are registered in database seeders.

## Verification Resources
Standard Laravel testing framework and existing application test suite.

## Acceptance Criteria

### UI Simplification & Layout Hierarchy
- [ ] All views in `resources/views/app/whatsapp` (`index.blade.php`, `broadcast.blade.php`, `broadcast_detail.blade.php`, `logs.blade.php`) are decluttered with clear visual and information hierarchy.
- [ ] No buttons or UI elements overlap or collide across responsive desktop and mobile viewports.
- [ ] Action buttons adhere to distinct primary, secondary, and destructive hierarchy without cluttered or duplicate actions.

### Role-Based Access Control (RBAC)
- [ ] WhatsApp controller actions enforce permission checks at the controller level.
- [ ] Blade views in `resources/views/app/whatsapp` conditionally display actions, buttons, and navigation using `@can` directives.
- [ ] All relevant WhatsApp permissions are defined and registered in database seeders.
- [ ] Unauthorized users attempting direct access to WhatsApp controller endpoints receive an HTTP 403 Forbidden response.

### Verification & Stability
- [ ] All existing test suites pass without regression (`php artisan test`).
- [ ] All modified Blade views compile and render without syntax errors or missing variable exceptions.
