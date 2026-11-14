import re
import os

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

report = []

for file_path in files_in_scope:
    if not os.path.exists(file_path):
        continue
    content = open(file_path, encoding='utf-8').read()
    lines = content.split('\n')
    
    issues = []
    
    for i, line in enumerate(lines, 1):
        # 1. bg-white without dark:bg
        if 'bg-white' in line and not ('dark:bg-' in line or 'bg-white/' in line or 'bg-white]' in line or 'text-white' in line or 'border-' in line or 'modal' in line):
            # Check if this line defines a container/card with bg-white but no dark:bg
            if re.search(r'\bclass="[^"]*\bbg-white\b(?!/[0-9]+)(?![^"]*\bdark:bg-)', line):
                issues.append((i, "bg-white without dark:bg variant", line.strip()[:100]))
        
        # 2. text-slate-900 / text-black / text-gray-900 without dark:text
        if re.search(r'\bclass="[^"]*\b(text-slate-900|text-gray-900|text-black)\b(?![^"]*\bdark:text-)', line):
            issues.append((i, "dark text class without dark:text variant", line.strip()[:100]))
            
        # 3. text-slate-800 / text-gray-800 without dark:text
        if re.search(r'\bclass="[^"]*\b(text-slate-800|text-gray-800)\b(?![^"]*\bdark:text-)', line):
            issues.append((i, "dark text-800 class without dark:text variant", line.strip()[:100]))

        # 4. text-slate-700 / text-gray-700 without dark:text
        if re.search(r'\bclass="[^"]*\b(text-slate-700|text-gray-700)\b(?![^"]*\bdark:text-)', line):
            issues.append((i, "dark text-700 class without dark:text variant", line.strip()[:100]))

        # 5. text-slate-600 / text-gray-600 without dark:text
        if re.search(r'\bclass="[^"]*\b(text-slate-600|text-gray-600)\b(?![^"]*\bdark:text-)', line):
            issues.append((i, "muted dark text-600 class without dark:text variant", line.strip()[:100]))

        # 6. border-slate-200 / border-gray-200 without dark:border
        if re.search(r'\bclass="[^"]*\b(border-slate-200|border-gray-200|border-slate-300|border-gray-300)\b(?![^"]*\bdark:border-)', line):
            issues.append((i, "light border class without dark:border variant", line.strip()[:100]))

        # 7. inline color styles
        if re.search(r'style="[^"]*color\s*:\s*(#[0-9a-fA-F]{3,6}|rgb|black)', line):
            issues.append((i, "inline hardcoded dark text color style", line.strip()[:100]))

    if issues:
        report.append({
            'file': file_path,
            'count': len(issues),
            'sample': issues[:5]
        })

print(f"Scanned {len(files_in_scope)} files. Found issues in {len(report)} files.")
for r in report:
    print(f"\n--- {r['file']} ({r['count']} issues) ---")
    for line_num, desc, snippet in r['sample']:
        print(f"  L{line_num}: {desc} -> {snippet}")
