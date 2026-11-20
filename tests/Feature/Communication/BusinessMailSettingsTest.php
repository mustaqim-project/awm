<?php

declare(strict_types=1);

namespace Tests\Feature\Communication;

use App\Domain\Communication\Mail\CustomerInvoiceSentMail;
use App\Domain\Communication\Mail\PurchaseOrderSentMail;
use App\Domain\Communication\Mail\QuotationSentMail;
use App\Domain\Communication\Models\BusinessEmailTemplate;
use App\Domain\Communication\Models\BusinessReminderSetting;
use App\Domain\Communication\Models\BusinessSmtpAccount;
use App\Domain\Communication\Models\InboundEmail;
use App\Domain\Communication\Services\EmailTemplateService;
use App\Domain\Communication\Services\InboundEmailReaderService;
use App\Domain\Communication\Services\InvoicePaymentReminderDispatcher;
use App\Domain\Communication\Services\PurchaseOrderDispatchService;
use App\Domain\Communication\Services\QuotationDispatchService;
use App\Domain\Communication\Services\TenantMailService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessMailSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // Setup Business A & Owner A
        $this->ownerA = User::create([
            'name' => 'Owner Bisnis A',
            'email' => 'ownerA_' . uniqid() . '@test.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $this->businessA = Business::create([
            'name' => 'Bisnis Korporat A',
            'slug' => 'bisnis-a-' . uniqid(),
            'email' => 'corp_a_' . uniqid() . '@test.com',
            'phone' => '081234567890',
            'address' => 'Jl. Bisnis No. 1 Jakarta',
            'is_active' => true,
        ]);

        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        // Setup Business B & Owner B
        $this->ownerB = User::create([
            'name' => 'Owner Bisnis B',
            'email' => 'ownerB_' . uniqid() . '@test.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'phone' => '081234567891',
        ]);

        $this->businessB = Business::create([
            'name' => 'Bisnis Korporat B',
            'slug' => 'bisnis-b-' . uniqid(),
            'email' => 'corp_b_' . uniqid() . '@test.com',
            'phone' => '081234567891',
            'address' => 'Jl. Bisnis No. 2 Surabaya',
            'is_active' => true,
        ]);

        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
    }

    public function test_owner_can_configure_and_update_default_smtp_settings(): void
    {
        $this->actingAs($this->ownerA);
        session(['active_business_id' => $this->businessA->id]);

        $response = $this->post(route('communication.settings.smtp.update'), [
            'channel_type' => 'default',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'user_smtp_123',
            'mail_password' => 'super_secret_smtp_pass',
            'from_name' => 'PT Bisnis A Utama',
            'from_address' => 'noreply@bisnis-a.com',
            'reply_to_address' => 'support@bisnis-a.com',
            'is_active' => true,
            'enable_inbound_reading' => true,
            'imap_host' => 'imap.mailtrap.io',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'imap_username' => 'imap_user_123',
            'imap_password' => 'super_secret_imap_pass',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $account = BusinessSmtpAccount::where('business_id', $this->businessA->id)
            ->where('channel_type', 'default')
            ->first();

        $this->assertNotNull($account);
        $this->assertSame('smtp.mailtrap.io', $account->mail_host);
        $this->assertSame(587, $account->mail_port);
        $this->assertSame('user_smtp_123', $account->mail_username);
        $this->assertSame('noreply@bisnis-a.com', $account->from_address);

        // Verify password encryption at rest: raw database column is encrypted
        $raw = DB::table('business_smtp_accounts')->where('id', $account->id)->first();
        $this->assertNotEquals('super_secret_smtp_pass', $raw->mail_password);
        $this->assertSame('super_secret_smtp_pass', Crypt::decryptString($raw->mail_password));

        // Verify Eloquent accessor transparently decrypts
        $this->assertSame('super_secret_smtp_pass', $account->mail_password);
        $this->assertSame('super_secret_imap_pass', $account->imap_password);
    }

    public function test_channel_fallback_hierarchy_master_vs_overrides(): void
    {
        $mailService = app(TenantMailService::class);

        // 1. Initially no account exists
        $resolved = $mailService->resolveAccount($this->businessA, 'quotation');
        $this->assertNull($resolved);

        // 2. Create Master Default Account
        $defaultAccount = BusinessSmtpAccount::create([
            'business_id' => $this->businessA->id,
            'channel_type' => 'default',
            'mail_host' => 'smtp.default.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'default_user',
            'mail_password' => 'def_pass',
            'from_name' => 'Default Sender',
            'from_address' => 'default@bisnis-a.com',
            'is_active' => true,
        ]);

        // Quotation, Invoice, and PO all fallback to default
        $resolvedQuotation = $mailService->resolveAccount($this->businessA, 'quotation');
        $this->assertNotNull($resolvedQuotation);
        $this->assertSame('default', $resolvedQuotation->channel_type);
        $this->assertSame('default@bisnis-a.com', $resolvedQuotation->from_address);

        // 3. Add Quotation Override
        $quotationAccount = BusinessSmtpAccount::create([
            'business_id' => $this->businessA->id,
            'channel_type' => 'quotation',
            'mail_host' => 'smtp.sales.com',
            'mail_port' => 465,
            'mail_encryption' => 'ssl',
            'mail_username' => 'sales_user',
            'mail_password' => 'sales_pass',
            'from_name' => 'Sales Department',
            'from_address' => 'sales@bisnis-a.com',
            'is_active' => true,
        ]);

        // Now quotation uses override, but invoice still uses default
        $resolvedQuotationOverride = $mailService->resolveAccount($this->businessA, 'quotation');
        $this->assertSame('quotation', $resolvedQuotationOverride->channel_type);
        $this->assertSame('sales@bisnis-a.com', $resolvedQuotationOverride->from_address);

        $resolvedInvoice = $mailService->resolveAccount($this->businessA, 'invoice');
        $this->assertSame('default', $resolvedInvoice->channel_type);
        $this->assertSame('default@bisnis-a.com', $resolvedInvoice->from_address);
    }

    public function test_custom_template_saved_rendered_and_reset(): void
    {
        $this->actingAs($this->ownerA);
        session(['active_business_id' => $this->businessA->id]);

        $customBody = '<p>Halo {{customer_name}}, ini penawaran {{quotation_number}} sejumlah {{total_amount}} dari {{business_name}}.</p>';

        $response = $this->post(route('communication.templates.update'), [
            'template_key' => 'quotation_sent',
            'subject' => 'Penawaran Spesial: {{quotation_number}} untuk {{customer_name}}',
            'body_html' => $customBody,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Create a Customer and Quotation to use renderQuotation()
        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'code'        => 'C-TPL-001',
            'name'        => 'PT Mitra Sejati',
            'email'       => 'mitra@test.com',
        ]);

        $quotation = Quotation::create([
            'business_id'      => $this->businessA->id,
            'customer_id'      => $customer->id,
            'quotation_number' => 'QUO-2026/001',
            'date'             => now()->toDateString(),
            'subtotal'         => 15000000,
            'total_amount'     => 15000000,
            'status'           => 'draft',
        ]);

        $templateService = app(EmailTemplateService::class);
        // renderQuotation returns ['subject' => ..., 'body_html' => ...]
        $rendered = $templateService->renderQuotation($quotation, $this->businessA, null);

        $this->assertStringContainsString('QUO-2026/001', $rendered['subject']);
        $this->assertStringContainsString('PT Mitra Sejati', $rendered['subject']);
        $this->assertStringContainsString('15.000.000', $rendered['body_html']);
        $this->assertStringNotContainsString('{{customer_name}}', $rendered['body_html']);

        // Now test reset to default
        $resetResponse = $this->post(route('communication.templates.reset'), [
            'template_key' => 'quotation_sent',
        ]);

        $resetResponse->assertRedirect();
        $resetResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('business_email_templates', [
            'business_id'  => $this->businessA->id,
            'template_key' => 'quotation_sent',
        ]);
    }

    public function test_quotation_email_dispatch_with_pdf_attachment(): void
    {
        Mail::fake();

        // Configure default SMTP for Business A
        BusinessSmtpAccount::create([
            'business_id' => $this->businessA->id,
            'channel_type' => 'default',
            'mail_host' => 'smtp.test.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'testuser',
            'mail_password' => 'testpass',
            'from_name' => 'Bisnis Korporat A',
            'from_address' => 'sales@bisnis-a.com',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'code' => 'CUST-001',
            'name' => 'Klien PT Maju Makmur',
            'email' => 'klien@majumakmur.com',
            'phone' => '081299998888',
        ]);

        $quotation = Quotation::create([
            'business_id' => $this->businessA->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUO-2026-TEST',
            'date' => now()->toDateString(),
            'expiry_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 10000000,
            'total_amount' => 11100000,
            'status' => 'draft',
        ]);

        $dispatchService = app(QuotationDispatchService::class);
        $result = $dispatchService->dispatch($quotation, 'klien@majumakmur.com', 'Mohon ditinjau penawaran berikut.');

        $this->assertTrue($result['success']);
        $this->assertSame('klien@majumakmur.com', $result['recipient']);

        Mail::assertSent(QuotationSentMail::class, function ($mail) use ($customer) {
            return $mail->hasTo('klien@majumakmur.com');
        });

        // Verify quotation status updated to sent
        $quotation->refresh();
        $this->assertSame('sent', $quotation->status);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'quotation_email_sent',
        ]);
    }

    public function test_purchase_order_email_dispatch_with_pdf_attachment(): void
    {
        Mail::fake();

        // Configure PO SMTP override
        BusinessSmtpAccount::create([
            'business_id' => $this->businessA->id,
            'channel_type' => 'purchase_order',
            'mail_host' => 'smtp.purchasing.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'po_user',
            'mail_password' => 'po_pass',
            'from_name' => 'Purchasing Bisnis A',
            'from_address' => 'purchasing@bisnis-a.com',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'business_id' => $this->businessA->id,
            'code' => 'SUP-001',
            'name' => 'Supplier Baja Prima',
            'email' => 'orders@bajaprima.com',
            'phone' => '081255554444',
        ]);

        $po = PurchaseOrder::create([
            'business_id' => $this->businessA->id,
            'po_type' => 'supplier',
            'po_number' => 'PO-2026-TEST',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'subtotal' => 20000000,
            'total_amount' => 22200000,
        ]);

        $dispatchService = app(PurchaseOrderDispatchService::class);
        $result = $dispatchService->dispatch($po, 'orders@bajaprima.com', 'Harap konfirmasi ketersediaan stok.');

        $this->assertTrue($result['success']);
        $this->assertSame('orders@bajaprima.com', $result['recipient']);

        Mail::assertSent(PurchaseOrderSentMail::class, function ($mail) {
            return $mail->hasTo('orders@bajaprima.com');
        });

        // Verify PO status confirmed
        $po->refresh();
        $this->assertSame('confirmed', $po->status);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'purchase_order_email_sent',
        ]);
    }

    public function test_automated_invoice_payment_reminder_dispatch(): void
    {
        Mail::fake();

        BusinessSmtpAccount::create([
            'business_id' => $this->businessA->id,
            'channel_type' => 'default',
            'mail_host' => 'smtp.test.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'testuser',
            'mail_password' => 'testpass',
            'from_name' => 'Finance Bisnis A',
            'from_address' => 'billing@bisnis-a.com',
            'is_active' => true,
        ]);

        // Enable auto reminder on due date
        BusinessReminderSetting::create([
            'business_id' => $this->businessA->id,
            'auto_remind_invoices' => true,
            'remind_h_minus_3' => false,
            'remind_due_date' => true,
            'remind_h_plus_3' => false,
            'remind_h_plus_7' => false,
            'remind_weekly_overdue' => false,
            'preferred_send_hour' => 9,
            'channels' => ['email'],
        ]);

        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'code' => 'CUST-002',
            'name' => 'Client Sukses Jaya',
            'email' => 'finance@suksesjaya.com',
            'phone' => '081277776666',
        ]);

        // Invoice due TODAY — balance_due > 0 triggers the dispatcher
        $invoice = Invoice::create([
            'business_id'    => $this->businessA->id,
            'customer_id'    => $customer->id,
            'invoice_number' => 'INV-2026-DUE-TODAY',
            'invoice_date'   => now()->subDays(14)->toDateString(),
            'due_date'       => now()->toDateString(),
            'status'         => Invoice::STATUS_SENT,
            'subtotal'       => 5000000,
            'total_amount'   => 5550000,
            'paid_amount'    => 0,
            'balance_due'    => 5550000,
        ]);

        $dispatcher = app(InvoicePaymentReminderDispatcher::class);
        $res = $dispatcher->processScheduledReminders($this->businessA);

        $this->assertGreaterThanOrEqual(1, $res['sent']);

        Mail::assertSent(CustomerInvoiceSentMail::class, function ($mail) {
            return $mail->hasTo('finance@suksesjaya.com');
        });

        // Verify a CustomerTermReminder record was created for this invoice
        $this->assertDatabaseHas('customer_term_reminders', [
            'business_id' => $this->businessA->id,
            'invoice_id'  => $invoice->id,
            'reminder_type' => 'due_date',
            'channel'       => 'email',
        ]);
    }

    public function test_inbound_email_detection_and_document_matching(): void
    {
        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'code'        => 'CUST-003',
            'name'        => 'Client Balasan Email',
            'email'       => 'client@replytest.com',
        ]);

        $quotation = Quotation::create([
            'business_id'      => $this->businessA->id,
            'customer_id'      => $customer->id,
            'quotation_number' => 'QUO-2026/9999',
            'date'             => now()->toDateString(),
            'subtotal'         => 1000000,
            'total_amount'     => 1110000,
            'status'           => 'sent',
        ]);

        $reader = app(InboundEmailReaderService::class);

        // Test matching quotation via ingestEmail() — the public entry point for detection
        $subject = 'Re: Surat Penawaran Harga QUO-2026/9999 Disetujui';
        $body    = 'Terima kasih, kami menyetujui penawaran QUO-2026/9999 tersebut. Mohon dibuatkan PO/Invoice.';

        $inbound = $reader->ingestEmail($this->businessA->id, null, [
            'message_id' => 'msg-' . uniqid() . '@mail.client.com',
            'from'       => 'client@replytest.com',
            'from_name'  => 'Client Balasan Email',
            'subject'    => $subject,
            'body'       => $body,
        ]);

        $this->assertNotNull($inbound);
        // The service sets processing_status = 'matched' when a document is found
        $this->assertSame('matched', $inbound->processing_status);
        // Document is linked via related_type / related_id
        $this->assertSame('quotation', $inbound->related_type);
        $this->assertSame($quotation->id, $inbound->related_id);

        $this->assertDatabaseHas('inbound_emails', [
            'id'                => $inbound->id,
            'business_id'       => $this->businessA->id,
            'related_type'      => 'quotation',
            'related_id'        => $quotation->id,
            'processing_status' => 'matched',
        ]);
    }

    public function test_multi_tenant_isolation_prevents_cross_tenant_data_leakage(): void
    {
        // Setup Inbound Email in Business A
        InboundEmail::create([
            'business_id'       => $this->businessA->id,
            'message_id'        => 'msg-biz-a@mail.com',
            'from_email'        => 'client@biz-a.com',
            'subject'           => 'Confidential Quotation Response for Business A',
            'body_text'         => 'Confidential notes for Business A only.',
            'processing_status' => InboundEmail::STATUS_RECEIVED,
            'received_at'       => now(),
        ]);

        // Setup Inbound Email in Business B
        InboundEmail::create([
            'business_id'       => $this->businessB->id,
            'message_id'        => 'msg-biz-b@mail.com',
            'from_email'        => 'vendor@biz-b.com',
            'subject'           => 'Order for Business B',
            'body_text'         => 'Order notes for Business B only.',
            'processing_status' => InboundEmail::STATUS_RECEIVED,
            'received_at'       => now(),
        ]);

        // Login as Owner B
        $this->actingAs($this->ownerB);
        session(['active_business_id' => $this->businessB->id]);

        $response = $this->get(route('communication.inbound.index'));
        $response->assertOk();
        $response->assertSee('Order for Business B');
        $response->assertDontSee('Confidential Quotation Response for Business A');

        // Verify Business B cannot see Business A's SMTP configuration
        $responseSettings = $this->get(route('communication.settings.index'));
        $responseSettings->assertOk();
        $this->assertNull($responseSettings->viewData('defaultAccount'));
    }

    public function test_communication_notifications_feed_returns_inbound_and_sent_emails(): void
    {
        // 1. Create an Inbound Email for Business A
        InboundEmail::create([
            'business_id' => $this->businessA->id,
            'message_id' => 'feed-test@mail.com',
            'from_email' => 'client.feedback@feed.com',
            'from_name' => 'Klien Feed Feedback',
            'subject' => 'Konfirmasi Penawaran Harga Masuk',
            'body_text' => 'Kami setuju.',
            'processing_status' => InboundEmail::STATUS_RECEIVED,
            'received_at' => now(),
        ]);

        // 2. Create an AuditLog for email sent
        AuditLog::create([
            'business_id'    => $this->businessA->id,
            'auditable_type' => Quotation::class,
            'auditable_id'   => (string) Str::uuid(),
            'action'         => 'quotation_email_sent',
            'notes'          => 'Surat Penawaran #QUO-2026/001 berhasil dikirim ke klien@test.com.',
            'created_at'     => now(),
        ]);

        // 3. Request notifications feed as Owner A
        $this->actingAs($this->ownerA);
        session(['active_business_id' => $this->businessA->id]);

        $response = $this->getJson(route('communication.notifications.feed'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'unread_inbound_count' => 1,
            'sent_today_count' => 1,
        ]);

        $response->assertJsonFragment([
            'from_name' => 'Klien Feed Feedback',
            'subject' => 'Konfirmasi Penawaran Harga Masuk',
        ]);

        $response->assertJsonFragment([
            'action' => 'quotation_email_sent',
            'document_type' => 'Penawaran',
        ]);
    }
}
