@if (view()->exists("public.storefront.themes.retail_pharmacy.footer"))
    @include("public.storefront.themes.retail_pharmacy.footer")
@else
    @include("public.storefront.layouts.footer")
@endif