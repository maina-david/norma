<x-customer.norma.my.settings.layout :norma="$norma">
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
          <turbo-frame loading="lazy" id="settings-assess-setup-for-norma-unused-items-{{ $norma->id }}"
                       src="{{ route('my.settings.assess.setup.unused.items.for.norma', ['norma' => $norma]) }}">
          </turbo-frame>
        </div>
      </x-ui.tab-content>

      <x-ui.tab-content name="used">
        <turbo-frame loading="lazy" id="settings-assess-setup-for-norma-used-items-{{ $norma->id }}"
                     src="{{ route('my.settings.assess.setup.used.items.for.norma', ['norma' => $norma]) }}">
        </turbo-frame>
      </x-ui.tab-content>

    </x-ui.tabs>
  </div>
</x-customer.norma.my.settings.layout>
