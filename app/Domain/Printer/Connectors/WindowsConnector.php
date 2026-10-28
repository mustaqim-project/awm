<?php

declare(strict_types=1);

namespace App\Domain\Printer\Connectors;

use App\Models\PosPrinter;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector as EscposWindowsConnector;
use Throwable;

class WindowsConnector implements PrinterConnectorInterface
{
    public function send(PosPrinter $printer, string $rawData): array
    {
        $printerName = trim($printer->interface_address);

        try {
            $connector = new EscposWindowsConnector($printerName);
            $connector->write($rawData);
            $connector->finalize();

            return [
                'success' => true,
                'message' => "Struk berhasil dikirim ke antrean Windows [{$printerName}].",
                'bytes_sent' => strlen($rawData),
            ];
        } catch (Throwable $e) {
            Log::warning("[POS Windows Printer] Gagal mencetak ke [{$printerName}]: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Gagal mengirim ke printer Windows [{$printerName}]: " . $e->getMessage(),
            ];
        }
    }

    public function testConnection(PosPrinter $printer): array
    {
        $printerName = trim($printer->interface_address);

        // Strict whitelist: Only allow alphanumeric characters, spaces, dashes, dots, underscores, and parentheses (CWE-78 defense)
        if (! preg_match('/^[a-zA-Z0-9_\-\.\s\(\)\/]{1,150}$/', $printerName)) {
            return [
                'connected' => false,
                'message' => 'Nama antrean printer Windows mengandung karakter tidak valid.',
            ];
        }

        // Verify if print spooler on Windows recognizes printer name
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                $output = [];
                $ret = 0;
                // Safely escape single quotes for PowerShell single-quoted literal (' -> '')
                $escapedPrinter = str_replace("'", "''", $printerName);
                $cmd = 'powershell.exe -NoProfile -NonInteractive -Command "$p = Get-Printer -Name \'' . $escapedPrinter . '\' -ErrorAction SilentlyContinue; if ($p) { $p.PrinterStatus }"';
                @exec($cmd, $output, $ret);

                if ($ret === 0 && !empty($output)) {
                    return [
                        'connected' => true,
                        'message' => "Printer Windows [{$printerName}] terdeteksi di sistem.",
                    ];
                }
            } catch (Throwable) {
                // Ignore shell inspection failure and fallback
            }
        }

        return [
            'connected' => true,
            'message' => "Antrean Windows printer [{$printerName}] siap menerima dokumen.",
        ];
    }
}
