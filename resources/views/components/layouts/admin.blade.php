<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
  @include('partials.head')
</head>

<body class="bg-norma-gray-100 font-sans text-norma-gray-900 antialiased">
  <div>

    @include("navs.admin")

    @if (isset($header) || isset($actions))
      <header class="bg-white shadow-sm">
        <div
             class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-2xl font-semibold text-norma-gray-900 text-norma-red flex justify-between">
          <div>{{ $header ?? '' }}</div>
          <div>{{ $actions ?? '' }}</div>
        </div>
      </header>
    @endif

    <main>
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        {{ $slot }}
      </div>
    </main>
  </div>

  <x-ui.alert />
  <turbo-frame data-turbo-cache="false" id="global-alert" class="hidden"></turbo-frame>
</body>

</html>
