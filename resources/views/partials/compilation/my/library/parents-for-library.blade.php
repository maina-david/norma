<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('compilation.library.parent_libraries_info', ['library' => $library->title]) }}
  </x-ui.alert-box>
</div>

<x-compilation.library.library-data-table :base-query="$baseQuery"
                                          :route="route('my.settings.children-parents.for.library.index', ['library' => $library->id, 'relation' => 'parents'])"
                                          searchable
                                          actionable
                                          :actions="['remove_parents_from_library']"
                                          :actions-route="route('my.settings.children-parents.for.library.actions.' . ($organisation ? 'organisation' : 'all'), ['library' => $library->id, 'relation' => 'parents', 'organisation' => $organisation->id ?? 'all'])"
                                          :paginate="50">
  <x-slot name="actionButton">
    <x-general.add-items-to-item-modal items-name="libraries"
                                       :tooltip="__('compilation.library.add_parents')"
                                       :action-route="route('my.settings.children-parents.for.library.add', ['library' => $library->id, 'relation' => 'parents'])"
                                       :route="route('my.settings.libraries.index')"
                                       :placeholder="__('compilation.library.select_libraries_to_add_as_parent', ['library' => $library->title])" />
  </x-slot>
</x-compilation.library.library-data-table>
