<!doctype html>
<html lang="{{ app()->getLocale() }}">

@include('partials.ui.my.guest-head')

<body
    style="background: {{ $appTheme->authBackground() }};background-blend-mode:multiply;"
    class="font-sans text-norma-gray-900 antialiased"
>
  <div class="min-h-screen flex flex-col items-center justify-center sm:px-6 lg:px-8">
    <div class="w-full flex flex-col justify-center items-center py-4 px-1">
      {{ $slot }}
    </div>
  </div>
  @livewireScriptConfig
</body>

</html>
