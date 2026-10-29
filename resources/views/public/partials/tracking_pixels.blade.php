@php
    $metaPixelId = config('services.meta.pixel_id', env('META_PIXEL_ID'));
    $tiktokPixelId = config('services.tiktok.pixel_id', env('TIKTOK_PIXEL_ID'));
    $ga4Id = \App\Models\SystemSetting::get('seo_google_analytics_id', env('GA4_MEASUREMENT_ID', 'G-HZSN9QHGN1'));
@endphp

<!-- Unified Multi-Channel Tracking Engine (GA4 + Meta Pixel + TikTok Pixel) -->
<script>
window.coocaTrackingConfig = {
    metaPixelId: @json($metaPixelId ?: null),
    tiktokPixelId: @json($tiktokPixelId ?: null),
    ga4Id: @json($ga4Id ?: null),
};

window.coocaTrack = function(eventName, params) {
    params = params || {};

    // 1. Google Analytics 4
    try {
        if (typeof window.gtag === 'function') {
            window.gtag('event', eventName, params);
        }
    } catch (e) {}

    // Check cookie consent for marketing pixels (UU PDP compliance)
    var consent = localStorage.getItem('cooca_consent_status');
    if (consent !== 'accepted') {
        return;
    }

    // 2. Meta (Facebook) Pixel
    try {
        if (typeof window.fbq === 'function') {
            var fbEvent = eventName;
            if (eventName === 'Lead') fbEvent = 'Lead';
            else if (eventName === 'ViewContent') fbEvent = 'ViewContent';
            else if (eventName === 'CompleteRegistration') fbEvent = 'CompleteRegistration';
            else if (eventName === 'StartTrial') fbEvent = 'StartTrial';
            window.fbq('track', fbEvent, params);
        }
    } catch (e) {}

    // 3. TikTok Pixel
    try {
        if (typeof window.ttq !== 'undefined' && typeof window.ttq.track === 'function') {
            var ttEvent = eventName;
            if (eventName === 'Lead') ttEvent = 'Contact';
            else if (eventName === 'ViewContent') ttEvent = 'ViewContent';
            else if (eventName === 'CompleteRegistration') ttEvent = 'CompleteRegistration';
            else if (eventName === 'StartTrial') ttEvent = 'Subscribe';
            window.ttq.track(ttEvent, params);
        }
    } catch (e) {}
};

window.initMarketingPixels = function() {
    var consent = localStorage.getItem('cooca_consent_status');
    if (consent !== 'accepted') {
        return;
    }

    // Initialize Meta Pixel if ID is present
    if (window.coocaTrackingConfig.metaPixelId && !window._fbq_initialized) {
        window._fbq_initialized = true;
        (function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)})(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        
        fbq('init', window.coocaTrackingConfig.metaPixelId);
        fbq('track', 'PageView');
    }

    // Initialize TikTok Pixel if ID is present
    if (window.coocaTrackingConfig.tiktokPixelId && !window._ttq_initialized) {
        window._ttq_initialized = true;
        (function() {
            var ta = document.createElement('script');
            ta.type = 'text/javascript';
            ta.async = true;
            ta.src = 'https://analytics.tiktok.com/i18n/pixel/sdk.js?sdkid=' + window.coocaTrackingConfig.tiktokPixelId;
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ta, s);
        })();
    }
};

// Initialize marketing pixels if consent is already stored
if (localStorage.getItem('cooca_consent_status') === 'accepted') {
    window.initMarketingPixels();
}

// Track WhatsApp Lead clicks automatically across the entire site
document.addEventListener('click', function(e) {
    var target = e.target.closest('a[href*="wa.me"], a[href*="whatsapp.com"]');
    if (target) {
        window.coocaTrack('Lead', {
            channel: 'WhatsApp',
            href: target.href,
            page: window.location.pathname
        });
    }
});
</script>

