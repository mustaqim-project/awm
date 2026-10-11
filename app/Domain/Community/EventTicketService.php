<?php

declare(strict_types=1);

namespace App\Domain\Community;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

final class EventTicketService
{
    /**
     * Generate unique ticket code: EVT-YYYYMM-XXXXXX
     */
    public function generateTicketCode(): string
    {
        $prefix = 'EVT-' . date('ym') . '-';
        return $prefix . strtoupper(Str::random(6));
    }

    /**
     * Generate unique event check-in code for event poster/venue barcode
     */
    public function generateEventCheckinCode(): string
    {
        return 'COOCA-EVT-' . strtoupper(Str::random(10));
    }

    /**
     * Generate an SVG QR code string that scales cleanly and never overflows its parent container.
     */
    public function generateQrCodeSvg(string $data, int $size = 280): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($data, 'utf-8', ErrorCorrectionLevel::M());

        // Remove any standalone xml declaration
        $svg = preg_replace('/<\?xml[^>]*\?>/i', '', $svg);

        // Normalize svg tag to be fully responsive
        if (!preg_match('/viewBox=/i', $svg)) {
            $svg = preg_replace('/<svg/i', "<svg viewBox=\"0 0 {$size} {$size}\"", $svg, 1);
        }
        $svg = preg_replace('/<svg([^>]*)width="[^"]*"([^>]*)height="[^"]*"/i', '<svg$1width="100%" height="100%" class="w-full h-full max-w-full max-h-full block"$2', $svg);

        return trim($svg);
    }

    /**
     * Generate a crisp vector SVG linear Barcode (Code 128 / Code 39 style) for the ticket code.
     */
    public function generateBarcodeSvg(string $code, int $width = 280, int $height = 65): string
    {
        $code = strtoupper(trim($code));
        $bars = '';
        $x = 10;
        $barWidthUnit = max(1.5, ($width - 20) / (strlen($code) * 11 + 35));

        // Start guard
        $bars .= "<rect x=\"{$x}\" y=\"0\" width=\"".($barWidthUnit * 2)."\" height=\"{$height}\" fill=\"currentColor\" />";
        $x += $barWidthUnit * 3;
        $bars .= "<rect x=\"{$x}\" y=\"0\" width=\"".($barWidthUnit)."\" height=\"{$height}\" fill=\"currentColor\" />";
        $x += $barWidthUnit * 2;

        // Encode pseudo-bars deterministically based on character ascii
        for ($i = 0; $i < strlen($code); $i++) {
            $charVal = ord($code[$i]);
            $pattern = [
                ($charVal % 3) + 1,
                (($charVal / 3) % 2) + 1,
                (($charVal / 5) % 3) + 1,
                (($charVal / 7) % 2) + 1,
            ];

            foreach ($pattern as $idx => $unit) {
                if ($idx % 2 === 0) {
                    $bWidth = $unit * $barWidthUnit;
                    $bars .= "<rect x=\"{$x}\" y=\"0\" width=\"{$bWidth}\" height=\"{$height}\" fill=\"currentColor\" />";
                    $x += $bWidth;
                } else {
                    $x += $unit * $barWidthUnit;
                }
            }
            $x += $barWidthUnit;
        }

        // Stop guard
        $bars .= "<rect x=\"{$x}\" y=\"0\" width=\"".($barWidthUnit * 2)."\" height=\"{$height}\" fill=\"currentColor\" />";
        $x += $barWidthUnit * 3;
        $bars .= "<rect x=\"{$x}\" y=\"0\" width=\"".($barWidthUnit)."\" height=\"{$height}\" fill=\"currentColor\" />";

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" class="w-full h-auto text-slate-900 dark:text-white" preserveAspectRatio="none">
    {$bars}
</svg>
SVG;
    }
}
