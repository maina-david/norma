<div class="mb-5">
  <x-ui.form method="post" :action="route('my.settings.compilation.libraries.session.add', ['key' => $key])">
    <div class="flex flex-col lg:flex-row border border-libryo-gray-200 p-4 rounded mt-4">
      <div class="flex-grow">
        <x-compilation.library.library-selector label="" placeholder="{{ __('compilation.library.select_libraries') }}" />
      </div>

      <div>
        <x-ui.button type="submit" theme="primary" class="ml-2 mt-2">
          {{ __('actions.add') }}
        </x-ui.button>
      </div>
    </div>
  </x-ui.form>

</div>

<x-compilation.library.library-data-table :base-query="$baseQuery"
                                          :route="route('my.settings.compilation.libraries.session', ['key' => $key])"
                                          searchable
                                          :paginate="20">

</x-compilation.library.library-data-table>
