<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Business;
use App\Models\User;

final class BusinessGetLogoTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'business_get_logo';
    }

    public function getDescription(): string
    {
        return 'Membaca logo resmi usaha, aset visual branding, identitas bisnis, dan profil toko COOCA (URL logo publik, data base64 logo, nama usaha, kontak, dan alamat).';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:business:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'include_base64' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Jika true, sertakan representasi data URI base64 dari logo untuk disematkan langsung di dokumen atau pesan.',
                ],
                'include_storefront' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Sertakan informasi link toko publik / storefront dan aset visual landing page.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $hasLogo = ! empty($business->logo_path);
        $logoUrl = $business->logo_url;
        $storeLogoUrl = $business->store_logo_url;

        $base64 = null;
        if (! empty($arguments['include_base64'])) {
            $base64 = $business->pdf_logo;
        }

        $result = [
            'success'           => true,
            'business_id'       => (string) $business->id,
            'name'              => $business->name,
            'slug'              => $business->slug,
            'has_logo'          => $hasLogo,
            'logo_url'          => $logoUrl,
            'store_logo_url'    => $storeLogoUrl,
            'logo_path'         => $business->logo_path,
            'description'       => $business->description,
            'industry_category' => $business->industry_category,
            'business_scale'    => $business->business_scale,
            'currency'          => $business->currency ?? 'IDR',
            'currency_symbol'   => $business->currency_symbol,
            'contact'           => [
                'phone'   => $business->phone,
                'email'   => $business->email,
                'address' => $business->address,
                'city'    => $business->city,
            ],
            'operating_hours'   => $business->operating_hours,
        ];

        if ($base64 !== null) {
            $result['logo_base64'] = $base64;
        }

        if (! empty($arguments['include_storefront'])) {
            $result['storefront'] = [
                'url'         => url('/' . $business->slug),
                'catalog_url' => url('/' . $business->slug . '/katalog'),
                'is_active'   => (bool) $business->is_active,
            ];
        }

        $result['message'] = $hasLogo
            ? "Logo usaha '{$business->name}' berhasil dibaca."
            : "Usaha '{$business->name}' belum memiliki logo resmi terunggah.";

        return $result;
    }
}
