<?php

declare(strict_types=1);

namespace App\Domain\Communication\Controllers;

use App\Domain\Communication\Models\BusinessEmailTemplate;
use App\Domain\Communication\Models\BusinessReminderSetting;
use App\Domain\Communication\Models\BusinessSmtpAccount;
use App\Domain\Communication\Models\InboundEmail;
use App\Domain\Communication\Services\EmailTemplateService;
use App\Domain\Communication\Services\InboundEmailReaderService;
use App\Domain\Communication\Services\TenantMailService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessMailSettingsWebController extends Controller
{
    public function __construct(
        protected TenantMailService $mailService,
        protected EmailTemplateService $templateService,
        protected InboundEmailReaderService $inboundReader
    ) {}

    /**
     * Display SMTP & Inbound IMAP accounts dashboard.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $accounts = BusinessSmtpAccount::where('business_id', $business->id)->get()->keyBy('channel_type');
        $defaultAccount = $accounts->get(BusinessSmtpAccount::CHANNEL_DEFAULT);
        $quotationAccount = $accounts->get(BusinessSmtpAccount::CHANNEL_QUOTATION);
        $invoiceAccount = $accounts->get(BusinessSmtpAccount::CHANNEL_INVOICE);
        $poAccount = $accounts->get(BusinessSmtpAccount::CHANNEL_PURCHASE_ORDER);

        return view('app.communication.settings.index', compact(
            'business',
            'defaultAccount',
            'quotationAccount',
            'invoiceAccount',
            'poAccount'
        ));
    }

    /**
     * Store or update SMTP & IMAP settings for a channel.
     */
    public function updateSmtp(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'channel_type' => ['required', 'string', 'in:default,quotation,invoice,purchase_order'],
            'mail_host' => ['required', 'string', 'max:150'],
            'mail_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,null'],
            'mail_username' => ['required', 'string', 'max:150'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'from_name' => ['required', 'string', 'max:150'],
            'from_address' => ['required', 'email', 'max:150'],
            'reply_to_address' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'enable_inbound_reading' => ['nullable', 'boolean'],
            'imap_host' => ['nullable', 'string', 'max:150'],
            'imap_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'imap_encryption' => ['nullable', 'string', 'in:ssl,tls,null'],
            'imap_username' => ['nullable', 'string', 'max:150'],
            'imap_password' => ['nullable', 'string', 'max:255'],
        ]);

        $account = BusinessSmtpAccount::firstOrNew([
            'business_id' => $business->id,
            'channel_type' => $validated['channel_type'],
        ]);

        $account->mail_host = $validated['mail_host'];
        $account->mail_port = (int) $validated['mail_port'];
        $account->mail_encryption = $validated['mail_encryption'] === 'null' ? null : $validated['mail_encryption'];
        $account->mail_username = $validated['mail_username'];
        if (! empty($validated['mail_password'])) {
            $account->mail_password = $validated['mail_password'];
        }
        $account->from_name = $validated['from_name'];
        $account->from_address = $validated['from_address'];
        $account->reply_to_address = $validated['reply_to_address'] ?? null;
        $account->is_active = $request->boolean('is_active', true);

        // IMAP settings
        $account->enable_inbound_reading = $request->boolean('enable_inbound_reading', false);
        $account->imap_host = $validated['imap_host'] ?? null;
        $account->imap_port = (int) ($validated['imap_port'] ?? 993);
        $account->imap_encryption = $validated['imap_encryption'] ?? 'ssl';
        $account->imap_username = $validated['imap_username'] ?? null;
        if (! empty($validated['imap_password'])) {
            $account->imap_password = $validated['imap_password'];
        }

        $account->save();

        return back()->with('success', __('communication.smtp_saved_success', ['channel' => ucfirst($account->channel_type)]));
    }

    /**
     * Send test email to verify SMTP credentials.
     */
    public function testSmtp(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'channel_type' => ['required', 'string', 'in:default,quotation,invoice,purchase_order'],
            'recipient_email' => ['required', 'email'],
        ]);

        $account = BusinessSmtpAccount::where('business_id', $business->id)
            ->where('channel_type', $validated['channel_type'])
            ->first();

        if (! $account) {
            $msg = __('communication.smtp_not_configured');
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $msg], 400) : back()->with('error', $msg);
        }

        $result = $this->mailService->testConnection($account, $validated['recipient_email']);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Display email templates management page.
     */
    public function templates(): View
    {
        $business = Context::requireBusiness();
        $available = $this->templateService->getAvailableTemplates();

        $savedTemplates = BusinessEmailTemplate::where('business_id', $business->id)->get()->keyBy('template_key');

        return view('app.communication.templates.index', compact('business', 'available', 'savedTemplates'));
    }

    /**
     * Update an email template.
     */
    public function updateTemplate(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'template_key' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
        ]);

        $template = BusinessEmailTemplate::updateOrCreate(
            [
                'business_id' => $business->id,
                'template_key' => $validated['template_key'],
            ],
            [
                'subject' => $validated['subject'],
                'body_html' => $validated['body_html'],
                'is_active' => true,
            ]
        );

        return back()->with('success', __('communication.template_saved_success'));
    }

    /**
     * Reset a template back to system default.
     */
    public function resetTemplate(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $templateKey = (string) $request->input('template_key');

        BusinessEmailTemplate::where('business_id', $business->id)
            ->where('template_key', $templateKey)
            ->delete();

        return back()->with('success', __('communication.template_reset_success'));
    }

    /**
     * Display automated invoice reminder settings page.
     */
    public function reminders(): View
    {
        $business = Context::requireBusiness();
        $settings = BusinessReminderSetting::firstOrNew(['business_id' => $business->id]);

        return view('app.communication.reminders.index', compact('business', 'settings'));
    }

    /**
     * Update automated invoice reminder settings.
     */
    public function updateReminders(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'auto_remind_invoices' => ['nullable', 'boolean'],
            'remind_h_minus_3' => ['nullable', 'boolean'],
            'remind_due_date' => ['nullable', 'boolean'],
            'remind_h_plus_3' => ['nullable', 'boolean'],
            'remind_h_plus_7' => ['nullable', 'boolean'],
            'remind_weekly_overdue' => ['nullable', 'boolean'],
            'preferred_send_hour' => ['required', 'integer', 'min:0', 'max:23'],
        ]);

        $settings = BusinessReminderSetting::firstOrNew(['business_id' => $business->id]);
        $settings->auto_remind_invoices = $request->boolean('auto_remind_invoices', true);
        $settings->remind_h_minus_3 = $request->boolean('remind_h_minus_3', true);
        $settings->remind_due_date = $request->boolean('remind_due_date', true);
        $settings->remind_h_plus_3 = $request->boolean('remind_h_plus_3', true);
        $settings->remind_h_plus_7 = $request->boolean('remind_h_plus_7', true);
        $settings->remind_weekly_overdue = $request->boolean('remind_weekly_overdue', true);
        $settings->preferred_send_hour = (int) $validated['preferred_send_hour'];
        $settings->channels = ['email'];
        $settings->save();

        return back()->with('success', __('communication.reminders_saved_success'));
    }

    /**
     * Display inbound detected emails log.
     */
    public function inbound(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = InboundEmail::where('business_id', $business->id)->latest('received_at');

        if ($filter = $request->query('status')) {
            $query->where('processing_status', $filter);
        }

        $emails = $query->paginate(20)->withQueryString();

        return view('app.communication.inbound.index', compact('business', 'emails'));
    }

    /**
     * Trigger immediate inbound email poll.
     */
    public function checkInboundNow(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $accounts = BusinessSmtpAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('enable_inbound_reading', true)
            ->get();

        if ($accounts->isEmpty()) {
            $msg = __('communication.no_inbound_account');
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $msg], 400) : back()->with('error', $msg);
        }

        $totalChecked = 0;
        $totalMatched = 0;

        foreach ($accounts as $account) {
            $res = $this->inboundReader->checkAccount($account);
            $totalChecked += $res['checked_count'];
            $totalMatched += $res['matched_count'];
        }

        $msg = __('communication.inbound_check_complete', ['checked' => $totalChecked, 'matched' => $totalMatched]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'checked_count' => $totalChecked,
                'matched_count' => $totalMatched,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Provide real-time communication notification feed (inbound emails + sent emails).
     */
    public function notificationsFeed(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $unreadInboundCount = InboundEmail::where('business_id', $business->id)
            ->where('processing_status', InboundEmail::STATUS_RECEIVED)
            ->count();

        $inboundEmails = InboundEmail::where('business_id', $business->id)
            ->latest('received_at')
            ->limit(10)
            ->get()
            ->map(function (InboundEmail $email) {
                return [
                    'id' => $email->id,
                    'from_name' => $email->from_name ?: $email->from_email,
                    'from_email' => $email->from_email,
                    'subject' => $email->subject ?: '(Tanpa Subjek)',
                    'status' => $email->processing_status,
                    'document_number' => $email->detected_document_number,
                    'document_type' => $email->detected_document_type,
                    'document_id' => $email->detected_document_id,
                    'received_at' => $email->received_at?->diffForHumans() ?? '-',
                    'received_at_formatted' => $email->received_at?->translatedFormat('d M H:i') ?? '-',
                ];
            });

        $sentTodayCount = AuditLog::where('business_id', $business->id)
            ->whereIn('action', ['quotation_email_sent', 'purchase_order_email_sent', 'invoice_email_sent'])
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $sentLogs = AuditLog::where('business_id', $business->id)
            ->whereIn('action', ['quotation_email_sent', 'purchase_order_email_sent', 'invoice_email_sent'])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function (AuditLog $log) {
                $docType = match ($log->action) {
                    'quotation_email_sent' => 'Penawaran',
                    'purchase_order_email_sent' => 'Purchase Order',
                    'invoice_email_sent' => 'Faktur',
                    default => 'Email',
                };

                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'document_type' => $docType,
                    'notes' => $log->notes,
                    'created_at' => $log->created_at?->diffForHumans() ?? '-',
                    'created_at_formatted' => $log->created_at?->translatedFormat('d M H:i') ?? '-',
                ];
            });

        return response()->json([
            'success' => true,
            'unread_inbound_count' => $unreadInboundCount,
            'sent_today_count' => $sentTodayCount,
            'total_badge_count' => $unreadInboundCount + $sentTodayCount,
            'inbound' => $inboundEmails,
            'sent' => $sentLogs,
        ]);
    }
}
