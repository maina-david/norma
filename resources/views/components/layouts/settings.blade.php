<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
  @include('partials.head')
</head>

<body class="bg-libryo-gray-50 font-sans text-libryo-gray-900 antialiased">
  <div x-data="{ 'sidebarShow': false }" class="h-screen flex overflow-hidden bg-libryo-gray-100">
    <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
    <div :class="{ 'hidden': !sidebarShow }" class="fixed inset-0 flex z-30 lg:relative lg:w-64 lg:block" role="dialog"
         aria-modal="true">
      <div :class="{ 'hidden': !sidebarShow }"
           x-transition:enter="transition-opacity ease-linear duration-300 lg:block"
           x-transition:leave="transition-opacity ease-linear duration-300"
           class="fixed inset-0 bg-gray-600 bg-opacity-75 lg:hidden"
           aria-hidden="true"
           @click="sidebarShow = false"></div>
      <div
           :class="{ 'flex': sidebarShow, 'hidden': !sidebarShow }"
           x-transition:enter="transition ease-in-out duration-300 transform"
           x-transition:leave="transition ease-in-out duration-300 transform"
           class="relative flex-1 lg:flex flex-col max-w-xs w-full bg-white min-h-screen">
        <div
             x-show="sidebarShow"
             x-transition:enter="ease-in-out duration-300"
             x-transition:leave="ease-in-out duration-300"
             class="absolute top-0 right-0 -mr-12 pt-2 lg:hidden">
          <button
                  @click="sidebarShow = false"
                  type="button"
                  class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
            <span class="sr-only">Close sidebar</span>
            <!-- Heroicon name: outline/x -->
            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <x-ui.settings.sidebar />

      </div>

      <div class="shrink-0 w-14 hidden md:block">
        <!-- Force sidebar to shrink to fit close icon -->
      </div>
    </div>

    {{-- <!-- Static sidebar for desktop --> --}}
    {{-- <div class="hidden lg:flex md:shrink-0"> --}}
    {{-- <div class="flex flex-col w-64"> --}}
    {{-- <!-- Sidebar component, swap this element with another sidebar if you like --> --}}
    {{-- <x-ui.settings.sidebar /> --}}
    {{-- </div> --}}
    {{-- </div> --}}


    <div class="flex flex-col w-0 flex-1 overflow-hidden">
      <div class="flex-1 relative z-0 overflow-y-auto focus:outline-none">
        <div class="py-4">
          <div class=" px-2 sm:px-4 md:px-6">
            <div class="">

              <header class=" ">
                <div class="mb-5 flex justify-between items-center w-full overflow-x-hidden">
                  <div class="flex flex-shrink-0 items-center flex-grow">
                    <button class="lg:hidden mr-2 p-2" @click="sidebarShow = true">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                           stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h8m-8 6h16" />
                      </svg>
                    </button>
                    <div class="text-xl font-semibold text-libryo-gray-900">{{ $header ?? '' }}</div>
                  </div>
                  <div class="flex-shrink-0">{{ $actions ?? '' }}</div>
                </div>
              </header>

              <main>
                <div class="">
                  {{ $slot }}
                </div>
              </main>
            </div>
            <!-- /End replace -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <x-ui.alert />
  <turbo-frame data-turbo-cache="false" id="global-alert" class="hidden"></turbo-frame>
  @livewireScriptConfig
</body>

</html>
