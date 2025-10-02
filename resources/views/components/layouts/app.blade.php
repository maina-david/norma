@php
use App\Services\Customer\ActiveNormasManager;
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
  @include('partials.head')
  @env('production')
    @include('partials.ui.my.googletagmanager-head')
  @endenv

  @if(!$noDrive)
    <script>
      // refresh page when component is in the previous page.
      window.addEventListener('popstate',function (event) {
        if (event.state && event.state.component) {
          window.location.reload();
        }
      });
    </script>
  @endif
</head>

<body class="bg-body font-sans text-norma-gray-900 antialiased" {{ $attributes ?? '' }}>
@env('production')
  @include('partials.ui.my.googletagmanager-body')
@endenv
@if($plainLayout)
  <div class="h-screen sm:ml-14 pt-16">
    {{ $slot }}
  </div>
@else
  <div>

    <div class="sm:ml-14 pt-16">

      @if (isset($header) || isset($actions))
        <header>
          <div class="py-4 px-4 sm:px-6 flex flex-col md:flex-row justify-between space-x-2 space-y-4">
            <div class="font-light text-lg md:text-2xl text-norma-gray-900 font-serif">{{ $header ?? '' }}</div>
            <div>{{ $actions ?? '' }}</div>
          </div>
        </header>
      @endif

      <main{{ $noDrive ? ' data-turbo="false"' : '' }}>
        <div class="pb-20 px-4">
          {{ $slot }}
        </div>

        <div class="mb-12">
          @include('partials.ui.copyright')
        </div>

      </main>

    </div>

  </div>
@endif
<x-system.system-notification.my.popup/>

@include("navs.{$normaApp}")

<turbo-frame data-turbo-cache="false" id="global-alert" class="hidden"></turbo-frame>
@env('production')
  @include('partials.ui.my.hotjar')
  @include('partials.ui.my.hubspot')
  @if (app(ActiveNormasManager::class)->getActiveOrganisation()->hasLiveChat())
    @include('partials.ui.my.zopim')
  @endif
@endenv
@include('partials.ui.my.ie-notice')
@livewireScriptConfig
</body>

</html>
