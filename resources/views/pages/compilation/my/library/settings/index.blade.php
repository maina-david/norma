<x-layouts.settings>
  <x-slot name="header">
    <x-ui.icon name="books" size="10" class="mr-5 text-libryo-gray-600" /> {{ __('settings.nav.libraries') }}
  </x-slot>

  <x-slot name="actions">
    <x-ui.button styling="outline"
                 theme="primary"
                 type="link"
                 href="{{ route('my.settings.libraries.create') }}">
      {{ __('actions.create') }}</x-ui.button>
  </x-slot>

  <x-compilation.library.library-data-table :base-query="$baseQuery"
                                            :route="route('my.settings.libraries.index')"
                                            searchable
                                            :fields="['title']"
                                            :paginate="50" />
</x-layouts.settings>
