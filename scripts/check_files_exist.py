import os
import re

files_in_scope = [
    # Auth
    "resources/views/auth/account-recovery/create.blade.php",
    "resources/views/auth/account-recovery/status.blade.php",
    "resources/views/auth/partials/brand-logo.blade.php",
    "resources/views/auth/complete-profile.blade.php",
    "resources/views/auth/forgot-password.blade.php",
    "resources/views/auth/google-register.blade.php",
    "resources/views/auth/login.blade.php",
    "resources/views/auth/otp.blade.php",
    "resources/views/auth/register-otp.blade.php",
    "resources/views/auth/register.blade.php",
    "resources/views/auth/reset-password.blade.php",
    "resources/views/auth/select-business.blade.php",
    "resources/views/auth/verify-email.blade.php",

    # Core Layout and Landing
    "resources/views/landing.blade.php",
    "resources/views/layouts/partials/typography.blade.php",
    "resources/views/layouts/public_marketing.blade.php",

    # Blog
    "resources/views/public/blog/index.blade.php",
    "resources/views/public/blog/show.blade.php",

    # Business Operating System
    "resources/views/public/business-operating-system/how-it-works.blade.php",
    "resources/views/public/business-operating-system/overview.blade.php",
    "resources/views/public/business-operating-system/why-cooca.blade.php",

    # Calculators
    "resources/views/public/calculators/bep.blade.php",
    "resources/views/public/calculators/gaji_karyawan.blade.php",
    "resources/views/public/calculators/harga_jual.blade.php",
    "resources/views/public/calculators/hpp.blade.php",
    "resources/views/public/calculators/index.blade.php",
    "resources/views/public/calculators/laba_bersih.blade.php",
    "resources/views/public/calculators/omzet_harian.blade.php",
    "resources/views/public/calculators/pph_final.blade.php",
    "resources/views/public/calculators/simulasi_what_if.blade.php",

    # Contact
    "resources/views/public/contact/index.blade.php",

    # Content Automation
    "resources/views/public/content-automation/analytics.blade.php",
    "resources/views/public/content-automation/content-calendar.blade.php",
    "resources/views/public/content-automation/content-creation.blade.php",
    "resources/views/public/content-automation/publishing.blade.php",

    # Discovery and Home
    "resources/views/public/discovery/index.blade.php",
    "resources/views/public/home/index.blade.php",

    # Commercial Landing Pages
    "resources/views/public/landing_commercial/kasir_android.blade.php",
    "resources/views/public/landing_commercial/kasir_gratis.blade.php",
    "resources/views/public/landing_commercial/kasir_offline.blade.php",

    # Marketplace
    "resources/views/public/marketplace/businesses.blade.php",
    "resources/views/public/marketplace/categories.blade.php",
    "resources/views/public/marketplace/index.blade.php",
    "resources/views/public/marketplace/locations.blade.php",
    "resources/views/public/marketplace/products.blade.php",
    "resources/views/public/marketplace/search.blade.php",

    # Omnichannel
    "resources/views/public/omnichannel/customer.blade.php",
    "resources/views/public/omnichannel/marketplace.blade.php",
    "resources/views/public/omnichannel/orders.blade.php",
    "resources/views/public/omnichannel/social-media.blade.php",
    "resources/views/public/omnichannel/whatsapp.blade.php",

    # Omnichannel ERP
    "resources/views/public/omnichannel-erp/accounting.blade.php",
    "resources/views/public/omnichannel-erp/analytics.blade.php",
    "resources/views/public/omnichannel-erp/crm.blade.php",
    "resources/views/public/omnichannel-erp/erp.blade.php",
    "resources/views/public/omnichannel-erp/finance.blade.php",
    "resources/views/public/omnichannel-erp/hrm.blade.php",
    "resources/views/public/omnichannel-erp/inventory.blade.php",
    "resources/views/public/omnichannel-erp/pos.blade.php",

    # Shared Public Partials
    "resources/views/public/partials/cookie_consent_banner.blade.php",
    "resources/views/public/partials/live_chat_widget.blade.php",
    "resources/views/public/partials/seo.blade.php",
    "resources/views/public/partials/subpage_layout.blade.php",
    "resources/views/public/partials/tracking_pixels.blade.php",
    "resources/views/public/partials/verified_testimonials.blade.php",
    "resources/views/public/partials/video_testimonials.blade.php",

    # Resources
    "resources/views/public/resources/case-studies.blade.php",
    "resources/views/public/resources/faq.blade.php",
    "resources/views/public/resources/guides.blade.php",

    # Solutions
    "resources/views/public/solutions/fnb.blade.php",
    "resources/views/public/solutions/laundry.blade.php",
    "resources/views/public/solutions/manufacturing.blade.php",
    "resources/views/public/solutions/retail.blade.php",
    "resources/views/public/solutions/services.blade.php",
    "resources/views/public/solutions/show.blade.php",
    "resources/views/public/solutions/workshop.blade.php",

    # Additional Public Pages
    "resources/views/public/about.blade.php",
    "resources/views/public/business_landing.blade.php",
    "resources/views/public/demo.blade.php",
    "resources/views/public/pricing.blade.php",
    "resources/views/public/privacy.blade.php",
    "resources/views/public/security.blade.php",
    "resources/views/public/sitemap.blade.php",
    "resources/views/public/support.blade.php",
    "resources/views/public/terms.blade.php",

    # Templates
    "resources/views/public/templates/index.blade.php",
    "resources/views/public/templates/show.blade.php",
]

# Verify all files exist
print(f"Checking {len(files_in_scope)} files...")
missing = []
for f in files_in_scope:
    if not os.path.exists(f):
        missing.append(f)
if missing:
    print(f"MISSING FILES ({len(missing)}):")
    for m in missing:
        print("  -", m)
else:
    print("ALL FILES EXIST!")
