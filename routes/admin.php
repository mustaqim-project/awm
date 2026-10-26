<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminAccountRecoveryController;
use App\Http\Controllers\Admin\AdminAiTokenController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBillingPackageController;
use App\Http\Controllers\Admin\AdminBusinessController;
use App\Http\Controllers\Admin\AdminCommunityEventController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminErrorLogController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminInboxController;
use App\Http\Controllers\Admin\AdminLeadController;
use App\Http\Controllers\Admin\AdminLegalPageController;
use App\Http\Controllers\Admin\AdminPasswordResetController;
use App\Http\Controllers\Admin\AdminPaymentAccountController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminSettlementController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminSmtpController;
use App\Http\Controllers\Admin\AdminSocialMediaController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminTemplateController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Guard: admin)
|--------------------------------------------------------------------------
|
| Dedicated superadmin authentication, platform settings, SaaS billing,
| tenant management, and platform-wide monitoring.
|
*/
Route::prefix('admin')->name('admin.')->group(function (): void {
    // Direct /admin entry point: Redirect authenticated superadmin to dashboard, guests to login
    Route::get('/', function () {
        return auth('admin')->check()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('admin.login');
    })->name('index');

    // 1. Guest Admin Authentication
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
        Route::get('/forgot-password', [AdminPasswordResetController::class, 'showForgot'])->name('password.request');
        Route::post('/forgot-password', [AdminPasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
        Route::get('/reset-password/{token}', [AdminPasswordResetController::class, 'showReset'])->name('password.reset');
        Route::post('/reset-password', [AdminPasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
    });

    // 2. Authenticated Admin Workspace
    Route::middleware(['auth:admin', \App\Http\Middleware\EnsureAdminIsActive::class])->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [AdminProfileController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Business / Tenant Management
        Route::get('/businesses', [AdminBusinessController::class, 'index'])->name('businesses.index');
        Route::get('/businesses/{business}', [AdminBusinessController::class, 'show'])->name('businesses.show');
        Route::post('/businesses/{business}/toggle-status', [AdminBusinessController::class, 'toggleStatus'])->name('businesses.toggle-status');
        Route::post('/businesses/{business}/impersonate', [AdminBusinessController::class, 'impersonate'])->name('businesses.impersonate');
        Route::match(['GET', 'POST'], '/businesses/leave-impersonation', [AdminBusinessController::class, 'leaveImpersonation'])->name('businesses.leave-impersonation');

        // User Management & CSV Export
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Account Recovery Verification & Approval
        Route::prefix('account-recoveries')->name('account-recoveries.')->group(function (): void {
            Route::get('/', [AdminAccountRecoveryController::class, 'index'])->name('index');
            Route::get('/{recovery}', [AdminAccountRecoveryController::class, 'show'])->name('show');
            Route::get('/{recovery}/document/{type}', [AdminAccountRecoveryController::class, 'document'])->name('document');
            Route::post('/{recovery}/approve', [AdminAccountRecoveryController::class, 'approve'])->name('approve');
            Route::post('/{recovery}/reject', [AdminAccountRecoveryController::class, 'reject'])->name('reject');
        });

        // AI Token Monitoring & Platform Engine Command Center
        Route::get('/ai-tokens', [AdminAiTokenController::class, 'index'])->name('ai-tokens.index');
        Route::post('/ai-tokens/{business}/grant', [AdminAiTokenController::class, 'grant'])->name('ai-tokens.grant');
        Route::post('/ai-tokens/engine/update', [AdminAiTokenController::class, 'updateEngine'])->name('ai-tokens.engine.update');
        Route::post('/ai-tokens/engine/test', [AdminAiTokenController::class, 'testEngine'])->name('ai-tokens.engine.test');
        Route::post('/ai-tokens/engine/detect-models', [AdminAiTokenController::class, 'detectModels'])->name('ai-tokens.engine.detect-models');
        Route::post('/ai-tokens/engine/keys', [AdminAiTokenController::class, 'addKey'])->name('ai-tokens.engine.keys.add');
        Route::post('/ai-tokens/engine/keys/{keyId}/toggle', [AdminAiTokenController::class, 'toggleKey'])->name('ai-tokens.engine.keys.toggle');
        Route::delete('/ai-tokens/engine/keys/{keyId}', [AdminAiTokenController::class, 'deleteKey'])->name('ai-tokens.engine.keys.delete');
        Route::post('/ai-tokens/engine/models', [AdminAiTokenController::class, 'addModel'])->name('ai-tokens.engine.models.add');
        Route::delete('/ai-tokens/engine/models/{modelId}', [AdminAiTokenController::class, 'deleteModel'])->where('modelId', '.*')->name('ai-tokens.engine.models.delete');
        Route::post('/ai-tokens/engine/models/reset-default', [AdminAiTokenController::class, 'resetDefaultModels'])->name('ai-tokens.engine.models.reset-default');

        // Google API, System Settings, Payment Gateway, WhatsApp & SMTP
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/billing', [AdminSettingController::class, 'updateBilling'])->name('settings.billing');
        Route::post('/settings/test-social-media', [AdminSettingController::class, 'testSocialMediaConfig'])->name('settings.test-social');
        Route::post('/settings/test-tripay', [AdminSettingController::class, 'testTripayConfig'])->name('settings.test-tripay');
        Route::post('/settings/test-whatsapp', [AdminSettingController::class, 'testWhatsAppConfig'])->name('settings.test-whatsapp');
        Route::post('/settings/test-instagram', [AdminSettingController::class, 'testInstagramConfig'])->name('settings.test-instagram');
        Route::post('/settings/test-biteship', [AdminSettingController::class, 'testBiteshipConfig'])->name('settings.test-biteship');
        Route::post('/settings/test-marketplace', [\App\Http\Controllers\Admin\AdminMarketplaceSettingController::class, 'testConfig'])->name('settings.test-marketplace');
        Route::get('/smtp', [AdminSmtpController::class, 'index'])->name('smtp.index');
        Route::post('/smtp', [AdminSmtpController::class, 'update'])->name('smtp.update');
        Route::post('/smtp/test', [AdminSmtpController::class, 'test'])->name('smtp.test');

        // Billing Package Catalogs
        Route::get('/billing-packages/{type?}', [AdminBillingPackageController::class, 'index'])->name('billing-packages.index');
        Route::post('/billing-packages', [AdminBillingPackageController::class, 'store'])->name('billing-packages.store');
        Route::put('/billing-packages/{billingPackage}', [AdminBillingPackageController::class, 'update'])->name('billing-packages.update');
        Route::post('/billing-packages/{billingPackage}/toggle', [AdminBillingPackageController::class, 'toggle'])->name('billing-packages.toggle');

        // SaaS Subscription Promos & Vouchers
        Route::prefix('promos')->name('promos.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Admin\AdminPromoController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\AdminPromoController::class, 'store'])->name('store');
            Route::put('/{promo}', [\App\Http\Controllers\Admin\AdminPromoController::class, 'update'])->name('update');
            Route::post('/{promo}/toggle', [\App\Http\Controllers\Admin\AdminPromoController::class, 'toggle'])->name('toggle');
            Route::delete('/{promo}', [\App\Http\Controllers\Admin\AdminPromoController::class, 'destroy'])->name('destroy');
            Route::post('/seed-defaults', [\App\Http\Controllers\Admin\AdminPromoController::class, 'seedDefaults'])->name('seed-defaults');
        });

        // SaaS Subscription Management & Payment Approval
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{payment}', [AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{payment}/approve', [AdminSubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::post('/subscriptions/{payment}/reject', [AdminSubscriptionController::class, 'reject'])->name('subscriptions.reject');

        // Merchant Gateway Settlement & Payout Hub (Bukti Transfer Upload & Verifikasi)
        Route::prefix('settlements')->name('settlements.')->group(function (): void {
            Route::get('/', [AdminSettlementController::class, 'index'])->name('index');
            Route::get('/{settlement}', [AdminSettlementController::class, 'show'])->name('show');
            Route::post('/{settlement}/approve', [AdminSettlementController::class, 'approve'])->name('approve');
            Route::post('/{settlement}/reject', [AdminSettlementController::class, 'reject'])->name('reject');
        });

        // Platform Finance & Corporate Accounting Hub (Buku Kas, Jurnal Umum, Buku Besar, Neraca Saldo, Neraca, COA)
        Route::prefix('finance')->name('finance.')->group(function (): void {
            Route::get('/', [AdminFinanceController::class, 'index'])->name('index');
            Route::post('/', [AdminFinanceController::class, 'store'])->name('store');
            Route::put('/{transaction}', [AdminFinanceController::class, 'update'])->name('update');
            Route::delete('/{transaction}', [AdminFinanceController::class, 'destroy'])->name('destroy');
            Route::post('/sync', [AdminFinanceController::class, 'sync'])->name('sync');
            Route::get('/export', [AdminFinanceController::class, 'export'])->name('export');

            // Double-Entry Journals & Chart of Accounts
            Route::post('/journals', [AdminFinanceController::class, 'storeJournal'])->name('journals.store');
            Route::post('/accounts', [AdminFinanceController::class, 'storeAccount'])->name('accounts.store');
            Route::put('/accounts/{account}', [AdminFinanceController::class, 'updateAccount'])->name('accounts.update');
            Route::post('/accounts/{account}/toggle', [AdminFinanceController::class, 'toggleAccount'])->name('accounts.toggle');

            // Accounts Receivable (AR) & Accounts Payable (AP)
            Route::post('/receivables', [AdminFinanceController::class, 'storeReceivable'])->name('receivables.store');
            Route::post('/receivables/{receivable}/payments', [AdminFinanceController::class, 'recordReceivablePayment'])->name('receivables.payments.store');
            Route::post('/payables', [AdminFinanceController::class, 'storePayable'])->name('payables.store');
            Route::post('/payables/{payable}/payments', [AdminFinanceController::class, 'recordPayablePayment'])->name('payables.payments.store');
        });

        // Penjualan B2B & Faktur Platform (Quotations, Sales Orders, Invoices, Sales Returns & Official PDF)
        Route::prefix('b2b')->name('b2b.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Admin\AdminB2bController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Admin\AdminB2bController::class, 'export'])->name('export');
            Route::get('/business-resources', [\App\Http\Controllers\Admin\AdminB2bController::class, 'businessResources'])->name('business-resources');

            // Quotations CRUD & Actions
            Route::post('/quotations', [\App\Http\Controllers\Admin\AdminB2bController::class, 'storeQuotation'])->name('quotations.store');
            Route::put('/quotations/{quotation}', [\App\Http\Controllers\Admin\AdminB2bController::class, 'updateQuotation'])->name('quotations.update');
            Route::delete('/quotations/{quotation}', [\App\Http\Controllers\Admin\AdminB2bController::class, 'destroyQuotation'])->name('quotations.destroy');
            Route::post('/quotations/{quotation}/convert', [\App\Http\Controllers\Admin\AdminB2bController::class, 'convertQuotation'])->name('quotations.convert');
            Route::get('/quotations/{quotation}/pdf', [\App\Http\Controllers\Admin\AdminB2bController::class, 'pdfQuotation'])->name('quotations.pdf');

            // Sales Orders CRUD & Actions
            Route::post('/orders', [\App\Http\Controllers\Admin\AdminB2bController::class, 'storeSalesOrder'])->name('orders.store');
            Route::put('/orders/{salesOrder}', [\App\Http\Controllers\Admin\AdminB2bController::class, 'updateSalesOrder'])->name('orders.update');
            Route::delete('/orders/{salesOrder}', [\App\Http\Controllers\Admin\AdminB2bController::class, 'destroySalesOrder'])->name('orders.destroy');
            Route::post('/orders/{salesOrder}/generate-invoice', [\App\Http\Controllers\Admin\AdminB2bController::class, 'generateInvoiceFromOrder'])->name('orders.generate-invoice');
            Route::get('/orders/{salesOrder}/pdf', [\App\Http\Controllers\Admin\AdminB2bController::class, 'pdfSalesOrder'])->name('orders.pdf');

            // Invoices CRUD & Actions
            Route::post('/invoices', [\App\Http\Controllers\Admin\AdminB2bController::class, 'storeInvoice'])->name('invoices.store');
            Route::put('/invoices/{invoice}', [\App\Http\Controllers\Admin\AdminB2bController::class, 'updateInvoice'])->name('invoices.update');
            Route::post('/invoices/{invoice}/void', [\App\Http\Controllers\Admin\AdminB2bController::class, 'voidInvoice'])->name('invoices.void');
            Route::delete('/invoices/{invoice}', [\App\Http\Controllers\Admin\AdminB2bController::class, 'destroyInvoice'])->name('invoices.destroy');
            Route::post('/invoices/{invoice}/payments', [\App\Http\Controllers\Admin\AdminB2bController::class, 'recordInvoicePayment'])->name('invoices.payments.store');
            Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\AdminB2bController::class, 'pdfInvoice'])->name('invoices.pdf');

            // Sales Returns CRUD & Actions
            Route::post('/returns', [\App\Http\Controllers\Admin\AdminB2bController::class, 'storeSalesReturn'])->name('returns.store');
            Route::put('/returns/{salesReturn}', [\App\Http\Controllers\Admin\AdminB2bController::class, 'updateSalesReturn'])->name('returns.update');
            Route::post('/returns/{salesReturn}/reject', [\App\Http\Controllers\Admin\AdminB2bController::class, 'rejectSalesReturn'])->name('returns.reject');
            Route::delete('/returns/{salesReturn}', [\App\Http\Controllers\Admin\AdminB2bController::class, 'destroySalesReturn'])->name('returns.destroy');
            Route::post('/returns/{salesReturn}/approve', [\App\Http\Controllers\Admin\AdminB2bController::class, 'approveSalesReturn'])->name('returns.approve');
            Route::post('/returns/{salesReturn}/complete', [\App\Http\Controllers\Admin\AdminB2bController::class, 'completeSalesReturn'])->name('returns.complete');
            Route::get('/returns/{salesReturn}/pdf', [\App\Http\Controllers\Admin\AdminB2bController::class, 'pdfSalesReturn'])->name('returns.pdf');
        });

        // Feedback: Bug Reports & Feature Requests
        Route::get('/feedback/bugs', [AdminFeedbackController::class, 'bugs'])->name('feedback.bugs.index');
        Route::get('/feedback/bugs/{bugReport}', [AdminFeedbackController::class, 'showBug'])->name('feedback.bugs.show');
        Route::patch('/feedback/bugs/{bugReport}', [AdminFeedbackController::class, 'updateBug'])->name('feedback.bugs.update');
        Route::get('/feedback/features', [AdminFeedbackController::class, 'features'])->name('feedback.features.index');
        Route::get('/feedback/features/{featureRequest}', [AdminFeedbackController::class, 'showFeature'])->name('feedback.features.show');
        Route::patch('/feedback/features/{featureRequest}', [AdminFeedbackController::class, 'updateFeature'])->name('feedback.features.update');

        // CMS Rekening Pembayaran Platform
        Route::get('/payment-accounts', [AdminPaymentAccountController::class, 'index'])->name('payment-accounts.index');
        Route::get('/payment-accounts/create', [AdminPaymentAccountController::class, 'create'])->name('payment-accounts.create');
        Route::post('/payment-accounts', [AdminPaymentAccountController::class, 'store'])->name('payment-accounts.store');
        Route::get('/payment-accounts/{paymentAccount}/edit', [AdminPaymentAccountController::class, 'edit'])->name('payment-accounts.edit');
        Route::put('/payment-accounts/{paymentAccount}', [AdminPaymentAccountController::class, 'update'])->name('payment-accounts.update');
        Route::delete('/payment-accounts/{paymentAccount}', [AdminPaymentAccountController::class, 'destroy'])->name('payment-accounts.destroy');
        Route::post('/payment-accounts/{paymentAccount}/toggle-status', [AdminPaymentAccountController::class, 'toggleStatus'])->name('payment-accounts.toggle-status');

        // CMS Artikel, Kategori, & Cluster
        Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [AdminPostController::class, 'create'])->name('posts.create');
        Route::post('/posts', [AdminPostController::class, 'store'])->name('posts.store');
        Route::get('/posts/{post}/edit', [AdminPostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{post}', [AdminPostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [AdminPostController::class, 'destroy'])->name('posts.destroy');
        Route::post('/posts/{post}/toggle', [AdminPostController::class, 'toggleStatus'])->name('posts.toggle-status');

        // Post Categories CRUD
        Route::post('/posts/categories', [AdminPostController::class, 'storeCategory'])->name('posts.categories.store');
        Route::put('/posts/categories/{category}', [AdminPostController::class, 'updateCategory'])->name('posts.categories.update');
        Route::delete('/posts/categories/{category}', [AdminPostController::class, 'destroyCategory'])->name('posts.categories.destroy');

        // Post Clusters CRUD
        Route::post('/posts/clusters', [AdminPostController::class, 'storeCluster'])->name('posts.clusters.store');
        Route::put('/posts/clusters/{cluster}', [AdminPostController::class, 'updateCluster'])->name('posts.clusters.update');
        Route::delete('/posts/clusters/{cluster}', [AdminPostController::class, 'destroyCluster'])->name('posts.clusters.destroy');

        // CMS Leads
        Route::get('/leads', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/export', [AdminLeadController::class, 'export'])->name('leads.export');

        // Community Events Management & Check-in Hub
        Route::prefix('events')->name('events.')->group(function (): void {
            Route::get('/', [AdminCommunityEventController::class, 'index'])->name('index');
            Route::get('/create', [AdminCommunityEventController::class, 'create'])->name('create');
            Route::post('/', [AdminCommunityEventController::class, 'store'])->name('store');
            Route::get('/{event}', [AdminCommunityEventController::class, 'show'])->name('show');
            Route::get('/{event}/edit', [AdminCommunityEventController::class, 'edit'])->name('edit');
            Route::put('/{event}', [AdminCommunityEventController::class, 'update'])->name('update');
            Route::delete('/{event}', [AdminCommunityEventController::class, 'destroy'])->name('destroy');
            Route::post('/{event}/checkin', [AdminCommunityEventController::class, 'checkin'])->name('checkin');
            Route::get('/{event}/export', [AdminCommunityEventController::class, 'export'])->name('export');
        });

        // Community Word Filters & Moderation Rules (Judol, Scam, Kata Kasar, Pornografi)
        Route::prefix('word-filters')->name('word-filters.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Admin\AdminWordFilterController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\AdminWordFilterController::class, 'store'])->name('store');
            Route::post('/bulk', [\App\Http\Controllers\Admin\AdminWordFilterController::class, 'bulkStore'])->name('bulk-store');
            Route::put('/{wordFilter}', [\App\Http\Controllers\Admin\AdminWordFilterController::class, 'update'])->name('update');
            Route::post('/{wordFilter}/toggle', [\App\Http\Controllers\Admin\AdminWordFilterController::class, 'toggle'])->name('toggle');
            Route::delete('/{wordFilter}', [\App\Http\Controllers\Admin\AdminWordFilterController::class, 'destroy'])->name('destroy');
            Route::post('/seed-defaults', [\App\Http\Controllers\Admin\AdminWordFilterController::class, 'seedDefaults'])->name('seed-defaults');
        });

        // CMS Template Excel Unduhan
        Route::prefix('templates')->name('templates.')->group(function (): void {
            Route::get('/', [AdminTemplateController::class, 'index'])->name('index');
            Route::get('/create', [AdminTemplateController::class, 'create'])->name('create');
            Route::post('/', [AdminTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [AdminTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [AdminTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [AdminTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/toggle', [AdminTemplateController::class, 'toggle'])->name('toggle');
            Route::get('/{template}/download', [AdminTemplateController::class, 'download'])->name('download');
        });

        // CMS Kebijakan Legalitas & Privasi (Privacy Policy & Terms of Service)
        Route::prefix('legal-pages')->name('legal-pages.')->group(function (): void {
            Route::get('/', [AdminLegalPageController::class, 'index'])->name('index');
            Route::get('/{legalPage}/edit', [AdminLegalPageController::class, 'edit'])->name('edit');
            Route::put('/{legalPage}', [AdminLegalPageController::class, 'update'])->name('update');
            Route::post('/{legalPage}/toggle', [AdminLegalPageController::class, 'toggle'])->name('toggle');
        });

        // WhatsApp Admin Center (Meta Official Platform Gateway, Reminders & Blast)
        Route::prefix('whatsapp')->name('whatsapp.')->group(function (): void {
            Route::get('/', [AdminWhatsAppController::class, 'index'])->name('index');
            Route::get('/status', [AdminWhatsAppController::class, 'checkStatus'])->name('status');
            Route::post('/test', [AdminWhatsAppController::class, 'testSend'])->name('test');
            Route::post('/send-otp', [AdminWhatsAppController::class, 'sendOtp'])->name('send-otp');
            Route::post('/verify-meta', [AdminWhatsAppController::class, 'verifyMetaCredentials'])->name('verify-meta');
            Route::post('/reminders/{subscription}/send', [AdminWhatsAppController::class, 'sendSingleReminder'])->name('reminders.send');
            Route::post('/reminders/send-all', [AdminWhatsAppController::class, 'sendAllReminders'])->name('reminders.send-all');
            Route::post('/reminders/templates', [AdminWhatsAppController::class, 'updateTemplates'])->name('reminders.templates');
            Route::post('/blasts', [AdminWhatsAppController::class, 'storeBlast'])->name('blasts.store');
            Route::get('/blasts/{blast}', [AdminWhatsAppController::class, 'showBlast'])->name('blasts.show');
            Route::post('/config', [AdminWhatsAppController::class, 'updateGatewayConfig'])->name('config');

            // Message Template Management (Meta Cloud API)
            Route::post('/meta-templates/sync', [AdminWhatsAppController::class, 'syncMetaTemplates'])->name('meta-templates.sync');
            Route::post('/meta-templates/create', [AdminWhatsAppController::class, 'createMetaTemplate'])->name('meta-templates.create');
            Route::post('/meta-templates/deploy-standards', [AdminWhatsAppController::class, 'deployStandardTemplates'])->name('meta-templates.deploy-standards');
            Route::post('/meta-templates/seed-standards', [AdminWhatsAppController::class, 'seedStandardTemplates'])->name('meta-templates.seed-standards');
            Route::post('/meta-templates/purge-non-id-en', [AdminWhatsAppController::class, 'purgeNonIdEnTemplates'])->name('meta-templates.purge-non-id-en');
            Route::delete('/meta-templates/{template}', [AdminWhatsAppController::class, 'deleteMetaTemplate'])->name('meta-templates.delete');

            // Legacy stubs for graceful fallback
            Route::get('/qr', [AdminWhatsAppController::class, 'getQr'])->name('qr');
            Route::get('/sessions', [AdminWhatsAppController::class, 'getSessions'])->name('sessions.index');
        });

        // Social Media Platform Admin Center (Meta App, Webhook & Tenant Oversight)
        Route::prefix('social-media')->name('social-media.')->group(function (): void {
            Route::get('/', [AdminSocialMediaController::class, 'index'])->name('index');
            Route::get('/linkedin/connect', [AdminSocialMediaController::class, 'getLinkedInAuthUrl'])->name('linkedin.connect');
            Route::get('/linkedin/callback', [AdminSocialMediaController::class, 'handleLinkedInCallback'])->name('linkedin.callback');
            Route::get('/tiktok/connect', [AdminSocialMediaController::class, 'getTikTokAuthUrl'])->name('tiktok.connect');
            Route::get('/tiktok/callback', [AdminSocialMediaController::class, 'handleTikTokCallback'])->name('tiktok.callback');
            Route::post('/accounts/{account}/disconnect', [AdminSocialMediaController::class, 'disconnectAccount'])->name('accounts.disconnect');
            Route::post('/config', [AdminSocialMediaController::class, 'updateConfig'])->name('config');
            Route::post('/posts', [AdminSocialMediaController::class, 'storePost'])->name('posts.store');
            Route::put('/posts/{post}', [AdminSocialMediaController::class, 'updatePost'])->name('posts.update');
            Route::post('/posts/{post}/publish', [AdminSocialMediaController::class, 'publishNow'])->name('posts.publish');
            Route::post('/posts/{post}/retry', [AdminSocialMediaController::class, 'retryPost'])->name('posts.retry');
            Route::delete('/posts/{post}', [AdminSocialMediaController::class, 'destroyPost'])->name('posts.destroy');
            Route::post('/comments/{comment}/reply', [AdminSocialMediaController::class, 'replyComment'])->name('comments.reply');
        });

        // Platform Omnichannel Inbox (Standalone Admin Communication Hub)
        Route::prefix('inbox')->name('inbox.')->group(function (): void {
            Route::get('/', [AdminInboxController::class, 'index'])->name('index');
            Route::get('/poll', [AdminInboxController::class, 'poll'])->name('poll');
            Route::get('/customer-history', [AdminInboxController::class, 'customerHistory'])->name('customer-history');
            Route::post('/reply/{id}', [AdminInboxController::class, 'reply'])->name('reply');
            Route::post('/upload-media', [AdminInboxController::class, 'uploadMedia'])->name('upload-media');
            Route::post('/send-private-dm/{id}', [AdminInboxController::class, 'sendPrivateDm'])->name('send-private-dm');
            Route::post('/toggle-status/{id}', [AdminInboxController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/tags/{id}', [AdminInboxController::class, 'updateTags'])->name('tags');
            Route::post('/sync-meta', [AdminInboxController::class, 'syncMeta'])->name('sync-meta');
            Route::post('/settings', [AdminInboxController::class, 'saveSettings'])->name('settings');

            // AI Chatbot & Knowledge Base Guidelines (Do's and Don'ts)
            Route::get('/ai-settings', [AdminInboxController::class, 'getAiSettings'])->name('ai-settings');
            Route::post('/ai-settings', [AdminInboxController::class, 'saveAiSettings'])->name('ai-settings.save');
            Route::post('/ai-test', [AdminInboxController::class, 'testAiPrompt'])->name('ai-test');
            Route::post('/generate-ai-reply/{id}', [AdminInboxController::class, 'generateAiReply'])->name('generate-ai-reply');
        });

        // Error Logs & System Diagnostics
        Route::prefix('error-logs')->name('error-logs.')->group(function (): void {
            Route::get('/', [AdminErrorLogController::class, 'index'])->name('index');
            Route::get('/download', [AdminErrorLogController::class, 'download'])->name('download');
            Route::delete('/clear', [AdminErrorLogController::class, 'clear'])->name('clear');
        });

        // Platform Admin Roles & Permissions Management Hub
        Route::prefix('roles')->name('roles.')->group(function (): void {
            Route::get('/', [AdminRoleController::class, 'index'])->name('index');
            Route::post('/', [AdminRoleController::class, 'store'])->name('store');
            Route::put('/{role}', [AdminRoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [AdminRoleController::class, 'destroy'])->name('destroy');
            Route::put('/admins/{admin}/role', [AdminRoleController::class, 'updateAdminRole'])->name('admins.role');
        });
    });
});
