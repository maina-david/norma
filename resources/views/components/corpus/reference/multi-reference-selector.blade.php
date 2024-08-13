@php
$selected = json_encode(collect($references)->map(fn ($ref) => ['id' => $ref->id, 'label' => htmlentities($ref->refPlainText?->plain_text ?? '')])->toArray());
if ($last = last($references)) {
    $last = json_encode(['id' => $last->id, 'label' => htmlentities($last->refPlainText?->plain_text ?? '')]);
}
$selected = str_replace('"', "'", $selected);
$last = str_replace('"', "'", $last);
@endphp
<div x-data="{
  fetchFilter: typeof fetchFilter === 'undefined' ? {} : fetchFilter,
  selectedReferences: {{ $selected }},
  lastSelected: {{ empty($last) ? '{}' : $last }},
  showSectionSelector: {{ $work ? 'true' : 'false' }},
  handleRefSelect: function($event) {
    this.lastSelected = $event.detail;
    var selectedIndex = this.selectedReferences.indexOf($event.detail.id)
    if(selectedIndex !== -1) {
      var refs = Array.from(this.selectedReferences);
      refs.splice(selectedIndex, 1);
      this.selectedReferences = Array.from(refs);
      Array.from($refs.referencesSelect.options).some(function (i) { if (i.value == $event.detail.id) { i.remove(); return true;} return false })
      return;
    }
    this.selectedReferences.push($event.detail.id);
    $dispatch('selected-reference', $event.detail);
    var opt = document.createElement('option');
    opt.value = $event.detail.id;
    opt.setAttribute('selected', true);
    $refs.referencesSelect.appendChild(opt);
  }
 }">


  @if(!($single ?? false))
    <div class="flex flex-row my-6 items-center ml-2 h-10">
      <div>
        {{ __('corpus.reference.sections_selected') }}: <span x-text="selectedReferences.length"></span>
      </div>
      <x-ui.button x-show="selectedReferences.length > 0" class="ml-2" @click="selectedReferences = []"
                   styling="outline" theme="gray">
        <x-ui.icon name="times" class="mr-2" />
        {{ __('actions.clear') }}
      </x-ui.button>
    </div>
  @endif

  <x-corpus.work.work-selector
    @selected-work="document.getElementById('multi-reference-selector-references-for-work').src = '{{ $listRoute }}'.replace(/\/([0-9]+)(\?has-.+)?$/, '/' +$event.detail.id + '$2') + (fetchFilter.query || ''); showSectionSelector = true;"
    :work="$work"
    :app-type="$appType"
    :name="$workFieldName ?? 'work_id'"
  />

  <div class="mt-2">

    <x-ui.dropdown>
      <x-slot name="trigger">
        <x-ui.button x-show="showSectionSelector" theme="primary" styling="outline" class="ml-2">
          @if($single ?? false)
            <span x-show="lastSelected.label" x-html="lastSelected.label"></span>
            <span x-show="!lastSelected.label">{{ __('corpus.reference.select_section') }}</span>
          @else
            <span>{{ __('corpus.reference.select_section') }}</span>
          @endif
          <x-ui.icon name="chevron-down" class="ml-2" size="3" />
        </x-ui.button>
      </x-slot>

      <div @@selected="handleRefSelect($event);" x-data="{ open: false }">
        <turbo-frame id="multi-reference-selector-references-for-work"@if($work) src="{{ $listRoute }}" @endif>
          <x-ui.skeleton />
        </turbo-frame>
      </div>
    </x-ui.dropdown>

  </div>

  <select x-ref="referencesSelect" name="{{ $name ?? 'references' }}" class="hidden" multiple>
    @foreach($references as $ref)
        <option selected value="{{ $ref->id }}"></option>
    @endforeach
  </select>
</div>
