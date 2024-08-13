<div class="flex-1 flex flex-col min-h-0 border-r border-libryo-gray-200 bg-white">
  <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
    <div class="flex items-center shrink-0 px-4 font-bold text-lg">
      {{ __('settings.nav.org_settings') }}
    </div>
    <div>
      <x-customer.organisation-switcher />
    </div>
    <nav class="mt-5 flex-1 px-2 bg-white space-y-1">
      <!-- Current: "bg-libryo-gray-100 text-libryo-gray-900", Default: "text-libryo-gray-600 hover:bg-libryo-gray-50 hover:text-libryo-gray-900" -->

      @foreach ($items as $item)
        <x-ui.settings.nav-item
                                class="w-full sm:w-auto"
                                :icon="$item['icon']"
                                :route="$item['route']">{{ $item['label'] }}
        </x-ui.settings.nav-item>
      @endforeach

    </nav>
  </div>
  <div class="shrink-0 flex border-t border-libryo-gray-200 p-4">
    <a href="{{ route('my.dashboard') }}" class="shrink-0 w-full group block">
      <div class="flex flex-row items-center">
        <div>
          <x-ui.icon name="arrow-left" class="text-xl mr-5" />
        </div>
        <div>
          {{ __('settings.nav.back_to_libryo') }}
        </div>
      </div>
    </a>
  </div>
</div>
