<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateShopeeReviewerAccount extends Command
{
    protected $signature = 'reviewer:shopee';

    protected $description = 'Create or update a Shopee Open Platform review test account for shopee.reviewer@cooca.id';

    public function handle(): int
    {
        $email    = 'shopee.reviewer@cooca.id';
        $password = 'ShopeeReview2026!';

        // Check if user already exists
        $user = User::where('email', $email)->first();

        $userData = [
            'name'              => 'Shopee App Reviewer',
            'email'             => $email,
            'password'          => Hash::make($password),
            'email_verified_at' => now(),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
            $userData['phone'] = '628123456789';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone_verified_at')) {
            $userData['phone_verified_at'] = now();
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'onboarding_completed')) {
            $userData['onboarding_completed'] = true;
            $userData['onboarding_completed_at'] = now();
            $userData['onboarding_current_step'] = 99;
            $userData['onboarding_version'] = 1;
        }

        if ($user) {
            User::withoutEvents(fn () => $user->update($userData));
            $this->info("User '{$email}' updated successfully.");
        } else {
            $user = User::withoutEvents(fn () => User::create($userData));
            $this->info("User '{$email}' created successfully.");
        }

        // Create or find demo business for the reviewer
        $business = Business::where('slug', 'shopee-reviewer-store')->first();

        if (! $business) {
            $bizData = [
                'name'        => 'Shopee Reviewer Demo Store',
                'slug'        => 'shopee-reviewer-store',
                'description' => 'Official Demo Store for Shopee Open Platform Review & Audit',
            ];

            $optionalCols = [
                'phone'              => '628123456789',
                'email'              => $email,
                'address'            => 'Jl. Sudirman Kav. 25, Jakarta Selatan, DKI Jakarta 12920',
                'currency'           => 'IDR',
                'rounding_strategy'  => Business::ROUNDING_ROUND,
                'currency_precision' => 0,
                'industry_category'  => 'retail',
                'is_active'          => true,
            ];

            foreach ($optionalCols as $col => $val) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('businesses', $col)) {
                    $bizData[$col] = $val;
                }
            }

            $business = Business::withoutEvents(fn () => Business::create($bizData));
            $this->info("Business 'Shopee Reviewer Demo Store' created.");
        } else {
            $updates = [];
            if (\Illuminate\Support\Facades\Schema::hasColumn('businesses', 'is_active')) {
                $updates['is_active'] = true;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('businesses', 'suspended_at')) {
                $updates['suspended_at'] = null;
            }
            if (! empty($updates)) {
                Business::withoutEvents(fn () => $business->update($updates));
            }
            $this->info("Business 'Shopee Reviewer Demo Store' verified.");
        }

        // Attach user as owner of the business
        $membership = BusinessMembership::where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->first();

        if (! $membership) {
            BusinessMembership::create([
                'user_id'     => $user->id,
                'business_id' => $business->id,
                'role'        => 'owner',
            ]);
        }

        // Set active business
        $user->update(['active_business_id' => $business->id]);

        // Seed sample products if empty
        rescue(function () use ($business) {
            $category = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'demo-catalog'],
                ['name' => 'Demo Catalog']
            );

            if (Product::where('business_id', $business->id)->count() === 0) {
                Product::create([
                    'business_id'   => $business->id,
                    'category_id'   => $category->id,
                    'name'          => 'Sample Retail T-Shirt Cotton Combed',
                    'sku'           => 'TSHIRT-BLK-01',
                    'barcode'       => '8991234567890',
                    'selling_price' => 75000,
                    'cost_price'    => 45000,
                    'current_stock' => 50,
                    'min_stock'     => 5,
                    'is_active'     => true,
                ]);

                Product::create([
                    'business_id'   => $business->id,
                    'category_id'   => $category->id,
                    'name'          => 'Sample Retail Canvas Tote Bag',
                    'sku'           => 'TOTE-NAT-01',
                    'barcode'       => '8991234567891',
                    'selling_price' => 50000,
                    'cost_price'    => 28000,
                    'current_stock' => 100,
                    'min_stock'     => 10,
                    'is_active'     => true,
                ]);
            }
        }, null, false);

        $this->newLine();
        $this->info('=== SHOPEE REVIEWER ACCOUNT READY ===');
        $this->info("Email:    {$email}");
        $this->info("Password: {$password}");
        $this->info("Business: {$business->name} (/{$business->slug})");
        $this->info("Login:    https://cooca.id/login");
        $this->info("Hub:      https://cooca.id/marketplace-hub");

        return self::SUCCESS;
    }
}
