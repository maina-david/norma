<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
  @include('partials.head')
  {{ $pageHead ?? '' }}
  <style>
    nav span[aria-current="page"] .pager-item {
      background-color: var(--primary);
      color: #fff;
    }
  </style>
</head>

<body class="bg-norma-gray-50 font-sans text-norma-gray-900 antialiased h-screen overflow-hidden flex flex-col">
  @include('navs.collaborate')

  @if (isset($header) || isset($actions))
    <header class="bg-white shadow-sm shrink-0 z-10">
      <div class="{{ $fluid ? '' : 'max-w-7xl' }} {{ $noPadding ? '' : ' mx-auto py-2 px-4 sm:px-6 lg:px-8' }} text-2xl font-semibold text-norma-gray-900 text-norma-red flex justify-between">
        <div class="flex-grow">{{ $header ?? '' }}</div>
        <div class="flex-shrink-0">{{ $actions ?? '' }}</div>
      </div>
    </header>
  @endif

  <main class="grow overflow-auto custom-scroll">
    <div class="h-full {{ $fluid ? '' : 'max-w-7xl' }}{{ $noPadding ? '' : ' mx-auto py-6 sm:px-6 lg:px-8' }}">
      {{ $slot }}
    </div>
  </main>

  <x-ui.alert />
  <livewire:collaborators.update-residency-modal />
  <turbo-frame data-turbo-cache="false" id="global-alert" class="hidden"></turbo-frame>
  @include('partials.ui.copyright')
  @livewireScriptConfig
</body>

</html>
