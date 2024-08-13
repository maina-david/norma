@php use App\Enums\Application\ApplicationType; @endphp
@props(['appType' => ApplicationType::collaborate(), 'work' => null])

<div x-data="{ workId: {{ $work->id ?? 0 }}, clearAndLoad: function (el) {el.tomselect.clear();el.tomselect.clearOptions();el.tomselect.load('')} }">
  <x-corpus.work.work-selector
    :app-type="$appType"
    :work="$work"
    @selected-work="workId = $event.detail.id;clearAndLoad($refs.expSel)"
  />

  <div class="px-2 max-w-xs" x-show="workId != 0">
    <x-ui.input
        x-ref="expSel"
        class="remote-select"
        type="select"
        :options="[]"
        label=""
        name="work_expression_id"
        :placeholder="__('corpus.work_expression.index_title')"
        data-load-start-searching="0"
        data-load-search-field="no-filter"
        :data-fetch-on-load="(bool) $work"
        x-bind:data-load="'{{ route('collaborate.work-expressions.json', ['work' => '0']) }}'.replace(/\/\d+\//, '/' + workId + '/')"
        :no-error="true"
    />
  </div>
</div>
