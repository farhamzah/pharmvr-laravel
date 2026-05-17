<?php

namespace App\Helpers;

/**
 * Minimal QR Code helper — generates a simple QR-like visual placeholder
 * using pure PHP/SVG without any external dependency.
 *
 * For production-grade QR codes, replace with simplesoftwareio/simple-qrcode
 * or endroid/qr-code once the security advisory for league/commonmark is resolved.
 *
 * This implementation renders a deterministic dot-matrix pattern that encodes
 * the URL as a visual reference. It is NOT a scannable QR code — it is a
 * styled placeholder that displays the verification URL alongside it.
 */
class QrCodeHelper
{
    /**
     * Generate a simple SVG placeholder that visually represents a QR code.
     * The actual URL is displayed as text below the placeholder.
     *
     * @param  string  $url   The verification URL to encode
     * @param  int     $size  SVG size in pixels (default 80)
     * @return string  SVG markup (safe for inline use in Blade/DomPDF)
     */
    public static function svgPlaceholder(string $url, int $size = 80): string
    {
        // Deterministic seed from URL for consistent pattern
        $seed = crc32($url);
        $cells = 9; // 9x9 grid
        $cellSize = (int) floor($size / $cells);
        $actualSize = $cellSize * $cells;

        $rects = '';

        for ($row = 0; $row < $cells; $row++) {
            for ($col = 0; $col < $cells; $col++) {
                // Corner finder patterns (always filled)
                $isFinderCorner = self::isFinderPattern($row, $col, $cells);

                // Deterministic fill for data cells
                $hash = abs(crc32($url . $row . $col . $seed));
                $filled = $isFinderCorner || ($hash % 3 !== 0);

                if ($filled) {
                    $x = $col * $cellSize;
                    $y = $row * $cellSize;
                    $rects .= sprintf(
                        '<rect x="%d" y="%d" width="%d" height="%d" fill="#0f2438"/>',
                        $x, $y, $cellSize - 1, $cellSize - 1
                    );
                }
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">'
            . '<rect width="%d" height="%d" fill="#ffffff" stroke="#d8e0e7" stroke-width="1"/>'
            . '%s'
            . '</svg>',
            $actualSize, $actualSize,
            $actualSize, $actualSize,
            $actualSize, $actualSize,
            $rects
        );
    }

    /**
     * Returns true if the cell is part of a QR finder pattern corner.
     */
    private static function isFinderPattern(int $row, int $col, int $cells): bool
    {
        // Top-left 3x3
        if ($row < 3 && $col < 3) return true;
        // Top-right 3x3
        if ($row < 3 && $col >= $cells - 3) return true;
        // Bottom-left 3x3
        if ($row >= $cells - 3 && $col < 3) return true;

        return false;
    }

    /**
     * Build the public verification URL for a certificate.
     */
    public static function verificationUrl(string $certificateId): string
    {
        $appUrl = rtrim(config('app.url', 'https://pharmvr.cloud'), '/');
        return $appUrl . '/api/v1/public/certificates/' . $certificateId . '/verify';
    }
}
