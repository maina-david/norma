<div>
  @if (!$empty)
    <x-assess.assessment-item.assessment-item-data-table
      :base-query="$baseQuery"
      :route="$route"
      :fields="['description_with_domain']"
      :headings="false"
    />
  @else
    <x-ui.empty-state-icon icon="clipboard-list" :title="__('assess.no_assessment_items')" />
  @endif
</div>
