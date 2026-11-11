<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessStorefrontCustomTheme extends Model
{
    use HasFactory;

    protected $table = 'business_storefront_custom_themes';

    public const STATUS_DEFAULT = 'default';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';

    public const FILE_HERO = 'hero';
    public const FILE_NAVBAR = 'navbar';
    public const FILE_FOOTER = 'footer';
    public const FILE_FEATURES_BANNER = 'features_banner';
    public const FILE_PRODUCT_CARD = 'product_card';
    public const FILE_CUSTOM_CSS = 'custom_css';

    public const SUPPORTED_FILES = [
        self::FILE_HERO => [
            'key' => self::FILE_HERO,
            'name' => 'hero.blade.php',
            'label' => 'Hero Banner Section',
            'desc' => 'Tampilan atas etalase, headline, CTA tombol utama, dan visual pilar brand.',
            'language' => 'html',
        ],
        self::FILE_NAVBAR => [
            'key' => self::FILE_NAVBAR,
            'name' => 'navbar.blade.php',
            'label' => 'Header & Navigation Bar',
            'desc' => 'Logo, menu navigasi, subtitle toko, status buka, dan tombol kontak.',
            'language' => 'html',
        ],
        self::FILE_FEATURES_BANNER => [
            'key' => self::FILE_FEATURES_BANNER,
            'name' => 'features_banner.blade.php',
            'label' => 'Features / Bento Grid',
            'desc' => 'Keunggulan bisnis, nilai autentik pilar layanan, dan banner promosi.',
            'language' => 'html',
        ],
        self::FILE_PRODUCT_CARD => [
            'key' => self::FILE_PRODUCT_CARD,
            'name' => 'product_card.blade.php',
            'label' => 'Product / Item Card',
            'desc' => 'Kartu etalase produk, foto, label harga, badge diskon, dan tombol tambah keranjang.',
            'language' => 'html',
        ],
        self::FILE_FOOTER => [
            'key' => self::FILE_FOOTER,
            'name' => 'footer.blade.php',
            'label' => 'Footer Section',
            'desc' => 'Jadwal operasional, alamat fisik, jam cut-off order, dan link media sosial.',
            'language' => 'html',
        ],
        self::FILE_CUSTOM_CSS => [
            'key' => self::FILE_CUSTOM_CSS,
            'name' => 'custom.css',
            'label' => 'Custom CSS Stylesheet',
            'desc' => 'Gaya visual khusus (CSS), styling tambahan, animasi, dan penyesuaian warna.',
            'language' => 'css',
        ],
    ];

    protected $fillable = [
        'business_id',
        'theme_preset',
        'file_key',
        'file_name',
        'baseline_content',
        'draft_content',
        'published_content',
        'status',
        'has_custom_modifications',
        'admin_review_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'has_custom_modifications' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if this file has custom code that deviates from baseline.
     */
    public function isCustomized(): bool
    {
        return $this->has_custom_modifications && ! empty($this->draft_content);
    }

    /**
     * Check if currently under admin review.
     */
    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    /**
     * Check if published custom code is currently active in production.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && ! empty($this->published_content);
    }
}
