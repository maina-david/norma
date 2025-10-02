@php
use App\Enums\Assess\ResponseStatus;
@endphp

<div>

  <x-assess.assessment-item-response.metrics-data-table
                                                        :base-query="$baseQuery"
                                                        :route="route('my.assess.assessment-item-responses.metrics')"
                                                        searchable
                                                        :fields="[
                                                          'risk_rating',
                                                          'norma_title',
                                                          'last_answered',
                                                          'total_count',
                                                          'total_' . ResponseStatus::yes()->value,
                                                          'total_' . ResponseStatus::no()->value,
                                                          'total_' . ResponseStatus::notApplicable()->value,
                                                          'total_' . ResponseStatus::notAssessed()->value,
                                                          'percentage_non_compliant_items'
                                                        ]"
                                                        :paginate="50">
    <x-slot name="actionButton">
      <x-ui.modal>
        <x-slot name="trigger">
          <x-ui.button @click="open = true" styling="flat" theme="tertiary"
                       data-tippy-content="{{ __('interface.export') }}"
                       class="tippy mt-1">
            <x-ui.icon name="download" />
          </x-ui.button>
        </x-slot>

        <div class=" w-screen-50">
          <turbo-frame id="download-progress" loading="lazy"
                       src="{{ route('my.assessment-item-responses.metrics.export.excel', $filters) }}">
          </turbo-frame>
        </div>
      </x-ui.modal>


    </x-slot>
  </x-assess.assessment-item-response.metrics-data-table>
</div>
