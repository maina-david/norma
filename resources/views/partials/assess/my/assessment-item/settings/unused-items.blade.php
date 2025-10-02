@if ($count > 0)
  <x-ui.alert-box class="my-7">
    {{ __('assess.setup.unused_items_report_info') }}
  </x-ui.alert-box>

  <x-assess.assessment-item.assessment-item-data-table :base-query="$baseQuery"
                                                       :route="route('my.settings.assess.setup.unused.items.for.norma', ['norma' => $norma])"
                                                       :fields="['description']"
                                                       actionable
                                                       :actions="['add_unused_items']"
                                                       :actions-route="route('my.settings.assess.setup.actions.for.norma', ['norma' => $norma])"
                                                       :paginate="250">
  </x-assess.assessment-item.assessment-item-data-table>

@else
  <div class="py-10">
    <x-ui.empty-state-icon icon="check-circle"
                           :title="__('assess.setup.no_unused_items')" />
  </div>
@endif
