<!doctype html>
<html lang="{{ app()->getLocale() }}">

@include('partials.ui.my.guest-head')

<body class="font-sans text-norma-gray-900 antialiased">
  {{ $slot }}
  @livewireScriptConfig
</body>

</html>
