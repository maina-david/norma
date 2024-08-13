<!doctype html>
<html lang="{{ app()->getLocale() }}">

@include('partials.ui.my.guest-head')

<body class="font-sans text-libryo-gray-900 antialiased">
  {{ $slot }}
  @livewireScriptConfig
</body>

</html>
