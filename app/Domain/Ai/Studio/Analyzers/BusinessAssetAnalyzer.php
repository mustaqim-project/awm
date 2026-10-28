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
     * Deteksi tipe kemasan atau wujud produk fisik (termasuk F&B Kuliner & Layanan Jasa).
     */
    protected function detectPackagingType(string $productName, string $category, string $description): array|string
    {
        $corpus = strtolower("{$productName} {$category} {$description}");

        // Kuliner Makanan Berkuah / Mangkok (Bakso, Soto, Mie, Ramen, Rawon, Sop)
        if (str_contains($corpus, 'bakso') || str_contains($corpus, 'meatball') || str_contains($corpus, 'mie') || str_contains($corpus, 'noodle') || str_contains($corpus, 'soto') || str_contains($corpus, 'rawon') || str_contains($corpus, 'ramen') || str_contains($corpus, 'sop') || str_contains($corpus, 'soup') || str_contains($corpus, 'gulai')) {
            return 'authentic artisanal steaming ceramic bowl with appetizing glistening broth, succulent handcrafted meatballs, and fresh celery and shallot garnishes';
        }

        // Kuliner Makanan Piring / Sajian Utama (Nasi, Ayam, Bebek, Sate, Steak, Gorengan, Kuliner)
        if (str_contains($corpus, 'nasi') || str_contains($corpus, 'ayam') || str_contains($corpus, 'bebek') || str_contains($corpus, 'sate') || str_contains($corpus, 'steak') || str_contains($corpus, 'rendang') || str_contains($corpus, 'kuliner') || str_contains($corpus, 'makanan') || str_contains($corpus, 'fnb') || str_contains($corpus, 'snack') || str_contains($corpus, 'roti') || str_contains($corpus, 'kue') || str_contains($corpus, 'cake')) {
            return 'curated artisanal ceramic dining plate with mouthwatering gourmet presentation, authentic golden textures, and vibrant culinary garnishes';
        }

        // Jasa / Layanan Profesional (Cuci Sepatu, Laundry, Salon, Barbershop, Bengkel, Servis, Cleaning)
        if (str_contains($corpus, 'jasa') || str_contains($corpus, 'service') || str_contains($corpus, 'layanan') || str_contains($corpus, 'cuci') || str_contains($corpus, 'laundry') || str_contains($corpus, 'salon') || str_contains($corpus, 'barber') || str_contains($corpus, 'pangkas') || str_contains($corpus, 'spa') || str_contains($corpus, 'massage') || str_contains($corpus, 'bengkel') || str_contains($corpus, 'servis') || str_contains($corpus, 'cleaning') || str_contains($corpus, 'treatment')) {
            return 'professional premium service environment with authentic craftsmanship, specialized expert tools, and pristine customer experience setting';
        }

        if (str_contains($corpus, 'botol') || str_contains($corpus, 'bottle') || str_contains($corpus, 'serum') || str_contains($corpus, 'toner') || str_contains($corpus, 'shampoo')) {
            return 'sleek glass bottle with precision dropper/pump';
        }

        if (str_contains($corpus, 'jar') || str_contains($corpus, 'pot') || str_contains($corpus, 'krim') || str_contains($corpus, 'cream') || str_contains($corpus, 'masker')) {
            return 'minimalist cosmetic jar with metallic cap';
        }

        if (str_contains($corpus, 'pouch') || str_contains($corpus, 'stand pouch') || str_contains($corpus, 'kopi') || str_contains($corpus, 'coffee') || str_contains($corpus, 'keripik')) {
            return 'matte kraft or foil stand-up pouch with sealed top';
        }

        if (str_contains($corpus, 'box') || str_contains($corpus, 'kotak') || str_contains($corpus, 'dus') || str_contains($corpus, 'hampers')) {
            return 'rigid premium gift box with clean embossed corners';
        }

        if (str_contains($corpus, 'cup') || str_contains($corpus, 'gelas') || str_contains($corpus, 'minuman') || str_contains($corpus, 'boba') || str_contains($corpus, 'latte') || str_contains($corpus, 'tea') || str_contains($corpus, 'jus') || str_contains($corpus, 'juice')) {
            return 'crystal clear beverage cup with refreshing condensation';
        }

        if (str_contains($corpus, 'kaos') || str_contains($corpus, 't-shirt') || str_contains($corpus, 'baju') || str_contains($corpus, 'hoodie') || str_contains($corpus, 'dress') || str_contains($corpus, 'hijab') || str_contains($corpus, 'gamis')) {
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

        if (str_contains($corpus, 'bakso') || str_contains($corpus, 'meatball') || str_contains($corpus, 'mie') || str_contains($corpus, 'soto') || str_contains($corpus, 'rawon') || str_contains($corpus, 'ramen') || str_contains($corpus, 'sop')) {
            return ['delicate rising aromatic steam vapors', 'crisp golden fried shallots', 'fresh lime wedges', 'traditional sambal condiment dish', 'rustic dark wood table surface'];
        }

        if (str_contains($corpus, 'jasa') || str_contains($corpus, 'service') || str_contains($corpus, 'cuci') || str_contains($corpus, 'laundry') || str_contains($corpus, 'salon') || str_contains($corpus, 'barber') || str_contains($corpus, 'treatment') || str_contains($corpus, 'bengkel')) {
            return ['impeccably clean specialized professional tools', 'pristine modern workshop or studio counter', 'subtle ambient warm lighting', 'crisp white towels and organized workspace'];
        }

        if (str_contains($corpus, 'kopi') || str_contains($corpus, 'coffee')) {
            return ['scattered roasted coffee beans', 'delicate aromatic steam', 'warm wooden coaster'];
        }

        if (str_contains($corpus, 'skincare') || str_contains($corpus, 'beauty') || str_contains($corpus, 'serum')) {
            return ['subtle crystal clear water ripples', 'soft botanical leaf silhouette', 'smooth stone podium'];
        }

        if (str_contains($corpus, 'makanan') || str_contains($corpus, 'fnb') || str_contains($corpus, 'kuliner')) {
            return ['fresh natural garnish', 'warm ambient kitchen surface', 'tasteful ceramic plate'];
        }

        if (str_contains($corpus, 'fashion') || str_contains($corpus, 'apparel') || str_contains($corpus, 'hijab')) {
            return ['neutral textured linen background', 'geometric minimalist architectural shadows'];
        }

        return ['clean architectural pedestal', 'soft diffuse studio shadows', 'pristine minimalist backdrop'];
    }
}
