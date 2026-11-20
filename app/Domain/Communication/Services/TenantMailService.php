<?php

declare(strict_types=1);

namespace App\Domain\Communication\Services;

use App\Domain\Communication\Models\BusinessSmtpAccount;
use App\Models\Business;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TenantMailService
{
    /**
     * Resolve the active SMTP configuration for a business and specific channel.
     * Fallback hierarchy: specific channel (e.g. quotation) -> default channel -> null (use system fallback)
     */
    public function resolveAccount(Business $business, string $channel = 'default'): ?BusinessSmtpAccount
    {
        // 1. Check specific channel override (quotation, invoice, purchase_order)
        if ($channel !== BusinessSmtpAccount::CHANNEL_DEFAULT) {
            $specific = BusinessSmtpAccount::where('business_id', $business->id)
                ->where('channel_type', $channel)
                ->where('is_active', true)
                ->first();

            if ($specific) {
                return $specific;
            }
        }

        // 2. Fallback to default (1 master SMTP for all)
        return BusinessSmtpAccount::where('business_id', $business->id)
            ->where('channel_type', BusinessSmtpAccount::CHANNEL_DEFAULT)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Send mailable using the tenant's configured SMTP (or system fallback if not configured).
     *
     * @return array{success: bool, used_tenant_smtp: bool, error?: string}
     */
    public function send(Business $business, string|array $to, Mailable $mailable, string $channel = 'default'): array
    {
        $account = $this->resolveAccount($business, $channel);

        try {
            if ($account && ! empty($account->mail_host)) {
                // Dynamically configure tenant mailer
                $mailerName = 'tenant_' . $business->id . '_' . $channel;
                
                Config::set("mail.mailers.{$mailerName}", [
                    'transport' => 'smtp',
                    'host' => $account->mail_host,
                    'port' => (int) $account->mail_port,
                    'encryption' => $account->mail_encryption ?: null,
                    'username' => $account->mail_username,
                    'password' => $account->mail_password,
                    'timeout' => 15,
                ]);

                // Ensure from address reflects tenant setting
                if (! empty($account->from_address)) {
                    $mailable->from($account->from_address, $account->from_name ?: $business->name);
                }

                if (! empty($account->reply_to_address)) {
                    $mailable->replyTo($account->reply_to_address, $account->from_name ?: $business->name);
                }

                Mail::purge($mailerName);
                Mail::mailer($mailerName)->to($to)->send($mailable);

                return [
                    'success' => true,
                    'used_tenant_smtp' => true,
                ];
            }

            // Fallback to system default mailer
            Mail::to($to)->send($mailable);

            return [
                'success' => true,
                'used_tenant_smtp' => false,
            ];
        } catch (\Throwable $e) {
            Log::error("TenantMailService error sending email for business {$business->id} channel {$channel}: " . $e->getMessage());

            if ($account) {
                $account->last_status = 'error';
                $account->last_error_message = $e->getMessage();
                $account->saveQuietly();
            }

            return [
                'success' => false,
                'used_tenant_smtp' => (bool) $account,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test SMTP connection by sending a diagnostic test email.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(BusinessSmtpAccount $account, string $recipientEmail): array
    {
        $business = $account->business ?? Business::find($account->business_id);
        $mailerName = 'tenant_test_' . $account->id;

        try {
            Config::set("mail.mailers.{$mailerName}", [
                'transport' => 'smtp',
                'host' => $account->mail_host,
                'port' => (int) $account->mail_port,
                'encryption' => $account->mail_encryption ?: null,
                'username' => $account->mail_username,
                'password' => $account->mail_password,
                'timeout' => 10,
            ]);

            Mail::purge($mailerName);

            $fromAddress = $account->from_address ?: 'noreply@cooca.id';
            $fromName = $account->from_name ?: ($business ? $business->name : 'COOCA System');

            Mail::mailer($mailerName)->raw("Halo!\n\nIni adalah email uji coba (test connection) dari sistem COOCA.\nServer SMTP [{$account->mail_host}:{$account->mail_port}] berhasil terhubung dan siap digunakan untuk pengiriman dokumen bisnis.\n\nWaktu pengujian: " . now()->toRfc2822String(), function ($message) use ($recipientEmail, $fromAddress, $fromName, $account) {
                $message->to($recipientEmail)
                    ->from($fromAddress, $fromName)
                    ->subject("Uji Coba Koneksi SMTP [{$account->channel_type}] Berhasil - COOCA");
            });

            $account->last_status = 'connected';
            $account->last_error_message = null;
            $account->last_tested_at = now();
            $account->save();

            return [
                'success' => true,
                'message' => "Koneksi SMTP berhasil! Email uji coba telah dikirim ke {$recipientEmail}.",
            ];
        } catch (\Throwable $e) {
            $account->last_status = 'error';
            $account->last_error_message = $e->getMessage();
            $account->last_tested_at = now();
            $account->save();

            return [
                'success' => false,
                'message' => "Gagal terhubung ke server SMTP: " . $e->getMessage(),
            ];
        }
    }
}
