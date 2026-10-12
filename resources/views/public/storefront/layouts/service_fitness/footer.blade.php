@if (view()->exists("public.storefront.themes.service_fitness.footer"))
    @include("public.storefront.themes.service_fitness.footer")
@else
    @include("public.storefront.layouts.footer")
@endif