<?php

declare(strict_types=1);

namespace App\Domain\Communication\Services;

use App\Domain\Communication\Models\BusinessSmtpAccount;
use App\Domain\Communication\Models\InboundEmail;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InboundEmailReaderService
{
    /**
     * Check incoming emails for a specific account via pure PHP IMAP socket.
     *
     * @return array{success: bool, checked_count: int, matched_count: int, error?: string}
     */
    public function checkAccount(BusinessSmtpAccount $account): array
    {
        if (! $account->enable_inbound_reading || empty($account->imap_host)) {
            return ['success' => false, 'checked_count' => 0, 'matched_count' => 0, 'error' => 'Inbound reading not configured'];
        }

        $host = (string) $account->imap_host;
        $port = (int) ($account->imap_port ?: 993);
        $username = (string) ($account->imap_username ?: $account->mail_username);
        $password = (string) ($account->imap_password ?: $account->mail_password);

        $socket = null;
        $checked = 0;
        $matched = 0;

        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);

            $timeout = 15;
            $remote = 'ssl://' . $host . ':' . $port;
            $socket = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

            if (! $socket) {
                throw new \RuntimeException("Koneksi IMAP gagal ke {$remote}: {$errstr} ({$errno})");
            }

            stream_set_timeout($socket, 10);
            $greeting = fgets($socket);

            // 1. LOGIN
            fputs($socket, "A001 LOGIN \"{$username}\" \"{$password}\"\r\n");
            $response = $this->readUntilTagged($socket, 'A001');
            if (! str_contains($response, 'A001 OK')) {
                throw new \RuntimeException("Otorisasi IMAP gagal: " . trim($response));
            }

            // 2. SELECT INBOX
            fputs($socket, "A002 SELECT INBOX\r\n");
            $response = $this->readUntilTagged($socket, 'A002');
            if (! str_contains($response, 'A002 OK')) {
                throw new \RuntimeException("Gagal membuka INBOX: " . trim($response));
            }

            // 3. SEARCH UNSEEN
            fputs($socket, "A003 SEARCH UNSEEN\r\n");
            $response = $this->readUntilTagged($socket, 'A003');

            preg_match('/\* SEARCH (.*)/i', $response, $matches);
            $msgIds = [];
            if (! empty($matches[1])) {
                $msgIds = array_filter(explode(' ', trim($matches[1])));
            }

            // Process up to 20 unread messages per cycle
            $msgIds = array_slice($msgIds, 0, 20);

            foreach ($msgIds as $msgId) {
                $tag = 'F' . $msgId;
                fputs($socket, "{$tag} FETCH {$msgId} (BODY.PEEK[HEADER] BODY.PEEK[TEXT])\r\n");
                $rawMsg = $this->readUntilTagged($socket, $tag);

                $parsed = $this->parseRawEmail($rawMsg);
                if (! empty($parsed['subject']) || ! empty($parsed['from'])) {
                    $inbound = $this->ingestEmail($account->business_id, $account->id, $parsed);
                    $checked++;
                    if ($inbound->related_id) {
                        $matched++;
                    }
                }
            }

            // 4. LOGOUT
            fputs($socket, "A004 LOGOUT\r\n");
            fclose($socket);

            $account->last_inbound_check_at = now();
            $account->saveQuietly();

            return [
                'success' => true,
                'checked_count' => $checked,
                'matched_count' => $matched,
            ];
        } catch (\Throwable $e) {
            if ($socket && is_resource($socket)) {
                fclose($socket);
            }
            Log::warning("InboundEmailReader error for account {$account->id}: " . $e->getMessage());

            return [
                'success' => false,
                'checked_count' => $checked,
                'matched_count' => $matched,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Read from socket until tagged response line arrives.
     */
    protected function readUntilTagged($socket, string $tag): string
    {
        $buffer = '';
        while (! feof($socket)) {
            $line = fgets($socket, 4096);
            if ($line === false) {
                break;
            }
            $buffer .= $line;
            if (str_starts_with($line, $tag . ' ')) {
                break;
            }
        }
        return $buffer;
    }

    /**
     * Parse raw RFC email text into structured data.
     *
     * @return array{from: string, from_name: string|null, to: string|null, subject: string, date: string|null, message_id: string|null, body: string}
     */
    public function parseRawEmail(string $raw): array
    {
        $headers = [];
        $body = '';

        $parts = preg_split("/\r?\n\r?\n/", $raw, 2);
        $headerText = $parts[0] ?? '';
        $body = $parts[1] ?? '';

        $headerLines = preg_split("/\r?\n/", $headerText);
        $currentHeader = '';

        foreach ($headerLines as $line) {
            if (preg_match('/^\s+(.*)$/', $line, $m) && ! empty($currentHeader)) {
                $headers[$currentHeader] .= ' ' . trim($m[1]);
            } elseif (preg_match('/^([A-Za-z0-9-]+):\s*(.*)$/', $line, $m)) {
                $currentHeader = strtolower($m[1]);
                $headers[$currentHeader] = trim($m[2]);
            }
        }

        $fromRaw = $headers['from'] ?? '';
        $fromEmail = $fromRaw;
        $fromName = null;
        if (preg_match('/^(.*?)\s*<([^>]+)>$/', $fromRaw, $fm)) {
            $fromName = trim(trim($fm[1]), '"\'');
            $fromEmail = trim($fm[2]);
        }

        $subject = $headers['subject'] ?? '(Tanpa Subjek)';
        if (str_starts_with($subject, '=?')) {
            $subject = iconv_mime_decode($subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        }

        return [
            'from' => $fromEmail,
            'from_name' => $fromName,
            'to' => $headers['to'] ?? null,
            'subject' => $subject,
            'date' => $headers['date'] ?? null,
            'message_id' => $headers['message-id'] ?? null,
            'body' => trim($body),
        ];
    }

    /**
     * Ingest structured email, detect document references, and store InboundEmail.
     */
    public function ingestEmail(string $businessId, ?string $accountId, array $data): InboundEmail
    {
        $subject = (string) ($data['subject'] ?? '');
        $bodyText = (string) ($data['body'] ?? '');
        $fromEmail = (string) ($data['from'] ?? '');
        $combinedText = $subject . ' ' . $bodyText;

        $relatedType = null;
        $relatedId = null;
        $status = InboundEmail::STATUS_RECEIVED;

        // 1. Detect Quotation reference: QUO-YYYY-XXXX or similar
        if (preg_match('/QUO-[\w-]+/i', $combinedText, $quoMatch)) {
            $quo = Quotation::where('business_id', $businessId)
                ->where('quotation_number', 'LIKE', '%' . $quoMatch[0] . '%')
                ->first();

            if ($quo) {
                $relatedType = 'quotation';
                $relatedId = $quo->id;
                $status = InboundEmail::STATUS_MATCHED;

                // Log audit communication
                AuditLog::create([
                    'business_id' => $businessId,
                    'auditable_type' => Quotation::class,
                    'auditable_id' => $quo->id,
                    'action' => 'inbound_email_received',
                    'notes' => "Balasan email masuk dari {$fromEmail} perihal Penawaran #{$quo->quotation_number}: {$subject}",
                    'ip_address' => '127.0.0.1',
                ]);
            }
        }

        // 2. Detect Invoice reference: INV-YYYY-XXXX or similar
        if (! $relatedId && preg_match('/INV-[\w-]+/i', $combinedText, $invMatch)) {
            $inv = Invoice::where('business_id', $businessId)
                ->where('invoice_number', 'LIKE', '%' . $invMatch[0] . '%')
                ->first();

            if ($inv) {
                $relatedType = 'invoice';
                $relatedId = $inv->id;
                $status = InboundEmail::STATUS_MATCHED;

                AuditLog::create([
                    'business_id' => $businessId,
                    'auditable_type' => Invoice::class,
                    'auditable_id' => $inv->id,
                    'action' => 'inbound_email_received',
                    'notes' => "Balasan email masuk dari {$fromEmail} perihal Faktur #{$inv->invoice_number}: {$subject}",
                    'ip_address' => '127.0.0.1',
                ]);
            }
        }

        // 3. Detect Purchase Order reference: PO-YYYY-XXXX or similar
        if (! $relatedId && preg_match('/PO-[\w-]+/i', $combinedText, $poMatch)) {
            $po = PurchaseOrder::where('business_id', $businessId)
                ->where('po_number', 'LIKE', '%' . $poMatch[0] . '%')
                ->first();

            if ($po) {
                $relatedType = 'purchase_order';
                $relatedId = $po->id;
                $status = InboundEmail::STATUS_MATCHED;

                AuditLog::create([
                    'business_id' => $businessId,
                    'auditable_type' => PurchaseOrder::class,
                    'auditable_id' => $po->id,
                    'action' => 'inbound_email_received',
                    'notes' => "Balasan email masuk dari {$fromEmail} perihal PO #{$po->po_number}: {$subject}",
                    'ip_address' => '127.0.0.1',
                ]);
            }
        }

        return InboundEmail::create([
            'business_id' => $businessId,
            'business_smtp_account_id' => $accountId,
            'message_id' => $data['message_id'] ?? null,
            'from_email' => $fromEmail,
            'from_name' => $data['from_name'] ?? null,
            'to_email' => $data['to'] ?? null,
            'subject' => $subject,
            'body_text' => Str::limit($bodyText, 5000),
            'body_html' => null,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'has_attachments' => ! empty($data['has_attachments']),
            'attachments_info' => $data['attachments_info'] ?? null,
            'processing_status' => $status,
            'received_at' => ! empty($data['date']) ? Carbon::parse($data['date']) : now(),
        ]);
    }
}
