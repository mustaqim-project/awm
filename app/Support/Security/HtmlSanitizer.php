<?php

declare(strict_types=1);

namespace App\Support\Security;

use DOMDocument;
use DOMElement;
use DOMNode;
use Throwable;

/**
 * Sanitasi konten HTML dari Rich-Text Editor (TinyMCE) untuk mencegah Stored XSS (CWE-79).
 * Menghapus elemen berbahaya (<script>, <iframe>, <object>, <embed>, dll.)
 * serta menetralkan atribut event handler (on*) dan skema URI berbahaya (javascript:).
 */
final class HtmlSanitizer
{
    /**
     * Tag HTML yang mutlak dilarang karena dapat mengeksekusi kode atau memuat konten eksternal tak sah.
     */
    private const DISALLOWED_TAGS = [
        'script', 'iframe', 'object', 'embed', 'applet', 'meta', 'link', 'style', 'base', 'form', 'input', 'button',
    ];

    /**
     * Bersihkan string HTML dan kembalikan string aman.
     */
    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Hapus tag berisiko secara regex pra-parsing untuk menghindari eksekusi parser anomali
        $cleaned = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $cleaned = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', (string) $cleaned);
        $cleaned = preg_replace('#<style(.*?)>(.*?)</style>#is', '', (string) $cleaned);

        // Hapus inline event handlers (on* seperti onload, onerror, onclick, onmouseover)
        $cleaned = preg_replace('/\s*on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', (string) $cleaned);

        // Hapus skema berbahaya pada tautan atau sumber daya (javascript:, data:text/html, vbscript:)
        $cleaned = preg_replace('/(href|src)\s*=\s*["\']\s*(javascript|vbscript|data:text\/html):/i', '$1="about:blank"', (string) $cleaned);

        try {
            $dom = new DOMDocument();
            // Gunakan libxml options untuk mematikan warning HTML5 entity & load external DTD
            libxml_use_internal_errors(true);
            $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><div>' . $cleaned . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            // Temukan elemen <div> pembungkus setelah meta tag
            $wrapper = null;
            foreach ($dom->getElementsByTagName('div') as $div) {
                $wrapper = $div;
                break;
            }

            if (! $wrapper) {
                return (string) $cleaned;
            }

            self::sanitizeNode($wrapper);

            // Ambil isi di dalam container <div> pembungkus
            $result = '';
            foreach ($wrapper->childNodes as $child) {
                $result .= $dom->saveHTML($child);
            }

            return $result ?: (string) $cleaned;
        } catch (Throwable) {
            // Fallback aman jika DOMDocument gagal mem-parsing
            return (string) $cleaned;
        }
    }

    /**
     * Rekursif telusuri dan bersihkan setiap DOM Node.
     */
    private static function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            $tagName = strtolower($node->tagName);

            if (in_array($tagName, self::DISALLOWED_TAGS, true)) {
                $node->parentNode?->removeChild($node);
                return;
            }

            // Hapus atribut yang berawalan "on" atau protokol berbahaya
            $attributesToRemove = [];
            foreach ($node->attributes as $attr) {
                $attrName = strtolower($attr->name);
                $attrValue = strtolower(trim($attr->value));

                if (str_starts_with($attrName, 'on')) {
                    $attributesToRemove[] = $attr->name;
                } elseif (in_array($attrName, ['href', 'src', 'action'], true)) {
                    if (str_starts_with($attrValue, 'javascript:') || str_starts_with($attrValue, 'data:text/html') || str_starts_with($attrValue, 'vbscript:')) {
                        $attributesToRemove[] = $attr->name;
                    }
                }
            }

            foreach ($attributesToRemove as $attrName) {
                $node->removeAttribute($attrName);
            }
        }

        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                self::sanitizeNode($child);
            }
        }
    }
}
