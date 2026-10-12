@if (view()->exists("public.storefront.themes.service_fitness.navbar"))
    @include("public.storefront.themes.service_fitness.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif