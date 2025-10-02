<x-customer.norma.my.settings.compilation-layout :norma="$norma">
  <x-ui.form :enctype="$enctype ?? null" method="post" :action="route($baseRoute . '.store', $baseRouteParams ?? [])">

    @include($form)

    <div class="mt-2 flex justify-end">
      <x-ui.button theme="primary" type="submit">{{ __('actions.add') }}</x-ui.button>
    </div>
  </x-ui.form>
</x-customer.norma.my.settings.compilation-layout>
