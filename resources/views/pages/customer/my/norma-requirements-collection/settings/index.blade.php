<x-customer.norma.my.settings.compilation-layout :norma="$norma">

  <div class="flex justify-end">
    <x-ui.button type="link" :href="route($baseRoute . '.create', $baseRouteParams ?? [])" theme="primary">{{ __('actions.add') }}
    </x-ui.button>
  </div>

  @include('pages.crud.index-table')
</x-customer.norma.my.settings.compilation-layout>
