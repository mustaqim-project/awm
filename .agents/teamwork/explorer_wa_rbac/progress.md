# Progress — WhatsApp RBAC Investigation

Last visited: 2026-11-07T05:37:00Z
Status: In Progress

## Tasks
- [x] 1. Identify all WhatsApp-related controllers
  - Web: `WhatsAppWebController`, `WhatsAppBroadcastWebController`, `MetaWhatsAppOnboardingController`
  - Admin: `AdminWhatsAppController`, `AdminSettingController`
  - Webhook/API: `WhatsAppWebhookController`, `MetaWhatsAppWebhookController`, `PosReceiptApiController`
- [x] 2. Identify all WhatsApp-related routes
  - Inspected `routes/owner.php` lines 925-962
  - Inspected `routes/admin.php` lines 365-389
  - Inspected `routes/api.php` lines 77-89, 1317
- [x] 3. Investigate RBAC authorization architecture in COOCA
  - Multi-tenant custom RBAC (not Spatie package): `Permission`, `Role`, `Context`, `RequirePermission` middleware
  - Seeders: `RbacSeeder.php` (for tenant permissions), `AdminRbacSeeder.php` (for admin)
  - Existing WhatsApp permissions: `whatsapp.view`, `whatsapp.manage`
  - Gating in `ModuleRegistry.php`: `channels_marketing` module
  - Controller-level guard mechanism: Laravel 11 `HasMiddleware` interface with `new Middleware(...)`
  - Found critical finding: `Gate::before` in `AppServiceProvider.php` only checks `Admin`, not `User` -> `@can` in views for tenant User requires `Gate::before` hook or Gate definition!
- [ ] 4. Determine exact controller-level guard implementation needed for 403 Forbidden responses
- [ ] 5. Identify action buttons and links in WhatsApp views (`resources/views/app/whatsapp/`) needing protection
- [ ] 6. Formulate concrete implementation recommendations for seeders, controllers, routes, and views
- [ ] 7. Compile comprehensive handoff report (`handoff.md`)
- [ ] 8. Send message to parent
