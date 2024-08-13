<div class="multi-ref-selector">
  <x-corpus.reference.reference-data-table
    :base-query="$baseQuery"
    :route="$route"
    :fields="['title_for_selector']"
    :filter-route="request()->fullUrl()"
    :headings="false"
    searchable
  >
    <x-slot:actionButton>
      @if($withSelectAll ?? false)
        <div class="flex px-4 py-2 space-x-4">
          <x-ui.button
              type="button"
              styling="outline"
              @click.prevent="$el.closest('.multi-ref-selector').querySelectorAll('td:first-child .selector-option').forEach(function (e) { e.click(); })"
          >
            {{ __('interface.select_all_none') }}
          </x-ui.button>
        </div>
      @endif
    </x-slot:actionButton>
  </x-corpus.reference.reference-data-table>
</div>
