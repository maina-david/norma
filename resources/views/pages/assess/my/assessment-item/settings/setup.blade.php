<x-customer.libryo.my.settings.layout :libryo="$libryo">
  <div class="mt-10">
    <x-ui.tabs>

      <x-slot name="nav">
        <x-ui.tab-nav name="unused">
          {{ __('settings.nav.assess.unused_assessment_items_report') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="used">
          {{ __('settings.nav.assess.used_assessment_items_report') }}</x-ui.tab-nav>
      </x-slot>

      <x-ui.tab-content name="unused">
        <div class="mt-5">
          <turbo-frame loading="lazy" id="settings-assess-setup-for-libryo-unused-items-{{ $libryo->id }}"
                       src="{{ route('my.settings.assess.setup.unused.items.for.libryo', ['libryo' => $libryo]) }}">
          </turbo-frame>
        </div>
      </x-ui.tab-content>

      <x-ui.tab-content name="used">
        <turbo-frame loading="lazy" id="settings-assess-setup-for-libryo-used-items-{{ $libryo->id }}"
                     src="{{ route('my.settings.assess.setup.used.items.for.libryo', ['libryo' => $libryo]) }}">
        </turbo-frame>
      </x-ui.tab-content>

    </x-ui.tabs>
  </div>
</x-customer.libryo.my.settings.layout>
