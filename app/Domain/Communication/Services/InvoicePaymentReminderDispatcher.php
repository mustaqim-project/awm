<?php

declare(strict_types=1);

namespace App\Domain\Communication\Services;

use App\Domain\Communication\Mail\CustomerInvoiceSentMail;
use App\Domain\Communication\Models\BusinessEmailTemplate;
use App\Domain\Communication\Models\BusinessReminderSetting;
use App\Domain\Communication\Models\BusinessSmtpAccount;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CustomerTermReminder;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InvoicePaymentReminderDispatcher
{
    public function __construct(
        protected TenantMailService $mailService,
        protected EmailTemplateService $templateService,
        protected ?WhatsAppGatewayService $waGateway = null
    ) {
        $this->waGateway = $waGateway ?? app(WhatsAppGatewayService::class);
    }

    /**
     * Dispatch invoice email (with PDF attachment) using tenant's custom SMTP and template.
     *
     * @return array{success: bool, recipient: string, used_tenant_smtp: bool, error?: string}
     */
    public function dispatchInvoice(
        Invoice $invoice,
        string $templateKey = BusinessEmailTemplate::KEY_INVOICE_SENT,
        ?string $recipientEmail = null,
        ?string $customNotes = null
    ): array {
        $business = $invoice->business ?? Business::findOrFail($invoice->business_id);
        $customer = $invoice->customer;

        $targetEmail = $recipientEmail ?: ($customer?->email ?? null);
        if (empty($targetEmail)) {
            return [
                'success' => false,
                'recipient' => '',
                'used_tenant_smtp' => false,
                'error' => 'Alamat email pelanggan tidak ditemukan.',
            ];
        }

        $rendered = $this->templateService->renderInvoice($invoice, $business, $templateKey, $customNotes);

        $mailable = new CustomerInvoiceSentMail(
            $invoice,
            $business,
            $rendered['subject'],
            $rendered['body_html']
        );

        $result = $this->mailService->send(
            $business,
            $targetEmail,
            $mailable,
            BusinessSmtpAccount::CHANNEL_INVOICE
        );

        if ($result['success']) {
            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => auth()->id(),
                'auditable_type' => Invoice::class,
                'auditable_id' => $invoice->id,
                'action' => 'invoice_email_sent',
                'notes' => "Faktur Penjualan #{$invoice->invoice_number} ({$templateKey}) berhasil dikirim ke {$targetEmail}.",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);
        }

        return [
            'success' => $result['success'],
            'recipient' => $targetEmail,
            'used_tenant_smtp' => $result['used_tenant_smtp'],
            'error' => $result['error'] ?? null,
        ];
    }

    /**
     * Run automated due invoice reminders based on each tenant's business_reminder_settings.
     *
     * @return array{total_checked: int, sent: int, skipped: int, failed: int}
     */
    public function processScheduledReminders(?Business $specificBusiness = null, ?Carbon $date = null): array
    {
        $today = ($date ?? Carbon::today())->startOfDay();

        $businesses = $specificBusiness
            ? collect([$specificBusiness])
            : Business::query()->where('is_active', true)->get();

        $stats = [
            'total_checked' => 0,
            'sent' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        foreach ($businesses as $business) {
            $settings = BusinessReminderSetting::where('business_id', $business->id)->first();
            if ($settings && ! $settings->auto_remind_invoices) {
                continue; // Business turned off auto reminders
            }

            $invoices = Invoice::with(['customer', 'business'])
                ->where('business_id', $business->id)
                ->whereIn('status', [
                    Invoice::STATUS_SENT,
                    Invoice::STATUS_UNPAID,
                    Invoice::STATUS_PARTIALLY_PAID,
                    Invoice::STATUS_OVERDUE,
                ])
                ->where('balance_due', '>', 0)
                ->whereNotNull('due_date')
                ->get();

            foreach ($invoices as $invoice) {
                $stats['total_checked']++;

                $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
                $diffDays = (int) round($today->diffInDays($dueDate, false));

                $templateKey = null;
                $reminderType = null;

                if ($diffDays === 3 && ($settings ? $settings->remind_h_minus_3 : true)) {
                    $templateKey = BusinessEmailTemplate::KEY_INVOICE_REMINDER_UPCOMING;
                    $reminderType = CustomerTermReminder::TYPE_UPCOMING_H3;
                } elseif ($diffDays === 0 && ($settings ? $settings->remind_due_date : true)) {
                    $templateKey = BusinessEmailTemplate::KEY_INVOICE_REMINDER_DUE;
                    $reminderType = CustomerTermReminder::TYPE_DUE_DATE;
                } elseif ($diffDays < 0) {
                    $daysOverdue = abs($diffDays);
                    $shouldRemind = false;
                    if ($daysOverdue === 3 && ($settings ? $settings->remind_h_plus_3 : true)) {
                        $shouldRemind = true;
                    } elseif ($daysOverdue === 7 && ($settings ? $settings->remind_h_plus_7 : true)) {
                        $shouldRemind = true;
                    } elseif ($daysOverdue > 7 && $daysOverdue % 7 === 0 && ($settings ? $settings->remind_weekly_overdue : true)) {
                        $shouldRemind = true;
                    }

                    if ($shouldRemind) {
                        $templateKey = BusinessEmailTemplate::KEY_INVOICE_REMINDER_OVERDUE;
                        $reminderType = CustomerTermReminder::TYPE_OVERDUE;
                    }
                }

                if (! $templateKey || ! $reminderType) {
                    continue;
                }

                // Check daily deduplication guard
                $alreadySent = CustomerTermReminder::where('business_id', $business->id)
                    ->where('invoice_id', $invoice->id)
                    ->where('reminder_type', $reminderType)
                    ->whereDate('sent_date', $today->toDateString())
                    ->exists();

                if ($alreadySent) {
                    $stats['skipped']++;
                    continue;
                }

                // Dispatch Email
                $mailResult = $this->dispatchInvoice($invoice, $templateKey);

                if ($mailResult['success']) {
                    $stats['sent']++;
                    CustomerTermReminder::create([
                        'business_id'    => $business->id,
                        'customer_id'    => $invoice->customer_id,
                        'invoice_id'     => $invoice->id,
                        'reminder_type'  => $reminderType,
                        'channel'        => CustomerTermReminder::CHANNEL_EMAIL,
                        'sent_date'      => $today->toDateString(),
                        'sent_at'        => $today,
                        'email_status'   => CustomerTermReminder::STATUS_SENT,
                        'wa_status'      => CustomerTermReminder::STATUS_SKIPPED,
                        'recipient_email'=> $mailResult['recipient'],
                        'status'         => CustomerTermReminder::STATUS_SENT,
                    ]);
                } else {
                    $stats['failed']++;
                }
            }
        }

        return $stats;
    }
}
