## 2026-11-08T19:16:45Z
You are a software implementation subagent (teamwork_worker).

MANDATORY FIRST STEP:
Read the authoritative request file at:
`c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`

Read the project plan and technical survey reports at:
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_rbac\handoff.md`
- Relevant skill: `C:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md`

Your working directory is:
`c:\laragon\www\cooca_core\.agents\teamwork\worker_m1_rbac\`

Update your `progress.md` in your working directory as you complete each step.

EXCLUSIVE FILE OWNERSHIP (You may modify ONLY these files):
- `app/Http/Controllers/Web/ImportWebController.php`
- `app/Http/Controllers/Api/V1/Hrm/HrmApiController.php`
- `app/Http/Controllers/Web/Hrm/HrmWebController.php`
- `database/seeders/RbacSeeder.php`
- `routes/owner.php` (import and hrm route definitions)

Tasks (Features F1, F2, F3, F4):
1. **ImportWebController (`app/Http/Controllers/Web/ImportWebController.php`)**:
   - Implement `Illuminate\Routing\Controllers\HasMiddleware`.
   - Add `public static function middleware(): array`:
     - `require.permission:materials.view,products.view,inventory.view,import.view` for `['index']`.
     - `require.permission:materials.view,import.view` for `['downloadMaterialTemplate', 'previewMaterials']`.
     - `require.permission:materials.manage,materials.create,import.manage` for `['executeMaterials']`.
     - `require.permission:products.view,import.view` for `['downloadProductTemplate', 'downloadRecipeTemplate', 'previewProducts', 'previewRecipes']`.
     - `require.permission:products.manage,products.create,import.manage` for `['executeProducts', 'executeRecipes']`.
     - `require.permission:inventory.view,import.view` for `['downloadInventoryTemplate', 'previewInventory']`.
     - `require.permission:inventory.manage,import.manage` for `['executeInventory']`.
2. **HrmApiController (`app/Http/Controllers/Api/V1/Hrm/HrmApiController.php`)**:
   - Implement `Illuminate\Routing\Controllers\HasMiddleware`.
   - Add controller middleware protecting API actions:
     - `require.permission:hrm.view,users.view` on `['hub']`.
     - `require.permission:hrm.manage,users.manage` on `['storeEmployee', 'updateEmployee', 'storeShift']`.
     - `require.permission:hrm.payroll.manage,hrm.manage,users.manage` on `['storeLoan']`.
     - `require.permission:hrm.attendance.manage,hrm.manage,users.manage` on `['approveCorrection', 'rejectCorrection']`.
3. **RbacSeeder (`database/seeders/RbacSeeder.php`)**:
   - Add HRM and Import permissions to `permissionMatrix()`:
     - `hrm.view`: 'Lihat Modul SDM / HRM'
     - `hrm.manage`: 'Kelola Karyawan & SDM'
     - `hrm.attendance.manage`: 'Kelola & Koreksi Absensi'
     - `hrm.payroll.view`: 'Lihat Data Gaji & Slip'
     - `hrm.payroll.manage`: 'Kelola & Eksekusi Payroll'
     - `import.view`: 'Lihat Data Exchange Hub'
     - `import.manage`: 'Eksekusi Import Data Massal'
   - Add role assignments to `roleDefinitions()`:
     - `manager`: add `hrm.view`, `hrm.manage`, `hrm.attendance.manage`, `hrm.payroll.view`, `hrm.payroll.manage`, `import.view`, `import.manage`.
     - `finance`: add `hrm.payroll.view`, `hrm.payroll.manage`, `import.view`.
     - `warehouse`: add `import.view`.
4. **HrmWebController (`app/Http/Controllers/Web/Hrm/HrmWebController.php`)**:
   - Update `middleware()` to support the new `hrm.*` permissions with fallback to `users.*` for seamless backward compatibility.
5. **Route Synchronization (`routes/owner.php`)**:
   - Align route group middleware for import and hrm so route definitions do not conflict with controller middleware.
6. **Execution & Verification**:
   - Run `php artisan db:seed --class=RbacSeeder --force` to sync permissions into the database.
   - Run `php -l` on all modified files to ensure 0 syntax errors.
   - Run `php artisan route:list --name=hrm` and `php artisan route:list --name=import` to verify route integrity.
