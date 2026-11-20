# 05. Multi-Tenant Database Architecture & Entity Specifications

**Platform**: COOCA SaaS ERP Ecosystem (`cooca_core` & `cooca_my_own`)  
**Target Environment**: `c:\laragon\www\cooca_core`  
**Database Engine**: MySQL 8.4.3 (Active Schema: `cooca_id`)  
**Document Classification**: Authoritative Engineering Architecture Specification  
**Version**: 1.0.0 (Production Verified)  

---

## 1. Executive Summary & Database Topology

The COOCA platform utilizes a high-scale, single-database multi-tenant relational architecture powered by **MySQL 8.4.3**. The active database schema (`cooca_id`) consolidates **270 operational tables** managed by **267 sequential Laravel migrations** and **257 Eloquent models**.

Rather than incurring the infrastructure overhead and migration sprawl of multi-database tenancy (database-per-tenant), COOCA enforces hard data isolation within a single relational database using **UUID Tenant Partitioning** anchored on `businesses.id`. Every tenant-scoped table is partitioned via an indexed `business_id` foreign key, combined with Eloquent global query scopes and thread-safe runtime context managers.

```
+---------------------------------------------------------------------------------------------------+
|                                   COOCA MULTI-TENANT DATABASE TOPOLOGY                            |
+---------------------------------------------------------------------------------------------------+
|                                        MYSQL 8.4.3 (`cooca_id`)                                   |
|                                       270 Tables | 267 Migrations                                 |
+---------------------------------------------------------------------------------------------------+
                                                  |
         +----------------------------------------+----------------------------------------+
         |                                                                                 |
         v                                                                                 v
+----------------------------------+                             +----------------------------------+
|      PLATFORM / SYSTEM TIER      |                             |        TENANT DATA DOMAIN        |
|  (Global / Shared Without Scope) |                             |  (Strictly Partitioned by UUID)  |
+----------------------------------+                             +----------------------------------+
| - `admins`                       |                             | - `businesses` (Root Tenant Hub) |
| - `admin_ai_visual_assets`       |                             | - `business_users` (Memberships) |
| - `billing_packages`             |                             | - `locations` (Outlets/Branches) |
| - `business_type_templates`      |                             | - `products` & `categories`      |
| - `platform_chart_of_accounts`   |                             | - `materials` & `bom_headers`    |
| - `roles` & `permissions` (Base) |                             | - `pos_orders` & `pos_order_items|
| - `migrations` & `jobs`          |                             | - `invoices` & `invoice_payments`|
| - `failed_jobs` & `sessions`     |                             | - `chart_of_accounts` & `journals|
+----------------------------------+                             | - `inventory_stocks` & `movements|
                                                                 | - `pos_shifts` & `pos_registers` |
                                                                 +----------------------------------+
```

---

## 2. Multi-Tenant Isolation Strategy & Architectural Mechanics

### 2.1 The Core Isolation Model: UUID Tenant Partitioning

1. **Deterministic Primary Keys**: The root entity `businesses` uses RFC 4122 Version 4 UUID (`char(36)`) as its primary key.
2. **Mandatory Foreign Key Partitioning**: All business-owned tables contain an explicit `business_id char(36)` column with a database index.
3. **Compound Indexes**: High-velocity tables (e.g., `pos_orders`, `stock_movements`, `inventory_stocks`) utilize compound indexes beginning with `business_id` (e.g., `['business_id', 'created_at']`, `['business_id', 'location_id', 'product_id']`), optimizing B-Tree search performance and guaranteeing indexed query isolation.

### 2.2 Eloquent Architectural Mechanics

Tenant isolation is transparently injected into the application layer through three tightly coupled primitives:

#### 1. Runtime Execution Context (`App\Support\Context`)
Located at `app/Support/Context.php`, this static container holds the verified business entity, the authenticated user's membership, the active location, and timezone state for the current request lifecycle:

```php
namespace App\Support;

final class Context
{
    private static ?Business $business = null;
    private static ?BusinessMembership $membership = null;
    private static ?Location $location = null;

    public static function business(): ?Business { return self::$business; }
    public static function businessId(): ?string { return self::$business?->id; }
    public static function hasBusiness(): bool { return self::$business !== null; }
    public static function requireBusiness(): Business {
        if (self::$business === null) {
            throw new RuntimeException('No active business context is bound to the current request.');
        }
        return self::$business;
    }
    public static function setBusiness(?Business $business, ?BusinessMembership $membership = null): void {
        self::$business = $business;
        self::$membership = $membership;
    }
}
```

#### 2. The Global Query Scope (`App\Models\Scopes\BusinessScope`)
Located at `app/Models/Scopes/BusinessScope.php`, this scope intercepts every Eloquent query builder execution:

```php
namespace App\Models\Scopes;

use App\Support\Context;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class BusinessScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Context::hasBusiness()) {
            $builder->where($model->qualifyColumn('business_id'), Context::business()->id);
        }
    }
}
```

#### 3. The Model Trait (`App\Models\Traits\BelongsToBusiness`)
Located at `app/Models/Traits/BelongsToBusiness.php`, this trait boots the scope and automatically injects `business_id` during entity persistence:

```php
namespace App\Models\Traits;

use App\Models\Business;
use App\Models\Scopes\BusinessScope;
use App\Support\Context;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBusiness
{
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope(new BusinessScope);

        static::creating(function (Model $model): void {
            if (empty($model->business_id) && Context::hasBusiness()) {
                $model->business_id = Context::business()->id;
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
```

### 2.3 Context Resolution Pipeline (`SetActiveBusinessContext`)
The HTTP middleware `App\Http\Middleware\SetActiveBusinessContext` intercepts incoming requests and binds the tenant context prior to controller execution:
1. **Route Bypass**: Requests targeting `/admin/*` (Superadmin) or `/customer/*` (Storefront Customer Portal) are exempted from tenant binding.
2. **Resolution Precedence**:
   - HTTP Header: `X-Business-Id: <uuid>` (Standard for Mobile Flutter client & REST API).
   - Web Session: `$request->session()->get('active_business_id')` (Standard for Web Backoffice).
   - User Default: `$user->active_business_id` (Database fallback).
3. **Security Authorization**: The resolved `business_id` is validated against `business_users` to confirm the authenticated user holds an active membership. If unauthorized, access is denied.

---

## 3. Superadmin Bypass & Platform Administration Architecture

System administrators operating under the platform guard (`auth:admin`) require global cross-tenant visibility for billing, audit compliance, fraud investigation, and technical maintenance.

The Superadmin bypass operates through three deliberate mechanisms:

1. **Context Immunity**: The `SetActiveBusinessContext` middleware deliberately excludes the `admin` route prefix:
   ```php
   if ($request->is('admin') || $request->is('admin/*')) {
       return $next($request);
   }
   ```
   Because no business context is bound, `Context::hasBusiness()` evaluates to `false`. Consequently, `BusinessScope::apply()` does not append the `WHERE business_id = ?` clause, allowing unrestricted global queries across all tenant tables.

2. **Explicit Eloquent Scope Removal**: In specialized reporting or data export tools where a tenant context might be ambiently present, system services invoke:
   ```php
   $allOrders = PosOrder::withoutGlobalScope(BusinessScope::class)->get();
   ```

3. **Controlled Tenant Impersonation**: When a Superadmin investigates a specific business tenant from the Superadmin Control Center (`AdminBusinessController`), the platform securely binds that tenant's context via:
   ```php
   Context::setBusiness($targetBusiness, $systemAdminMembership);
   ```
   This allows the administrator to view the workspace precisely as the business owner sees it, without modifying the underlying role definitions.

---

## 4. Entity Relationship Diagram (ERD) & Entity Dependency Graphs

```
+------------------------------------------------------------------------------------------------------------------------+
|                                              COOCA CORE RELATIONAL ERD                                                 |
+------------------------------------------------------------------------------------------------------------------------+

 [ users ] 1 ──── N [ business_users ] N ──── 1 [ businesses ] 1 ──── N [ locations ]
                              │                         │                         │
                              │ (Cashier / Shift)       │                         │ (Store/Warehouse)
                              ▼                         │                         ▼
                       [ pos_shifts ]                   │                  [ pos_registers ]
                              │                         │                         │
                              ▼                         │                         ▼
                       [ pos_orders ] ◄─────────────────┼─────────────────────────┘
                        1     │
                        │     │ 1
                        │     ▼
                        │   [ pos_order_items ] 1 ──── N [ pos_order_item_modifiers ]
                        │            │                                  │
                        │            ▼                                  ▼
                        │     [ products ] 1 ──── N [ product_modifier_groups ]
                        │       │        │                      │
                        │       │        │ 1                    ▼
                        │       │        ▼               [ modifier_groups ]
                        │       │   [ cost_models ]             │
                        │       │        │ 1                    ▼
                        │       │        ▼               [ modifier_options ]
                        │       │   [ bom_headers ]
                        │       │        │ 1
                        │       │        ▼
                        │       │   [ bom_items ] N ──── 1 [ materials ]
                        │       │                                │
                        │       ▼                                ▼
                        │   [ inventory_stocks ] ◄───────────────┘
                        │   [ stock_movements ]
                        │
                        ▼
               [ pos_order_payments ]
                        │
                        │ (AutoJournalService)
                        ▼
               [ journal_entries ] 1 ──── N [ journal_entry_lines ] N ──── 1 [ chart_of_accounts ]
```

### High-Level Entity Ownership Hierarchy
1. **Tenant Root**: `businesses`
2. **Organizational Structure**:
   - `businesses` ──1:N──> `business_users` ──N:1──> `users`
   - `businesses` ──1:N──> `locations` (Outlets, Warehouses, Central Kitchens)
   - `locations` ──1:N──> `pos_registers` (Physical/Virtual Cash Drawers)
3. **Master Catalog & Engineering**:
   - `businesses` ──1:N──> `product_categories`
   - `businesses` ──1:N──> `products` ──1:N──> `product_modifier_groups` ──N:1──> `modifier_groups` ──1:N──> `modifier_options`
   - `businesses` ──1:N──> `materials` (Raw Ingredients, Components, Consumables)
   - `products` ──1:1──> `cost_models` ──1:N──> `bom_headers` ──1:N──> `bom_items` ──N:1──> `materials`
4. **Operations & Fulfillment**:
   - `pos_registers` ──1:N──> `pos_shifts` ──1:N──> `pos_orders`
   - `pos_orders` ──1:N──> `pos_order_items` ──1:N──> `pos_order_item_modifiers`
   - `pos_orders` ──1:N──> `pos_order_payments`
5. **Inventory Ledger**:
   - `locations` + (`products` OR `materials`) ──1:1──> `inventory_stocks`
   - `inventory_stocks` ──1:N──> `stock_movements` (Immutable audit card)
   - `products` + `locations` ──1:N──> `product_batches` (Expiry & Lot tracking)
6. **Financial Ledger (Double-Entry SAK EMKM)**:
   - `businesses` ──1:N──> `chart_of_accounts`
   - `businesses` ──1:N──> `journal_entries` ──1:N──> `journal_entry_lines` ──N:1──> `chart_of_accounts`

---

## 5. Core Entity Schemas & Technical Specifications

### 5.1 `businesses` (Root Tenant Hub)
- **Table Name**: `businesses`
- **Total Columns**: 46
- **Model**: `App\Models\Business`
- **Primary Key**: `id` (`char(36)`, UUID v4)
- **Description**: Stores tenant enterprise identities, operating parameters, fiscal profiles, POS configurations, and subscription status.

| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique tenant UUID v4 identifier. |
| `name` | `varchar(255)` | NO | | NULL | Legal or commercial business name. |
| `logo_path` | `varchar(255)` | YES | | NULL | Storage disk path for brand logo. |
| `slug` | `varchar(255)` | NO | UNI | NULL | Unique URL subdomain and storefront slug. |
| `description` | `text` | YES | | NULL | Business biography and public description. |
| `is_active` | `tinyint(1)` | NO | | 1 | Operational status flag (1=Active, 0=Suspended). |
| `suspended_reason` | `varchar(255)` | YES | | NULL | Administrative reason for platform suspension. |
| `suspended_at` | `timestamp` | YES | | NULL | Timestamp when suspension took effect. |
| `phone` | `varchar(50)` | YES | | NULL | Primary corporate phone / WhatsApp number. |
| `email` | `varchar(150)` | YES | | NULL | Primary business billing / contact email. |
| `address` | `text` | YES | | NULL | Headquarters physical street address. |
| `tax_identification_number`| `varchar(50)` | YES | | NULL | NPWP / Tax identification number. |
| `tax_entity_type` | `varchar(30)` | NO | | individual | Tax structure (`individual`, `badan_pt`, `cv`). |
| `bank_name` | `varchar(100)` | YES | | NULL | Settlement bank name (BCA, Mandiri, BRI). |
| `bank_account_number` | `varchar(100)` | YES | | NULL | Bank account number for payouts. |
| `bank_account_holder` | `varchar(150)` | YES | | NULL | Registered account beneficiary name. |
| `currency` | `varchar(10)` | NO | | IDR | Operating currency code (ISO 4217). |
| `rounding_strategy` | `varchar(255)` | NO | | ROUND | POS cash rounding algorithm (`ROUND`, `CEIL`, `FLOOR`). |
| `allow_negative_stock` | `tinyint(1)` | NO | | 0 | Flag permitting sales when inventory is zero. |
| `currency_precision` | `tinyint unsigned`| NO | | 2 | Decimal precision for fiscal calculations. |
| `timezone` | `varchar(50)` | NO | | Asia/Jakarta | Default IANA timezone (`Asia/Jakarta`, `WITA`, `WIT`).|
| `pos_supervisor_pin` | `varchar(60)` | YES | | NULL | Bcrypt hash of master supervisor PIN. |
| `pos_max_cashier_discount_percent`| `decimal(5,2)`| NO | | 10.00 | Maximum discount cashier can give without PIN. |
| `pos_require_pin_for_void` | `tinyint(1)` | NO | | 1 | Requires supervisor authorization to void items. |
| `pos_require_pin_for_refund`| `tinyint(1)` | NO | | 1 | Requires supervisor authorization to refund. |
| `pos_receipt_footer_note` | `text` | YES | | NULL | Custom text printed at base of receipts. |
| `pos_receipt_wa_template` | `text` | YES | | NULL | WhatsApp template for digital receipts. |
| `pos_show_product_images` | `tinyint(1)` | NO | | 1 | Toggle product thumbnail display on POS grid. |
| `pos_auto_send_kds` | `tinyint(1)` | NO | | 0 | Automatically dispatch orders to kitchen/KDS. |
| `pos_enable_tax` | `tinyint(1)` | NO | | 0 | Enable PB1 / PPN calculation on POS orders. |
| `pos_tax_percent` | `decimal(5,2)`| NO | | 11.00 | Default tax percentage rate. |
| `pos_enable_service_charge`| `tinyint(1)` | NO | | 0 | Enable restaurant service charge calculation. |
| `pos_service_charge_percent`| `decimal(5,2)`| NO | | 5.00 | Service charge percentage rate. |
| `industry_category` | `varchar(255)` | YES | | NULL | Macro vertical (`fnb`, `retail`, `manufacturing`, `services`).|
| `business_scale` | `varchar(20)` | NO | MUL | umkm | Business scale (`micro`, `small`, `medium`, `enterprise`).|
| `template_code` | `varchar(255)` | YES | | NULL | Industry starter template code (e.g. `service_workshop`).|
| `disabled_modules` | `json` | YES | | NULL | Array of modules explicitly hidden for this tenant. |
| `settings` | `json` | YES | | NULL | Key-value store for extensible tenant preferences. |
| `operating_hours` | `json` | YES | | NULL | Weekly operational opening/closing schedule. |
| `ai_credits_balance` | `int` | NO | MUL | 0 | Pre-purchased AI Copilot & Studio token credits. |
| `is_test` | `tinyint(1)` | NO | | 0 | Indicates testing/sandbox environment tenant. |
| `is_demo` | `tinyint(1)` | NO | | 0 | Indicates pre-seeded showcase demo business. |
| `is_public_listed` | `tinyint(1)` | NO | | 1 | Visibility flag for public business directory. |
| `created_at` | `timestamp` | YES | | NULL | Record creation timestamp. |
| `updated_at` | `timestamp` | YES | | NULL | Record modification timestamp. |
| `deleted_at` | `timestamp` | YES | | NULL | Soft deletion timestamp. |

---

### 5.2 `business_users` (Tenant Membership & Employee Profile)
- **Table Name**: `business_users`
- **Total Columns**: 33
- **Model**: `App\Models\BusinessMembership`
- **Primary Key**: `id` (`char(36)`, UUID v4)
- **Description**: Manages tenant employee rosters, compensation models, PIN access, biometric facial templates, and assigned branches.

| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique membership UUID v4 identifier. |
| `business_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `businesses.id`. |
| `user_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `users.id`. |
| `role` | `varchar(255)` | NO | | owner | Primary workspace role slug (`owner`, `manager`, `cashier`, etc.). |
| `job_title` | `varchar(100)` | YES | | NULL | Position title (e.g., Head Chef, Senior Mechanic). |
| `employment_type` | `varchar(30)` | NO | | permanent | Status (`permanent`, `contract`, `daily`, `intern`). |
| `join_date` | `date` | YES | | NULL | Official commencement date. |
| `base_salary` | `decimal(15,2)`| NO | | 0.00 | Monthly gross base salary (IDR). |
| `daily_rate` | `decimal(15,2)`| NO | | 0.00 | Daily attendance wage rate. |
| `fixed_allowances` | `decimal(15,2)`| NO | | 0.00 | Fixed monthly allowances (housing, transport). |
| `variable_allowances` | `decimal(15,2)`| NO | | 0.00 | Performance and meal allowances. |
| `hourly_rate` | `decimal(15,2)`| NO | | 0.00 | Overtime calculation rate per hour. |
| `pin_hash` | `varchar(255)` | YES | | NULL | Bcrypt hash of quick 6-digit staff PIN. |
| `face_biometric_template` | `longtext` | YES | | NULL | Serialized facial vector embedding for Smart Clock-in.|
| `face_registered_at` | `timestamp` | YES | | NULL | Timestamp of biometric face enrollment. |
| `bank_name` | `varchar(50)` | YES | | NULL | Payroll disbursement bank name. |
| `bank_account_number` | `varchar(50)` | YES | | NULL | Payroll bank account number. |
| `bank_account_holder` | `varchar(100)` | YES | | NULL | Beneficiary name matching bank records. |
| `whatsapp_number` | `varchar(30)` | YES | | NULL | WhatsApp number for automated payslip delivery. |
| `nik_ktp` | `varchar(30)` | YES | | NULL | Indonesian national identity number (NIK). |
| `npwp` | `varchar(30)` | YES | | NULL | Employee tax identification number. |
| `tax_ptkp_status` | `varchar(10)` | NO | | TK/0 | PPh 21 PTKP status (`TK/0`, `K/1`, `K/2`, `K/3`). |
| `bpjs_tk_enabled` | `tinyint(1)` | NO | | 0 | Flag indicating BPJS Ketenagakerjaan deduction. |
| `bpjs_tk_number` | `varchar(50)` | YES | | NULL | BPJS Ketenagakerjaan membership card number. |
| `bpjs_kes_enabled` | `tinyint(1)` | NO | | 0 | Flag indicating BPJS Kesehatan deduction. |
| `bpjs_kes_number` | `varchar(50)` | YES | | NULL | BPJS Kesehatan membership card number. |
| `bpjs_dependents_count`| `smallint unsigned`| NO | | 0 | Number of registered family dependents. |
| `primary_location_id` | `char(36)` | YES | MUL | NULL | Foreign UUID referencing default `locations.id`. |
| `default_shift_id` | `char(36)` | YES | MUL | NULL | Foreign UUID referencing assigned `shifts.id`. |
| `attendance_mode` | `varchar(20)` | NO | | geofenced | Clock-in validation (`geofenced`, `anywhere`, `biometric`).|
| `role_id` | `char(36)` | YES | MUL | NULL | Foreign UUID referencing custom `roles.id`. |
| `created_at` | `timestamp` | YES | | NULL | Record creation timestamp. |
| `updated_at` | `timestamp` | YES | | NULL | Record modification timestamp. |

---

### 5.3 `locations` (Outlets, Warehouses & Branches)
- **Table Name**: `locations`
- **Total Columns**: 29
- **Model**: `App\Models\Location`
- **Primary Key**: `id` (`char(36)`, UUID v4)
- **Description**: Represents physical retail stores, branches, production floors, and distribution warehouses with geofencing coordinates.

| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique location UUID v4 identifier. |
| `business_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `businesses.id`. |
| `parent_id` | `char(36)` | YES | MUL | NULL | Self-referencing parent location ID for hierarchy. |
| `name` | `varchar(255)` | NO | | NULL | Commercial branch name (e.g. "Outlet Senopati"). |
| `slug` | `varchar(255)` | NO | | NULL | URL-safe location identifier. |
| `type` | `varchar(30)` | NO | | outlet | Branch vertical (`outlet`, `warehouse`, `kitchen`, `factory`).|
| `code` | `varchar(50)` | YES | | NULL | Internal short branch code (e.g. "JKT-01"). |
| `address` | `text` | YES | | NULL | Full physical street address. |
| `province` | `varchar(100)` | YES | | NULL | Indonesian administrative province. |
| `city` | `varchar(100)` | YES | | NULL | City / Regency (Kota/Kabupaten). |
| `district` | `varchar(100)` | YES | | NULL | District (Kecamatan). |
| `village` | `varchar(100)` | YES | | NULL | Urban village / sub-district (Kelurahan). |
| `postal_code` | `varchar(10)` | YES | | NULL | 5-digit postal code. |
| `timezone` | `varchar(50)` | YES | | NULL | Branch timezone if overriding business timezone. |
| `timezone_mode` | `varchar(20)` | NO | | inherit | Timezone resolution (`inherit`, `custom`). |
| `operating_hours_mode`| `varchar(20)` | NO | | inherit | Schedule mode (`inherit`, `custom`). |
| `operating_hours` | `json` | YES | | NULL | Branch-specific operational opening hours. |
| `latitude` | `decimal(10,7)`| YES | | NULL | Precise GPS latitude coordinate. |
| `longitude` | `decimal(10,7)`| YES | | NULL | Precise GPS longitude coordinate. |
| `geofence_radius_meters`| `int unsigned` | NO | | 50 | Geofence attendance radius in meters. |
| `biteship_area_id` | `varchar(100)` | YES | | NULL | Third-party logistics area mapping ID (Biteship). |
| `phone` | `varchar(50)` | YES | | NULL | Branch direct contact telephone. |
| `is_primary` | `tinyint(1)` | NO | | 0 | Flag indicating master headquarters location. |
| `is_active` | `tinyint(1)` | NO | | 1 | Operational activity status flag. |
| `pos_supervisor_pin` | `varchar(60)` | YES | | NULL | Location-specific supervisor PIN override. |
| `is_online_fulfillment`| `tinyint(1)` | NO | | 1 | Can fulfill online/omnichannel marketplace orders. |
| `allow_storefront_pickup`| `tinyint(1)`| NO | | 1 | Supports customer self-pickup (click & collect). |
| `created_at` | `timestamp` | YES | | NULL | Record creation timestamp. |
| `updated_at` | `timestamp` | YES | | NULL | Record modification timestamp. |

---

### 5.4 `products`, `product_categories` & Modifiers
- **Master Tables**: `products` (34 cols), `product_categories` (10 cols), `modifier_groups` (13 cols), `modifier_options` (10 cols), `product_modifier_groups` (5 cols).
- **Models**: `App\Models\Product`, `App\Models\ProductCategory`, `App\Models\ModifierGroup`, `App\Models\ModifierOption`.
- **Primary Keys**: `char(36)` UUID v4.

#### `products` Schema
| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique product UUID v4 identifier. |
| `business_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `businesses.id`. |
| `type` | `varchar(20)` | NO | | goods | Item classification (`goods`, `service`, `composite`).|
| `bom_consumption_type`| `varchar(30)` | NO | | no_bom | BOM behavior: `no_bom`, `consumption_on_sale`, `consumption_on_production`.|
| `category_id` | `char(36)` | YES | MUL | NULL | Foreign UUID referencing `product_categories.id`. |
| `output_unit_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing standard `units.id`. |
| `direct_material_id`| `char(36)` | YES | MUL | NULL | Optional linked material for 1:1 direct tracking. |
| `code` | `varchar(50)` | YES | | NULL | SKU or barcode alphanumeric string. |
| `name` | `varchar(255)` | NO | | NULL | Commercial product name. |
| `slug` | `varchar(255)` | NO | | NULL | URL slug for online catalog and SEO. |
| `description` | `text` | YES | | NULL | Full item marketing and ingredient description. |
| `image_path` | `varchar(500)` | YES | | NULL | Storage disk path for primary thumbnail. |
| `base_cost` | `decimal(15,2)`| NO | | 0.00 | Calculated standard HPP cost per unit. |
| `selling_price` | `decimal(15,2)`| NO | | 0.00 | Base retail selling price (IDR). |
| `wholesale_price` | `decimal(15,2)`| YES | | NULL | Discounted wholesale unit price. |
| `wholesale_min_qty`| `decimal(15,2)`| YES | | NULL | Minimum volume threshold for wholesale pricing. |
| `min_stock` | `decimal(15,2)`| NO | | 0.00 | Safety stock threshold triggering restock alert. |
| `weight` | `decimal(10,2)`| NO | | 200.00 | Physical shipping weight in grams. |
| `length` | `decimal(10,2)`| YES | | NULL | Package length in centimeters. |
| `width` | `decimal(10,2)`| YES | | NULL | Package width in centimeters. |
| `height` | `decimal(10,2)`| YES | | NULL | Package height in centimeters. |
| `is_active` | `tinyint(1)` | NO | | 1 | Global product active status flag. |
| `show_in_website` | `tinyint(1)` | NO | | 0 | Toggle visibility on public online storefront. |
| `show_in_pos` | `tinyint(1)` | NO | | 1 | Toggle visibility on POS cashier terminal. |
| `show_in_sales_order`| `tinyint(1)` | NO | | 1 | Toggle availability in B2B sales quotations/orders. |
| `show_price_on_web`| `tinyint(1)` | NO | | 1 | Display price or hide for "Contact Us" mode. |
| `is_preorder` | `tinyint(1)` | NO | | 0 | Flag indicating pre-order / make-to-order item. |
| `is_bundle` | `tinyint(1)` | NO | | 0 | Flag indicating composite promotional bundle. |
| `preorder_mode` | `varchar(30)` | NO | | customer_schedule | PO scheduling mode. |
| `preorder_lead_days`| `smallint unsigned`| NO| | 1 | Production lead time in days. |
| `business_type_hint`| `varchar(255)`| NO | | general | Industry vertical tag (`fnb`, `retail`, `mfg`, `auto`).|
| `created_at` | `timestamp` | YES | | NULL | Record creation timestamp. |
| `updated_at` | `timestamp` | YES | | NULL | Record modification timestamp. |
| `deleted_at` | `timestamp` | YES | | NULL | Soft deletion timestamp. |

---

### 5.5 `materials` & `recipes` (Bill of Materials & Cost Engineering)
- **Tables**: `materials` (14 cols), `bom_headers` (8 cols), `bom_items` (11 cols).
- **Models**: `App\Models\Material`, `App\Models\BomHeader`, `App\Models\BomItem`, `App\Models\CostModel`.
- **Primary Keys**: `char(36)` UUID v4.

#### `materials` Schema
| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique material UUID v4 identifier. |
| `business_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `businesses.id`. |
| `category_id` | `char(36)` | YES | MUL | NULL | Foreign UUID referencing `material_categories.id`.|
| `unit_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing base measurement `units.id` (e.g. gram, ml).|
| `supplier_id` | `char(36)` | YES | MUL | NULL | Primary vendor foreign UUID referencing `suppliers.id`.|
| `code` | `varchar(50)` | YES | | NULL | Material code / SKU. |
| `name` | `varchar(255)` | NO | | NULL | Raw material name (e.g. "Biji Kopi Espresso Blend").|
| `slug` | `varchar(255)` | NO | | NULL | Unique material slug. |
| `description` | `text` | YES | | NULL | Specifications and storage conditions. |
| `min_stock` | `decimal(15,2)`| NO | | 0.00 | Safety stock reorder threshold. |
| `discontinued_at` | `timestamp` | YES | | NULL | Timestamp when material was decommissioned. |
| `created_at` | `timestamp` | YES | | NULL | Record creation timestamp. |
| `updated_at` | `timestamp` | YES | | NULL | Record modification timestamp. |
| `deleted_at` | `timestamp` | YES | | NULL | Soft deletion timestamp. |

#### `bom_headers` & `bom_items` Schema
- `bom_headers`: Links to `cost_models.id` via `cost_model_id`. Contains `type` (`recipe`, `bom`, `sub_assembly`), `level` (multi-level BOM integer depth), and optional `name`.
- `bom_items`: Connects `bom_header_id` to either `material_id` or `sub_bom_header_id` (nested assembly). Contains `quantity` (`decimal(18,4)`), `waste_percentage` (`decimal(8,4)` for scrap/spillage calculation), `unit_id`, and `sort_order`.

---

### 5.6 `pos_orders` & `pos_order_items` (Omnichannel Transactions)
- **Tables**: `pos_orders` (79 cols), `pos_order_items` (19 cols), `pos_order_payments` (12 cols), `pos_order_item_modifiers` (12 cols).
- **Models**: `App\Models\PosOrder`, `App\Models\PosOrderItem`, `App\Models\PosOrderPayment`, `App\Models\PosOrderItemModifier`.
- **Primary Keys**: `char(36)` UUID v4.

#### `pos_orders` Key Architecture Fields
| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique order UUID v4 identifier. |
| `business_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `businesses.id`. |
| `location_id` | `char(36)` | YES | MUL | NULL | Outlet location UUID where transaction occurred. |
| `pos_shift_id` | `char(36)` | YES | MUL | NULL | Active cashier shift UUID at time of sale. |
| `pos_register_id` | `char(36)` | YES | MUL | NULL | Terminal/register hardware device UUID. |
| `user_id` | `char(36)` | NO | MUL | NULL | Cashier staff user UUID. |
| `customer_id` | `char(36)` | YES | MUL | NULL | Registered member customer UUID (nullable for guest).|
| `order_number` | `varchar(64)` | NO | | NULL | Sequential human-readable invoice string (e.g. `ORD-20261120-0012`).|
| `client_uuid` | `varchar(64)` | YES | MUL | NULL | Client-generated UUID for offline idempotency deduplication.|
| `order_date` | `date` | NO | | NULL | Transaction operational calendar date. |
| `status` | `varchar(30)` | NO | | completed | State: `draft`, `held`, `confirmed`, `completed`, `voided`, `refunded`.|
| `order_type` | `varchar(30)` | NO | | takeaway | Dining mode: `dine_in`, `takeaway`, `delivery`, `curbside`.|
| `sales_channel` | `varchar(30)` | NO | | dine_in | Channel: `pos`, `storefront`, `marketplace`, `whatsapp`.|
| `pos_table_id` | `char(36)` | YES | MUL | NULL | F&B dining table foreign UUID. |
| `subtotal` | `decimal(15,2)`| NO | | 0.00 | Sum of gross line item totals before adjustments. |
| `discount_amount` | `decimal(15,2)`| NO | | 0.00 | Total discount applied (cashier or voucher). |
| `tax_amount` | `decimal(15,2)`| NO | | 0.00 | Calculated tax total (e.g. 11% PPN/PB1). |
| `service_charge_amount`| `decimal(15,2)`| NO| | 0.00 | Calculated hospitality service charge total. |
| `rounding_amount` | `decimal(15,2)`| NO | | 0.00 | Cash denomination rounding adjustment. |
| `total_amount` | `decimal(15,2)`| NO | | 0.00 | Final net payable amount (Subtotal - Discount + Tax + Service + Rounding).|
| `paid_amount` | `decimal(15,2)`| NO | | 0.00 | Total monetary tender received. |
| `change_amount` | `decimal(15,2)`| NO | | 0.00 | Cash change returned to customer. |
| `total_hpp_cost` | `decimal(15,2)`| NO | | 0.00 | Sum of item COGS/HPP at moment of transaction. |
| `total_gross_profit`| `decimal(15,2)`| NO | | 0.00 | Net profit margin (`total_amount` - `total_hpp_cost`).|
| `vehicle_license_plate`| `varchar(30)`| YES | | NULL | Workshop vehicle registration plate number. |
| `vehicle_model` | `varchar(100)` | YES | | NULL | Workshop vehicle brand and model. |
| `vehicle_mileage` | `int unsigned` | YES | | NULL | Workshop vehicle odometer kilometer reading. |
| `technician_id` | `char(36)` | YES | MUL | NULL | Workshop assigned technician staff UUID. |
| `service_notes` | `text` | YES | | NULL | Workshop diagnostic inspection findings. |
| `laundry_weight_kg`| `decimal(8,2)`| YES | | NULL | Laundry vertical total scale weight in kg. |
| `void_reason` | `text` | YES | | NULL | Explanation logged when voiding transaction. |
| `voided_by` | `char(36)` | YES | MUL | NULL | Supervisor user UUID authorizing void. |
| `supervisor_approved_by`| `char(36)`| YES | MUL | NULL | Supervisor user UUID authorizing discount/override. |
| `created_at` | `timestamp` | YES | | NULL | Creation timestamp. |
| `updated_at` | `timestamp` | YES | | NULL | Modification timestamp. |

#### `pos_order_payments` Schema
Records individual tenders contributing to order settlement (supporting split tenders e.g. Cash + QRIS):
- `pos_order_id`: Foreign UUID to `pos_orders.id`.
- `payment_method`: `cash`, `qris_dynamic`, `qris_static`, `edc_debit`, `edc_credit`, `transfer`, `kasbon`.
- `store_edc_terminal_id`: Optional foreign UUID referencing `store_edc_terminals.id`.
- `amount`: Tendered amount.
- `fee_amount` / `net_amount`: Gateway/MDR transaction processing fee deduction.
- `reference_number`: TriPay reference, EDC approval code, or bank transfer reference.

---

### 5.7 `invoices` & `invoice_payments` (B2B Commercial Billing)
- **Tables**: `invoices` (33 cols), `invoice_items` (15 cols), `invoice_payments` (13 cols).
- **Models**: `App\Models\Invoice`, `App\Models\InvoiceItem`, `App\Models\InvoicePayment`.
- **Primary Keys**: `char(36)` UUID v4.

#### `invoices` Schema
| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique invoice UUID v4 identifier. |
| `business_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `businesses.id`. |
| `customer_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `customers.id`. |
| `location_id` | `char(36)` | YES | MUL | NULL | Billing origin branch UUID. |
| `sales_order_id` | `char(36)` | YES | MUL | NULL | Upstream B2B Sales Order UUID. |
| `invoice_number` | `varchar(100)` | NO | | NULL | Commercial invoice code (e.g. `INV/2026/11/008`). |
| `invoice_date` | `date` | NO | | NULL | Date of invoice issue. |
| `due_date` | `date` | NO | | NULL | Payment maturity deadline date. |
| `status` | `varchar(30)` | NO | | draft | Billing lifecycle: `draft`, `sent`, `partial`, `paid`, `overdue`, `voided`.|
| `subtotal` | `decimal(15,2)`| NO | | 0.00 | Net items total. |
| `discount_amount` | `decimal(15,2)`| NO | | 0.00 | Promotional deduction. |
| `tax_amount` | `decimal(15,2)`| NO | | 0.00 | Value Added Tax (PPN 11%). |
| `shipping_cost` | `decimal(15,2)`| NO | | 0.00 | Freight/logistics delivery fee. |
| `total_amount` | `decimal(15,2)`| NO | | 0.00 | Total gross invoice liability. |
| `paid_amount` | `decimal(15,2)`| NO | | 0.00 | Cumulative payments received. |
| `balance_due` | `decimal(15,2)`| NO | | 0.00 | Outstanding unpaid balance (`total_amount` - `paid_amount`).|
| `payment_terms` | `varchar(100)` | YES | | NULL | Credit terms (e.g. `NET 30`, `COD`). |
| `bank_details_snapshot`| `json` | YES | | NULL | Frozen bank account numbers stamped on invoice PDF. |
| `created_at` | `timestamp` | YES | | NULL | Record creation timestamp. |
| `updated_at` | `timestamp` | YES | | NULL | Record modification timestamp. |

---

### 5.8 `chart_of_accounts` & `journal_entries` (Double-Entry Financial Accounting)
- **Tables**: `chart_of_accounts` (11 cols), `journal_entries` (12 cols), `journal_entry_lines` (8 cols), `cash_accounts` (15 cols).
- **Models**: `App\Models\ChartOfAccount`, `App\Models\JournalEntry`, `App\Models\JournalEntryLine`.
- **Primary Keys**: `char(36)` UUID v4.

#### SAK EMKM Standard COA Mapping (`chart_of_accounts`)
Standard 4-digit chart of accounts seeded per business by `AutoJournalService`:

| Account Code | Account Name | Type | Normal Balance | Classification |
|---|---|---|:---:|---|
| `1-1001` | Kas Kasir (Drawer Float) | asset | debit | Current Asset |
| `1-1002` | Rekening Bank & QRIS | asset | debit | Current Asset |
| `1-1003` | Piutang Usaha (AR / Kasbon) | asset | debit | Current Asset |
| `1-1004` | Persediaan Barang Dagang (Inventory) | asset | debit | Current Asset |
| `1-1030` | Persediaan Bahan Baku (Raw Materials) | asset | debit | Current Asset |
| `1-1040` | Barang Dalam Proses (WIP) | asset | debit | Current Asset |
| `2-2001` | Hutang Usaha (Accounts Payable) | liability | credit | Current Liability |
| `2-2002` | Hutang Pajak PPN / PB1 | liability | credit | Current Liability |
| `2-2003` | Hutang Service Charge Resto | liability | credit | Current Liability |
| `3-3001` | Modal Pemilik (Owner Equity) | equity | credit | Equity |
| `3-3002` | Laba Ditahan (Retained Earnings) | equity | credit | Equity |
| `4-4001` | Pendapatan Penjualan POS | revenue | credit | Operating Revenue |
| `4-4003` | Pendapatan Toko Online Storefront | revenue | credit | Operating Revenue |
| `4-4004` | Pendapatan Marketplace Omnichannel | revenue | credit | Operating Revenue |
| `5-5001` | Beban Pokok Penjualan (HPP) | expense | debit | Direct Cost of Goods Sold |
| `5-5002` | Selisih / Kerugian Produksi Scrap | expense | debit | Direct Cost |
| `6-6001` | Beban Diskon & Promosi Penjualan | expense | debit | Operating Expense |
| `6-6002` | Beban Operasional Kas Kecil (Petty) | expense | debit | Operating Expense |
| `6-6010` | Beban Gaji Karyawan (Payroll) | expense | debit | Operating Expense |
| `6-6011` | Beban BPJS Ketenagakerjaan/Kesehatan| expense | debit | Operating Expense |

#### Transaction Balancing Rule
Every financial movement dispatches a `journal_entries` header and minimum two `journal_entry_lines`. The engine enforces:
$$\sum \text{total\_debit} \equiv \sum \text{total\_credit}$$
Zero tolerance for variance ($|\Delta| < 0.0001$).

---

### 5.9 `stock_movements`, `inventory_stocks` & `product_batches`
- **Tables**: `inventory_stocks` (11 cols), `stock_movements` (23 cols), `product_batches` (11 cols).
- **Models**: `App\Models\InventoryStock`, `App\Models\StockMovement`, `App\Models\ProductBatch`.
- **Primary Keys**: `char(36)` UUID v4.

#### `inventory_stocks` Schema
Stores real-time quantity snapshots per location and item (polymorphic to either `product_id` or `material_id`):
- `business_id`: UUID referencing `businesses.id`.
- `location_id`: UUID referencing `locations.id`.
- `product_id`: Optional UUID referencing `products.id`.
- `material_id`: Optional UUID referencing `materials.id`.
- `quantity`: Current physical stock level (`decimal(15,4)`).
- `reserved_quantity`: Stock locked in pending orders/production (`decimal(15,4)`).
- `last_cost` / `avg_purchase_cost`: Moving average purchase cost per unit.

#### `stock_movements` Schema (The Immutable Inventory Card)
| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique mutation UUID v4 identifier. |
| `business_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `businesses.id`. |
| `location_id` | `char(36)` | NO | MUL | NULL | Branch location UUID where stock changed. |
| `material_id` | `char(36)` | YES | MUL | NULL | Material UUID (if raw ingredient). |
| `product_id` | `char(36)` | YES | MUL | NULL | Product UUID (if finished good). |
| `movement_type` | `varchar(30)` | NO | | NULL | Trigger: `sale`, `purchase_receipt`, `adjustment`, `transfer_in`, `transfer_out`, `production_consume`, `production_yield`.|
| `reference_id` | `varchar(64)` | YES | | NULL | Source transaction UUID (e.g. `pos_orders.id`).|
| `reference_number`| `varchar(100)`| YES | | NULL | Human readable reference string. |
| `quantity_change`| `decimal(15,4)`| NO | | NULL | Signed delta (+10.0000 or -2.0000). |
| `balance_before` | `decimal(15,4)`| YES | | NULL | Inventory balance immediately prior to movement. |
| `balance_after` | `decimal(15,4)`| NO | | 0.0000 | New calculated inventory balance after movement. |
| `unit_cost` | `decimal(15,2)`| NO | | 0.00 | Cost per unit at moment of mutation. |
| `total_cost` | `decimal(15,2)`| NO | | 0.00 | Total financial valuation (`quantity_change` * `unit_cost`).|
| `batch_number` | `varchar(100)` | YES | | NULL | Lot number for expiry tracking. |
| `expiry_date` | `date` | YES | | NULL | Expiration date of batch. |
| `created_by` | `char(36)` | YES | MUL | NULL | Staff user UUID initiating movement. |
| `created_at` | `timestamp` | YES | | NULL | Mutation timestamp. |

---

### 5.10 `pos_shifts` & `pos_registers` (Cash Management & Terminal Registry)
- **Tables**: `pos_shifts` (22 cols), `pos_registers` (13 cols).
- **Models**: `App\Models\PosShift`, `App\Models\PosRegister`.
- **Primary Keys**: `char(36)` UUID v4.

#### `pos_shifts` Schema
Controls cashier session lifecycle, drawer float, denomination reconciliation, and cash variance logging:

| Column Name | Data Type | Nullable | Key / Index | Default | Description |
|---|---|:---:|:---:|---|---|
| `id` | `char(36)` | NO | PRI | NULL | Unique shift UUID v4 identifier. |
| `business_id` | `char(36)` | NO | MUL | NULL | Foreign UUID referencing `businesses.id`. |
| `pos_register_id`| `char(36)` | YES | MUL | NULL | Hardware register device UUID. |
| `location_id` | `char(36)` | YES | MUL | NULL | Branch outlet UUID. |
| `user_id` | `char(36)` | NO | MUL | NULL | Cashier staff user UUID. |
| `opened_at` | `timestamp` | NO | | NULL | Shift opening timestamp. |
| `closed_at` | `timestamp` | YES | | NULL | Shift closing timestamp (NULL if active). |
| `opening_cash` | `decimal(15,2)`| NO | | 0.00 | Initial opening cash float in drawer. |
| `opening_denominations`| `json` | YES | | NULL | Breakdown of opening cash (100k, 50k, 20k count).|
| `closing_cash_actual`| `decimal(15,2)`| YES| | NULL | Physical cash counted by cashier at close. |
| `closing_denominations`| `json` | YES | | NULL | Breakdown of counted closing cash notes. |
| `closing_cash_expected`| `decimal(15,2)`| YES| | NULL | System calculated drawer cash (`opening` + `cash_sales` + `cash_in` - `cash_out`).|
| `cash_difference` | `decimal(15,2)`| YES | | NULL | Variance: `closing_cash_actual` - `closing_cash_expected`.|
| `total_cash_sales`| `decimal(15,2)`| NO | | 0.00 | Total cash sales processed in shift. |
| `total_non_cash_sales`| `decimal(15,2)`| NO | | 0.00 | Total electronic sales (QRIS, EDC, Transfer).|
| `total_cash_in` | `decimal(15,2)`| NO | | 0.00 | Total drawer cash deposits during shift. |
| `total_cash_out` | `decimal(15,2)`| NO | | 0.00 | Total drawer cash withdrawals / petty expenses. |
| `status` | `varchar(20)` | NO | | open | Session status: `open`, `closed`. |
| `notes` | `text` | YES | | NULL | Operational shift handover remarks. |
| `cashier_notes` | `text` | YES | | NULL | Cashier explanation regarding cash variance. |
| `created_at` | `timestamp` | YES | | NULL | Record creation timestamp. |
| `updated_at` | `timestamp` | YES | | NULL | Record modification timestamp. |

---

## 6. Multi-Tenant Concurrency & Isolation Hardening Specifications

Empirical adversarial stress-testing identified critical edge cases and failure modes in multi-tenant concurrency, offline batch synchronization, and scope execution boundaries. Below are the authoritative technical specifications, root cause analyses, and mandatory architectural mitigations.

### 6.1 Database Idempotency & Composite Unique Constraint on `pos_orders`

#### 1. Vulnerability Analysis & TOCTOU Race Condition
In `database/migrations/2026_10_08_091500_create_mobile_device_tokens_and_pos_sync_fields.php` (line 38), the offline tracking column was declared with a standard non-unique index:
```php
$table->string('client_uuid', 64)->nullable()->after('order_number')->index();
```
Physical inspection of the index table via `SHOW INDEXES FROM pos_orders WHERE Column_name = 'client_uuid'` confirms `Non_unique: 1` (`Key_name: pos_orders_client_uuid_index`). 

In `App\Http\Controllers\Api\V1\Pos\PosSyncApiController::batchSync` (lines 74–124), deduplication was handled exclusively at the application layer:
```php
$existingOrder = PosOrder::where('business_id', $business->id)
    ->where('client_uuid', $clientUuid)
    ->first();

if ($existingOrder !== null) {
    // Return duplicate_skipped
}
$order = $this->orderService->checkout($business, $attributes);
$order->client_uuid = $clientUuid;
$order->save();
```
Under concurrent batch synchronization requests from multiple retry workers, flaky network retries, or distributed mobile terminals, this Time-Of-Check to Time-Of-Use (TOCTOU) pattern allows duplicate requests to pass the `first()` check simultaneously before the first transaction commits. This results in duplicate order records, double inventory deductions, corrupted shift cash balances, and unbalanced general ledger entries.

#### 2. Mandatory Architectural Mitigation
1. **Database Schema Hardening**: Execute a dedicated migration replacing the single-column non-unique index with a composite unique index scoped by tenant:
   ```php
   Schema::table('pos_orders', function (Blueprint $table) {
       $table->dropIndex(['client_uuid']);
       $table->unique(['business_id', 'client_uuid'], 'pos_orders_business_client_uuid_unique');
   });
   ```
2. **Payload-Level Validation**: In `PosSyncApiController::batchSync`, enforce in-payload uniqueness:
   ```php
   $request->validate([
       'transactions' => ['required', 'array'],
       'transactions.*.client_uuid' => ['required', 'string', 'distinct'],
   ]);
   ```
3. **Database Exception Trapping**: Wrap checkout creation in a `try...catch (QueryException $e)` block that catches MySQL error 1062 (Duplicate entry) and safely flags the transaction as `duplicate_skipped` without rolling back the entire batch.

---

### 6.2 Historical Timestamp Preservation in Offline Batch Checkout

#### 1. Vulnerability Analysis
In `App\Domain\Pos\PosOrderService::checkout()` (lines 330, 380, 864, 1231, 1355), the order date was unconditionally hardcoded to the server's current operational date:
```php
'order_date' => Carbon::today()->toDateString(),
```
While `PosSyncApiController::batchSync` parses `$tx['offline_created_at']` into `$orderDate` and passes it in `$attributes['order_date']` (lines 96–118), `PosOrderService::checkout()` completely discarded this parameter. When an offline POS terminal operates during network blackouts over a weekend and syncs on Monday morning, all transactions are attributed to Monday. This introduces critical accounting discrepancies, distorts daily gross revenue metrics, invalidates cashier shift handover reconciliations, and corrupts tax reporting (PPh Final / PPN).

#### 2. Mandatory Architectural Mitigation
Refactor `PosOrderService::checkout()` to respect historical timestamps with fallback to today:
```php
$orderDate = !empty($attributes['order_date']) 
    ? Carbon::parse($attributes['order_date'])->toDateString() 
    : Carbon::today()->toDateString();

$orderTime = !empty($attributes['offline_created_at']) 
    ? Carbon::parse($attributes['offline_created_at']) 
    : Carbon::now();

// Persist in pos_orders creation
$order = PosOrder::create([
    'business_id' => $business->id,
    'location_id' => $locationId,
    'order_number' => $orderNumber,
    'order_date' => $orderDate,
    'created_at' => $orderTime,
    'subtotal' => $subtotal,
    'tax_amount' => $taxAmount,
    'total_amount' => $totalAmount,
    'status' => 'completed',
]);
```
This guarantees that inventory valuation, journal postings, and sales history reflect the true chronological occurrence of the transaction.

---

### 6.3 Global Multi-Tenant Scope Fail-Closed Hardening (`BusinessScope`)

#### 1. Vulnerability Analysis & Fail-Open Risk
In `App\Models\Scopes\BusinessScope.php` (lines 19–24), the global scope was implemented as follows:
```php
public function apply(Builder $builder, Model $model): void
{
    if (Context::hasBusiness()) {
        $builder->where($model->qualifyColumn('business_id'), Context::business()->id);
    }
}
```
When `Context::hasBusiness()` evaluates to `false` (such as in unauthenticated HTTP requests, asynchronous background jobs without explicit context initialization, or CLI artisan commands), the scope executed **zero query filtering**. Empirical testing confirmed that executing `Context::flush()` followed by `Product::count()` returned 179 products—the total count across all tenants in `cooca_id`. This fail-open behavior risks catastrophic cross-tenant data leaks if an unauthenticated or misconfigured endpoint queries scoped models.

#### 2. Mandatory Architectural Mitigation
Enforce a strict fail-closed security policy. When no business context is bound, Eloquent queries on multi-tenant models must evaluate to an empty result set (`1 = 0`) unless explicitly unscoped via `withoutGlobalScope(BusinessScope::class)`:
```php
namespace App\Models\Scopes;

use App\Support\Context;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BusinessScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! Context::hasBusiness()) {
            // Fail closed: Prevent accidental unscoped data leakage across tenants
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->where($model->qualifyColumn('business_id'), Context::business()->id);
    }
}
```
Background queues, system seeders, and platform maintenance commands that legitimately require cross-tenant access must explicitly call `Model::withoutGlobalScope(BusinessScope::class)` or bind a valid system context.

---

### 6.4 Cross-Tenant Authorization & IDOR Defense Hardening

#### 1. Insecure Direct Object Reference (IDOR) in Template Application
In `routes/api.php` (line 1044) and `App\Http\Controllers\Api\V1\BusinessTypeTemplateController.php` (lines 43–57):
```php
public function apply(Request $request, Business $business, BusinessTemplateService $service): JsonResponse
{
    $validated = $request->validate([
        'template_code' => ['required', 'string', 'exists:business_type_templates,code'],
    ]);
    $template = BusinessTypeTemplate::where('code', $validated['template_code'])->firstOrFail();
    $result = $service->apply($business, $template);
    return response()->json(['success' => true, 'data' => $result]);
}
```
Route `POST /api/v1/businesses/{business:slug}/apply-template` was guarded only by `auth:sanctum`. Because Route Model Binding resolved `$business` by `slug` without verifying that the authenticated user was an owner or active member of that specific business, any authenticated user could supply a victim tenant's slug and overwrite its cost components, chart of accounts, and categories.

**Mandatory Mitigation**: Enforce strict tenant ownership validation in the controller:
```php
if ($business->id !== Context::businessId() || ! Context::isOwner()) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized: You do not have owner authority over this business entity.'
    ], \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
}
```

#### 2. Cross-Tenant Privilege Escalation in Member Management
In `routes/api.php` (lines 1048–1054) and `App\Http\Controllers\Api\V1\MemberController.php`:
```php
Route::post('/businesses/{business:slug}/members', [MemberController::class, 'store'])
    ->middleware('business.active', 'require.role:owner,admin');
```
The `RequireRole` middleware checks `Context::role()`, which reflects the user's *currently active business context*, not the `{business:slug}` parameter in the URL route. If an owner of Business A issued a request to `/businesses/business-b/members`, the middleware passed because the user had the `owner` role in Business A. `MemberController::store()` then attached the target user to Business B with arbitrary roles.

**Mandatory Mitigation**: In `MemberController` methods (`store`, `update`, `destroy`), assert exact tenant match between route model binding and runtime context:
```php
if ($business->id !== Context::businessId()) {
    return response()->json([
        'success' => false,
        'message' => 'Cross-tenant membership modification is strictly forbidden.'
    ], \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
}
```
This guarantees that tenant boundaries remain impenetrable against IDOR and horizontal privilege escalation.

---

## 7. Migration & Seeder System Architecture

### 7.1 Migration Lifecycle & Ordering
The database features **267 discrete migrations** residing in `database/migrations/`. Migrations strictly adhere to chronological foreign key dependency ordering:
1. **Core System Tables (`2026_08_17_*`)**:
   - `000000_create_users_table.php`
   - `000001_create_businesses_table.php`
   - `000002_create_business_users_table.php`
   - `000003_create_roles_and_permissions_tables.php`
   - `000004_create_locations_table.php`
2. **Master Catalogs (`2026_08_18_*`)**:
   - Units, categories, products, materials, price tiers.
3. **Engineering & BOM (`2026_08_19_*`)**:
   - Cost models, BOM headers, BOM items, machines, labor rates.
4. **Operations & Hardware (`2026_08_20_*`)**:
   - Registers, shifts, printers, tables, kitchen routing.
5. **Transactions & Inventory (`2026_08_21_*`)**:
   - Orders, order items, order payments, stock movements, stock opnames.
6. **Financial Accounting (`2026_08_22_*`)**:
   - Chart of accounts, journal entries, journal entry lines, cash accounts.
7. **Vertical Extensions (`2026_09_*` through `2026_11_*`)**:
   - Vehicle service schemas, laundry scales, prescription pharmacy fields, Biteship logistics, AI studio credits, and WhatsApp gateways.

### 7.2 Idempotent Seeding Architecture
The codebase contains **27 production-ready seeder classes** under `database/seeders/`. To maintain zero cross-tenant contamination and prevent accidental production data destruction, seeders adhere to strict idempotent design patterns:
1. **Zero Truncation**: No seeder issues `DB::table(...)->truncate()`. Seeders utilize `updateOrCreate()` or `firstOrCreate()` anchored on deterministic unique keys (slug, code, SKU).
2. **Deterministic UUID Seeding**: Demo business tenants and system roles utilize stable deterministic UUIDs (e.g. `UserSeeder.php`), ensuring idempotent re-runs never orphan related records.
3. **Multi-Industry Showcase Seeding**: `TwentyIndustriesShowcaseSeeder` and `ServicesAndAutomotiveClusterSeeder` instantiate realistic transactional data by executing genuine business logic pipelines (via `AutoJournalService` and stock deduction engines) rather than inserting synthetic mock rows.

---

## 8. Data Integrity, Concurrency & Soft Deletion Policies

1. **Transactional Enclosure**: All multi-table business mutations (such as checkout, goods receiving, production completion, and voiding) are enclosed within `DB::transaction()` blocks. If an unhandled exception or constraint failure occurs, all database changes rollback automatically.
2. **Concurrency & Locking**: Critical stock movements and cash shift updates utilize pessimistic row-level locking (`lockForUpdate()`) to prevent race conditions during high-volume cashier checkouts.
3. **Soft Deletions**: Master entity tables (`businesses`, `products`, `materials`, `product_categories`, `customers`, `suppliers`, `invoices`, `pos_orders`) implement Laravel's `SoftDeletes` trait (`deleted_at` timestamp). Accidental deletions preserve all relational foreign keys for historical auditability and double-entry accounting integrity.
4. **Foreign Key Restraints**: All tenant partitions enforce relational constraints (`ON DELETE CASCADE` for parent-child composite items like `pos_order_items`, and `ON DELETE RESTRICT` for financial and inventory audit records like `stock_movements` and `journal_entry_lines`).
