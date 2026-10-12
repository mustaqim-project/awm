@if (view()->exists("public.storefront.themes.universal_storefront.footer"))
    @include("public.storefront.themes.universal_storefront.footer")
@else
    @include("public.storefront.layouts.footer")
@endif