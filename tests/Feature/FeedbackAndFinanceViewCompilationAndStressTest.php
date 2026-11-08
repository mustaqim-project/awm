<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Accounting\AutoJournalService;
use App\Models\BugReport;
use App\Models\Business;
use App\Models\FeatureRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class FeedbackAndFinanceViewCompilationAndStressTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $financeUser;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->business = Business::create([
            'name' => 'Bisnis Layout Stress Test',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        (new AutoJournalService())->ensureStandardAccounts($this->business);

        // Owner
        $this->owner = User::create([
            'name' => 'Owner Stress Test',
            'email' => 'owner.stress@cooca.test',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);
        $ownerRole = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        // Finance Staff
        $this->financeUser = User::create([
            'name' => 'Finance Staff Stress',
            'email' => 'finance.stress@cooca.test',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);
        $financeRole = Role::where('slug', 'finance')->first();
        $this->business->users()->attach($this->financeUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'finance',
            'role_id' => $financeRole?->id,
        ]);
        $this->financeUser->update(['active_business_id' => $this->business->id]);

        // Cashier
        $this->cashier = User::create([
            'name' => 'Cashier Stress',
            'email' => 'cashier.stress@cooca.test',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);
        $cashierRole = Role::where('slug', 'cashier')->first();
        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole?->id,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);
    }

    /**
     * Test 1: Syntax integrity check on all 19 Blade views, 3 docs views, and 4 lang files using php -l.
     */
    public function test_all_19_blade_views_and_lang_files_pass_php_lint(): void
    {
        $targetPaths = [
            resource_path('views/app/feedback/index.blade.php'),
            resource_path('views/app/feedback/create.blade.php'),
            resource_path('views/app/feedback/show.blade.php'),
            resource_path('views/app/finance/expenses.blade.php'),
            resource_path('views/app/finance/journals.blade.php'),
            resource_path('views/app/finance/payables.blade.php'),
            resource_path('views/app/finance/receivables.blade.php'),
            resource_path('views/app/finance/cash-bank/index.blade.php'),
            resource_path('views/app/finance/cash-bank/ledger.blade.php'),
            resource_path('views/app/finance/edc-terminals/index.blade.php'),
            resource_path('views/app/finance/external-reconciliation/index.blade.php'),
            resource_path('views/app/finance/payout-accounts/index.blade.php'),
            resource_path('views/app/finance/settlements/index.blade.php'),
            resource_path('views/app/finance/settlements/show.blade.php'),
            resource_path('views/app/finance/accounting/coa.blade.php'),
            resource_path('views/app/finance/accounting/balance-sheet.blade.php'),
            resource_path('views/app/finance/accounting/trial-balance.blade.php'),
            resource_path('views/app/finance/accounting/general-ledger.blade.php'),
            resource_path('views/app/finance/accounting/reconciliation.blade.php'),
            resource_path('views/app/docs/index.blade.php'),
            resource_path('views/app/docs/partials/sec_feedback.blade.php'),
            resource_path('views/app/docs/partials/sec_finance.blade.php'),
            base_path('lang/id/feedback.php'),
            base_path('lang/en/feedback.php'),
            base_path('lang/id/finance.php'),
            base_path('lang/en/finance.php'),
        ];

        $this->assertCount(26, $targetPaths, 'Expected exactly 19 module views + 3 docs views + 4 lang dictionaries');

        foreach ($targetPaths as $path) {
            $this->assertFileExists($path, "Target file {$path} does not exist.");
            $output = [];
            $exitCode = 0;
            exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $exitCode);
            $this->assertSame(0, $exitCode, "Syntax error in {$path}: " . implode("\n", $output));
        }
    }

    /**
     * Test 2: Route Link Verification.
     * All direct route(...) calls or fallback branches in sec_feedback, sec_finance, and docs/index must resolve.
     */
    public function test_all_route_links_in_docs_exist_and_resolve(): void
    {
        $filesToInspect = [
            resource_path('views/app/docs/partials/sec_feedback.blade.php'),
            resource_path('views/app/docs/partials/sec_finance.blade.php'),
            resource_path('views/app/docs/index.blade.php'),
        ];

        foreach ($filesToInspect as $file) {
            $content = File::get($file);

            if (preg_match_all("/route\(\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches)) {
                $uniqueRoutes = array_unique($matches[1]);
                foreach ($uniqueRoutes as $routeName) {
                    if (!Route::has($routeName)) {
                        // If it doesn't exist directly, it MUST be wrapped in Route::has('...') ? route('...') : route('fallback')
                        $guardedPattern = "/Route::has\(['\"]" . preg_quote($routeName, '/') . "['\"]\)\s*\?\s*route\(['\"]" . preg_quote($routeName, '/') . "['\"]\)\s*:\s*route\(['\"]([^'\"]+)['\"]\)/";
                        $this->assertMatchesRegularExpression(
                            $guardedPattern,
                            $content,
                            "File {$file} contains call to non-existent route '{$routeName}' without a valid Route::has fallback guard"
                        );

                        // Also verify the fallback route actually exists
                        preg_match($guardedPattern, $content, $guardMatch);
                        $fallbackRoute = $guardMatch[1];
                        $this->assertTrue(
                            Route::has($fallbackRoute),
                            "Fallback route '{$fallbackRoute}' for '{$routeName}' does not exist in Laravel"
                        );
                    } else {
                        $this->assertTrue(Route::has($routeName));
                    }
                }
            }
        }
    }

    /**
     * Test 3: Translation Dictionaries Verification.
     * Verify all newly introduced keys in feedback and finance exist in BOTH lang/id and lang/en.
     */
    public function test_translation_dictionaries_have_full_parity_between_id_and_en(): void
    {
        // 1. Finance Expense Categories
        $financeKeys = [
            'cat_operational',
            'cat_operational_desc',
            'cat_utilities',
            'cat_utilities_desc',
            'cat_internet_phone_desc',
            'cat_supplies',
            'cat_supplies_desc',
            'cat_logistics',
            'cat_logistics_desc',
            'cat_rent',
            'cat_rent_desc',
            'cat_maintenance',
            'cat_maintenance_desc',
            'cat_marketing',
            'cat_marketing_desc',
            'cat_bank_admin',
            'cat_bank_admin_desc',
            'empty_allocations',
        ];

        foreach ($financeKeys as $key) {
            $idTrans = Lang::get("finance.{$key}", [], 'id');
            $enTrans = Lang::get("finance.{$key}", [], 'en');

            $this->assertNotEquals("finance.{$key}", $idTrans, "Missing Indonesian translation for finance.{$key}");
            $this->assertNotEquals("finance.{$key}", $enTrans, "Missing English translation for finance.{$key}");
            $this->assertNotEmpty($idTrans);
            $this->assertNotEmpty($enTrans);
        }

        // 2. Feedback Categories, Severities, Priorities
        $feedbackKeys = [
            'severity_low',
            'severity_normal',
            'severity_high',
            'severity_critical',
            'priority_low',
            'priority_normal',
            'priority_high',
            'cat_bug',
            'cat_performance',
            'cat_security',
            'cat_ui',
            'cat_data',
            'cat_reporting',
            'cat_inventory',
            'cat_sales',
            'cat_finance',
            'cat_ai',
            'cat_integration',
            'cat_mobile',
            'cat_other',
            'dashboard',
            'portal',
        ];

        foreach ($feedbackKeys as $key) {
            $idTrans = Lang::get("feedback.{$key}", [], 'id');
            $enTrans = Lang::get("feedback.{$key}", [], 'en');

            $this->assertNotEquals("feedback.{$key}", $idTrans, "Missing Indonesian translation for feedback.{$key}");
            $this->assertNotEquals("feedback.{$key}", $enTrans, "Missing English translation for feedback.{$key}");
            $this->assertNotEmpty($idTrans);
            $this->assertNotEmpty($enTrans);
        }
    }

    /**
     * Test 4: Documentation renders properly in both Indonesian and English without missing strings or errors.
     */
    public function test_documentation_page_renders_cleanly_for_owner_in_both_locales(): void
    {
        foreach (['id', 'en'] as $locale) {
            App::setLocale($locale);

            $response = $this->actingAs($this->owner)->get(route('docs.index'));

            $response->assertOk();
            $response->assertSee('id="sec-feedback"', false);
            $response->assertSee('id="sec-finance"', false);
            $response->assertSee(route('feedback.bugs.create'), false);
            $response->assertSee(route('finance.cash-bank.index'), false);
            $response->assertDontSee('RouteNotFoundException');
            $response->assertDontSee('Undefined variable');
        }
    }

    /**
     * Test 5: Adversarial Layout & Anti-Slop Audit on Cash-Bank.
     * Verify single primary action button in cash-bank/index.blade.php and no duplicate "Tambah Akun" button.
     */
    public function test_cash_bank_view_layout_has_single_primary_and_no_duplicate_add_button(): void
    {
        $cashBankView = File::get(resource_path('views/app/finance/cash-bank/index.blade.php'));

        // Check that Kas Masuk is the sole primary solid button with brand color #007AFF
        $this->assertStringContainsString('bg-[#007AFF]', $cashBankView);
        $this->assertStringContainsString('text-white', $cashBankView);

        // Check that Kas Keluar and Transfer Kas are secondary variants (not competing primary solid)
        $this->assertStringNotContainsString('bg-[#34C759] text-white', $cashBankView, 'Found old solid green Kas Masuk button');
        $this->assertStringNotContainsString('bg-[#FF3B30] text-white', $cashBankView, 'Found old solid red Kas Keluar button');

        // Check that duplicate "Tambah Akun" in accounts list was eliminated (only 1 occurrence in header toolbar)
        $this->assertSame(1, substr_count($cashBankView, 'btn_add_account'), 'Expected exactly one Tambah Akun button in header toolbar, duplicate removed');
    }

    /**
     * Test 6: Apple HIG Touch Targets (min 44px) verification in modified views.
     */
    public function test_touch_targets_meet_apple_hig_minimum_44px(): void
    {
        $viewsToCheck = [
            resource_path('views/app/finance/accounting/coa.blade.php') => ['min-h-[44px]', 'min-w-[44px]'],
            resource_path('views/app/finance/accounting/reconciliation.blade.php') => ['min-h-[44px]', 'min-w-[44px]'],
            resource_path('views/app/finance/edc-terminals/index.blade.php') => ['min-h-[44px]', 'min-w-[44px]'],
            resource_path('views/app/finance/payout-accounts/index.blade.php') => ['min-h-[44px]', 'min-w-[44px]'],
            resource_path('views/app/finance/settlements/show.blade.php') => ['pb-28 sm:pb-32 lg:pb-10'],
        ];

        foreach ($viewsToCheck as $viewPath => $requiredClasses) {
            $content = File::get($viewPath);
            foreach ($requiredClasses as $cls) {
                $this->assertStringContainsString(
                    $cls,
                    $content,
                    "Expected class '{$cls}' to enforce 44px touch targets or safe bottom padding in {$viewPath}"
                );
            }
        }
    }

    /**
     * Test 7: Double Title Elimination in Feedback Views.
     */
    public function test_feedback_views_have_zero_redundant_card_titles(): void
    {
        $createView = File::get(resource_path('views/app/feedback/create.blade.php'));
        $showView = File::get(resource_path('views/app/feedback/show.blade.php'));

        // create.blade.php should not contain duplicate <h2> with title
        $this->assertStringNotContainsString('<h2 class="text-base font-bold text-black dark:text-white mb-1">', $createView);

        // show.blade.php should not contain duplicate <h1> with $item->title
        $this->assertStringNotContainsString('<h1 class="text-lg font-bold text-black dark:text-white">', $showView);
    }

    /**
     * Test 8: Feedback Views live rendering with real data under both ID and EN locales.
     */
    public function test_feedback_views_render_under_both_locales_without_errors(): void
    {
        $bug = BugReport::create([
            'business_id' => $this->business->id,
            'reporter_id' => $this->owner->id,
            'title' => 'Kendala printer kasir POS',
            'category' => 'bug',
            'severity' => 'high',
            'status' => 'open',
            'priority' => 'high',
            'description' => 'Printer tidak mencetak struk saat tombol bayar ditekan.',
            'steps_to_reproduce' => "1. Klik Bayar\n2. Dialog print muncul",
            'expected_behavior' => 'Struk tercetak',
            'actual_behavior' => 'Struk tidak keluar',
            'environment' => 'Android Tablet',
        ]);

        $feature = FeatureRequest::create([
            'business_id' => $this->business->id,
            'requester_id' => $this->owner->id,
            'title' => 'Usulan Fitur Notifikasi WhatsApp Multi-Outlet',
            'category' => 'sales',
            'status' => 'submitted',
            'priority' => 'normal',
            'description' => 'Ingin setiap pesanan masuk otomatis kirim notifikasi ke manajer cabang.',
            'business_value' => 'Mempercepat respon pesanan pelanggan.',
            'use_case' => 'Kasir mencatat pesanan, WA otomatis terkirim.',
        ]);

        foreach (['id', 'en'] as $locale) {
            App::setLocale($locale);

            // Bug Index
            $bugIndexRes = $this->actingAs($this->owner)->get(route('feedback.bugs.index'));
            $bugIndexRes->assertOk();
            $bugIndexRes->assertSee($bug->title);

            // Bug Create
            $bugCreateRes = $this->actingAs($this->owner)->get(route('feedback.bugs.create'));
            $bugCreateRes->assertOk();

            // Bug Show
            $bugShowRes = $this->actingAs($this->owner)->get(route('feedback.bugs.show', $bug));
            $bugShowRes->assertOk();
            $bugShowRes->assertSee($bug->title);

            // Feature Index
            $featureIndexRes = $this->actingAs($this->owner)->get(route('feedback.features.index'));
            $featureIndexRes->assertOk();
            $featureIndexRes->assertSee($feature->title);

            // Feature Create
            $featureCreateRes = $this->actingAs($this->owner)->get(route('feedback.features.create'));
            $featureCreateRes->assertOk();

            // Feature Show
            $featureShowRes = $this->actingAs($this->owner)->get(route('feedback.features.show', $feature));
            $featureShowRes->assertOk();
            $featureShowRes->assertSee($feature->title);
        }
    }
}
