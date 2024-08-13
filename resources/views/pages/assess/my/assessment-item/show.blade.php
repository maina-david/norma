<x-layouts.app>
  <x-slot name="header">
    <a href="{{ route('my.assess.assessment-item-responses.index') }}" class="text-primary mr-3">
      <x-ui.icon name="chevron-up" />
    </a>
    <span class="text-base font-bold">{{ $assessmentItem->toDescription() }}</span>
    <span class="text-xs text-libryo-gray-500 italic ml-3">{{ $subTitle }}</span>
  </x-slot>

  @if ($responsesCount === 0)
    <div class="mt-20">
      <x-ui.empty-state-icon icon="clipboard-list"
                             :title="__('assess.assessment_item.no_responses', ['title' => $subTitle])" />
    </div>
  @else
    <x-assess.assessment-item-response.response-data-table
                                                           :base-query="$baseQuery"
                                                           :route="route('my.assess.assessment-item.show', ['assessmentItem' => $assessmentItem])"
                                                           :fields="$tableFields"
                                                           :searchable="!$isSingleMode"
                                                           :filterable="!$isSingleMode"
                                                           :actionable="$responsesCount > 1"
                                                           :actions-route="route('my.assess.assessment-item-responses.actions')"
                                                           :actions="$actions"
                                                           :filters="['domains', 'answer', 'answered']"
                                                           :paginate="25" />
  @endif
</x-layouts.app>
