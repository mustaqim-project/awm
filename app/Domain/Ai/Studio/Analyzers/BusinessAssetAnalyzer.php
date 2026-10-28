<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Analyzers;

use App\Models\Business;
use App\Models\Product;

class BusinessAssetAnalyzer
{
    /**
     * Analisis data produk dan identitas bisnis untuk menghasilkan deskripsi fisik dan batas invariansi.
     *
     * @param array<string, mixed> $customBrief
     * @return array{
     *     brand_name: string,
     *     product_name: string,
     *     category_name: string,
     *     packaging_type: string,
     *     physical_descriptor: string,
     *     key_features: array<int, string>,
     *     invariants: array<int, string>,
     *     accent_elements: array<int, string>,
     *     preservation_rules: string
     * }
     */
    public function analyze(Business $business, ?Product $product = null, array $customBrief = []): array
    {
        $brandName = (string) ($business->name ?? 'Brand Lokal');
        $productName = $product !== null ? (string) $product->name : (string) ($customBrief['product_name'] ?? 'Produk Unggulan');

        $categoryName = 'Umum';
        if ($product !== null && $product->category !== null) {
            $categoryName = (string) $product->category->name;
        } elseif (! empty($customBrief['category'])) {
            $categoryName = (string) $customBrief['category'];
        } elseif (! empty($business->industry_category)) {
            $categoryName = (string) $business->industry_category;
        }

        $packagingType = $this->detectPackagingType($productName, $categoryName, (string) ($product->description ?? ''));
        $physicalDescriptor = $this->generatePhysicalDescriptor($productName, $brandName, $categoryName, $packagingType, $product);

        $invariants = [
            "Maintain the exact shape and proportions of the {$packagingType}",
            "Preserve the authentic {$brandName} brand identity and label aesthetic",
            'Do not alter the primary product color or real material texture',
            'Do not generate unreadable fake text or hallucinated regulatory certification stamps',
        ];

        $accents = $this->suggestAccentElements($categoryName, $productName);

        $preservationRules = "EXACT PRODUCT PRESERVATION: The featured {$packagingType} representing {$productName} must remain the authentic focal point without structural distortion, logo replacement, or invented text.";

        return [
            'brand_name' => $brandName,
            'product_name' => $productName,
            'category_name' => $categoryName,
            'packaging_type' => $packagingType,
            'physical_descriptor' => $physicalDescriptor,
            'key_features' => (array) ($customBrief['key_features'] ?? [$categoryName, $packagingType]),
            'invariants' => $invariants,
            'accent_elements' => $accents,
            'preservation_rules' => $preservationRules,
        ];
    }

    /**
     * Deteksi tipe kemasan atau wujud produk fisik.
     */
    protected function detectPackagingType(string $productName, string $category, string $description): array|string
    {
        $corpus = strtolower("{$productName} {$category} {$description}");

        if (str_contains($corpus, 'botol') || str_contains($corpus, 'bottle') || str_contains($corpus, 'serum') || str_contains($corpus, 'toner') || str_contains($corpus, 'shampoo')) {
            return 'sleek glass bottle with precision dropper/pump';
        }

        if (str_contains($corpus, 'jar') || str_contains($corpus, 'pot') || str_contains($corpus, 'krim') || str_contains($corpus, 'cream') || str_contains($corpus, 'masker')) {
            return 'minimalist cosmetic jar with metallic cap';
        }

        if (str_contains($corpus, 'pouch') || str_contains($corpus, 'stand pouch') || str_contains($corpus, 'kopi') || str_contains($corpus, 'coffee') || str_contains($corpus, 'snack') || str_contains($corpus, 'keripik')) {
            return 'matte kraft or foil stand-up pouch with sealed top';
        }

        if (str_contains($corpus, 'box') || str_contains($corpus, 'kotak') || str_contains($corpus, 'dus') || str_contains($corpus, 'hampers')) {
            return 'rigid premium gift box with clean embossed corners';
        }

        if (str_contains($corpus, 'cup') || str_contains($corpus, 'gelas') || str_contains($corpus, 'minuman') || str_contains($corpus, 'boba') || str_contains($corpus, 'latte')) {
            return 'crystal clear beverage cup with refreshing condensation';
        }

        if (str_contains($corpus, 'kaos') || str_contains($corpus, 't-shirt') || str_contains($corpus, 'baju') || str_contains($corpus, 'hoodie') || str_contains($corpus, 'dress')) {
            return 'folded premium cotton garment with soft natural fabric drape';
        }

        return 'commercial retail packshot container';
    }

    /**
     * Menghasilkan deskripsi visual fisik produk.
     */
    protected function generatePhysicalDescriptor(
        string $productName,
        string $brandName,
        string $category,
        string $packagingType,
        ?Product $product
    ): string {
        $desc = "{$productName} by {$brandName}, high-end {$packagingType}";

        if ($product !== null && ! empty($product->description)) {
            $shortSnippet = substr(strip_tags((string) $product->description), 0, 100);
            $desc .= ", featuring {$shortSnippet}";
        }

        return $desc;
    }

    /**
     * Sarankan elemen pendukung (aksen latar / pelengkap estetika).
     *
     * @return array<int, string>
     */
    protected function suggestAccentElements(string $category, string $productName): array
    {
        $corpus = strtolower("{$category} {$productName}");

        if (str_contains($corpus, 'kopi') || str_contains($corpus, 'coffee')) {
            return ['scattered roasted coffee beans', 'delicate aromatic steam', 'warm wooden coaster'];
        }

        if (str_contains($corpus, 'skincare') || str_contains($corpus, 'beauty') || str_contains($corpus, 'serum')) {
            return ['subtle crystal clear water ripples', 'soft botanical leaf silhouette', 'smooth stone podium'];
        }

        if (str_contains($corpus, 'makanan') || str_contains($corpus, 'fnb') || str_contains($corpus, 'kuliner')) {
            return ['fresh natural garnish', 'warm ambient kitchen surface', 'tasteful ceramic plate'];
        }

        if (str_contains($corpus, 'fashion') || str_contains($corpus, 'apparel')) {
            return ['neutral textured linen background', 'geometric minimalist architectural shadows'];
        }

        return ['clean architectural pedestal', 'soft diffuse studio shadows', 'pristine minimalist backdrop'];
    }
}
