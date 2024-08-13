<div x-data="{ searchStr: '' }" class="my-5">
  <x-ui.dropdown class="w-60 left-2">
    <x-slot name="trigger">
      <div class="text-sm flex flex-row justify-between cursor-pointer px-3">
        <div>
          {{ $active['title'] }}
        </div>
        <div class="ml-2">
          <x-ui.icon name="chevron-down">
          </x-ui.icon>
        </div>
      </div>
    </x-slot>

    <div class="p-6 text-sm">
      <div class="font-bold">{{ __('customer.organisation.switch_organisation') }}</div>
      <div class="flex flex-row items-center mt-5">
        <x-ui.icon name="search" class="text-xl mr-5" />
        <turbo-frame id="organisation-switcher-search-input">
          <x-ui.form x-ref="orgSwitcherSearchForm" method="post"
                     :action="route('my.settings.organisations.switcher.search')">
            <x-ui.input
                        @keyup.debounce="if($event.key === 'Escape')  { $el.value = ''; searchStr = ''; } else searchStr = $el.value;  if(searchStr !== '') $refs.orgSwitcherSearchForm.requestSubmit();"
                        name="search_organisations"
                        :placeholder="__('customer.organisation.search_for_organisation')"
                        class="text-sm"
                        autocomplete="off" />
          </x-ui.form>
        </turbo-frame>
      </div>

      <div x-show="searchStr !== ''">
        <turbo-frame id="organisation-switcher-search-results" target="_top"></turbo-frame>
      </div>

      @if (userCanManageAllOrgs())
        <div class="border-t mt-10 pt-2">
          <x-ui.form method="post"
                     action="{{ route('my.settings.organisations.activate.all') }}">
            <button class="text-primary my-3 block text-left" role="menuitem" tabindex="-1">
              {{ __('customer.organisation.activate_all') }}
            </button>
          </x-ui.form>
        </div>
      @endif

    </div>
  </x-ui.dropdown>
</div>
