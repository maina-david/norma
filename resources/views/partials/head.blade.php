<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>{{ config('app.name') }}</title>
<link rel="icon" href="{{ $appTheme->favicon() }}">
<link rel="stylesheet" href="https://rsms.me/inter/inter.css">
@vite(['resources/js/app.js', 'resources/css/app.css'])
@yield('docHead')
<style>
  {{ $appTheme->css() }}
</style>
@livewireStyles
