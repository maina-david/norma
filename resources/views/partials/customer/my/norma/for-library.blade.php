<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('compilation.library.normas_in_library_info', ['library' => $library->title, 'organisation' => $organisation ? $organisation->title : __('customer.organisation.all_organisations')]) }}
  </x-ui.alert-box>
</div>

<x-customer.norma.norma-data-table :base-query="$baseQuery"
                                     :route="route('my.settings.normas.for.library.index', ['library' => $library->id])"
                                     searchable
                                     filterable
                                     :fields="['title', 'active']"
                                     :filters="['deactivated']"
                                     :paginate="50" />
