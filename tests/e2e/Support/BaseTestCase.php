<?php

declare(strict_types=1);

namespace Tests\E2E\Support;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
use Illuminate\Testing\TestResponse;
use PHPUnit\TextUI\Configuration\Builder as PHPUnitConfigBuilder;
use PHPUnit\TextUI\Configuration\Registry as PHPUnitConfigRegistry;
use Tests\E2E\Support\Assertions\AccountingAssertions;
use Tests\E2E\Support\Assertions\DatabaseAssertions;
use Tests\E2E\Support\Assertions\DocumentationAssertions;
use Tests\E2E\Support\Assertions\ScreenshotAssertions;
use Tests\E2E\Support\Helpers\ApiClientHelper;
use Tests\E2E\Support\Helpers\TenantHelper;
use Tests\E2E\Support\Helpers\TransactionHelper;
use Tests\TestCase;

abstract class BaseTestCase extends TestCase
{
    use DatabaseAssertions;
    use DocumentationAssertions;
    use AccountingAssertions;
    use ScreenshotAssertions;

    protected function setUp(): void
    {
        // Ensure PHPUnit configuration registry is initialized (prevents PHPUnit 12 lifecycle flush assertion error)
        if (class_exists(PHPUnitConfigRegistry::class)) {
            try {
                PHPUnitConfigRegistry::get();
            } catch (\Throwable) {
                try {
                    (new PHPUnitConfigBuilder)->build([]);
                } catch (\Throwable) {
                    // Ignore configuration build errors
                }
            }
        }

        parent::setUp();

        // Ensure clean context before test execution
        Context::flush();
    }

    protected function tearDown(): void
    {
        // Non-destructive invariants: ensure transaction rollback and transient tenant cleanup
        try {
            TransactionHelper::rollbackAll();
        } catch (\Throwable) {
            // Ignore rollback exceptions during cleanup
        }

        try {
            TenantHelper::cleanupTransientTenants();
        } catch (\Throwable) {
            // Ignore tenant cleanup exceptions
        }

        Context::flush();

        parent::tearDown();
    }

    /**
     * Non-destructive transactional wrapper.
     * Executes the callback inside an isolated DB transaction and guarantees rollback in finally.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function runInTransaction(callable $callback): mixed
    {
        return TransactionHelper::runIsolated($callback);
    }

    /**
     * Switch active tenant context by alias, slug, or UUID.
     */
    public function setTenantContext(string $aliasOrId, ?User $user = null): ?Business
    {
        $businessId = TenantHelper::getBusinessId($aliasOrId);
        if ($businessId === null) {
            return null;
        }

        $business = Business::find($businessId);
        if ($business === null) {
            return null;
        }

        $membership = null;
        if ($user !== null) {
            $membership = BusinessMembership::where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->first();
        }

        Context::setBusiness($business, $membership);
        session(['active_business_id' => $business->id]);

        return $business;
    }

    /**
     * Retrieve the Eloquent Business model instance by alias.
     */
    public function getTenantBusiness(string $alias): ?Business
    {
        return TenantHelper::getBusiness($alias);
    }

    /**
     * Retrieve the Business ID string by alias.
     */
    public function getTenantBusinessId(string $alias): ?string
    {
        return TenantHelper::getBusinessId($alias);
    }

    /**
     * Create an isolated transient tenant for isolated mutation testing.
     *
     * @param array<string, mixed> $attributes
     */
    public function createTransientTenant(array $attributes = []): Business
    {
        return TenantHelper::createTransientTenant($attributes);
    }

    /**
     * Authenticate as a user and optionally bind business context.
     *
     * @param array<int, string> $abilities
     */
    public function actingAsTenantUser(User $user, ?string $businessId = null, array $abilities = ['*']): self
    {
        ApiClientHelper::actingAsTenantUser($this, $user, $businessId, $abilities);

        return $this;
    }

    /**
     * Dispatch an internal JSON API request via Laravel test client with appropriate headers.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function apiJson(
        string $method,
        string $uri,
        array $data = [],
        array $headers = [],
        ?string $businessId = null,
        ?string $token = null
    ): TestResponse {
        return ApiClientHelper::dispatchApiRequest(
            $this,
            $method,
            $uri,
            $data,
            $headers,
            $businessId,
            $token
        );
    }
}
