@if ($count > 0)
  <x-ui.alert-box class="my-7">
    {{ __('assess.setup.used_items_report_info') }}
  </x-ui.alert-box>

  <x-assess.assessment-item-response.response-data-table :base-query="$baseQuery"
                                                         :route="route('my.settings.assess.setup.used.items.for.libryo', ['libryo' => $libryo])"
                                                         :fields="['assessment_item_description']"
                                                         actionable
                                                         :actions="['remove_used_items']"
                                                         :actions-route="route('my.settings.assess.setup.actions.for.libryo', ['libryo' => $libryo])"
                                                         :paginate="250">
  </x-assess.assessment-item-response.response-data-table>

@else
  <div class="py-10">
    <x-ui.empty-state-icon icon="check-circle"
                           :title="__('assess.setup.no_used_items')" />
  </div>
@endif
