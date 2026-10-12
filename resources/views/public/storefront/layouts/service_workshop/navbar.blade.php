@if (view()->exists("public.storefront.themes.service_workshop.navbar"))
    @include("public.storefront.themes.service_workshop.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif