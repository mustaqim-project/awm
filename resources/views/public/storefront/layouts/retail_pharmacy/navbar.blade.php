@if (view()->exists("public.storefront.themes.retail_pharmacy.navbar"))
    @include("public.storefront.themes.retail_pharmacy.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif