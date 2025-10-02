@php
  $bound = $bound ?? false;
  $doc = $bound ? "'affected_legislation[' + num + '][catalogue_title]'" : 'catalogue_title';
  $work = $bound ? "'affected_legislation[' + num + '][work_title]'" : 'work_title';
@endphp
<x-ui.label for="" class="mt-4">
  <span class="mr-2">{{ __('corpus.work.catalogue_doc') }}</span>
  @if(($item->catalogue_doc_id ?? false))
    @can('collaborate.corpus.catalogue-doc.view')
      <a class="text-primary" target="_blank" href="{{ route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => $item->catalogue_doc_id]) }}">
        <x-ui.icon name="arrow-up-right-from-square" size="3" />
      </a>
    @endcan
  @endif
</x-ui.label>
<div class="flex items-start group">
  <div class="flex-grow">
    <div class="catalogue-doc-id-input mt-2 font-medium text-norma-gray-900">
      <div class="doc-title">{{ $item->catalogueDoc->title ?? '-' }}</div>
      <div class="doc-title-translation">{{ $item->catalogueDoc->title_translation ?? '' }}</div>
    </div>
  </div>


  <div class="pb-1 ml-1 flex-shrink-0 pt-2">
    <x-ui.button
        styling="outline"
        class="tippy"
        data-tippy-content="{{ __('corpus.work.select_catalogue_doc') }}"
        @click="function () { onSelect = function (detail, type) { updateFields($event.target, type, detail); }; selectingDoc = true; selectingDocLoaded = true; }"
    >
      <x-ui.icon name="magnifying-glass" />
    </x-ui.button>

    <x-ui.button
        styling="outline"
        class="tippy"
        data-tippy-content="{{ __('actions.remove') }}"
        @click="$el.closest('.group').querySelector('.doc-title').innerHTML = '-';$el.closest('.group').querySelector('.doc-title-translation').innerHTML = '';$el.closest('.legislation-group').querySelector('.catalogue-doc-id').value = ''"
    >
      <x-ui.icon name="times" />
    </x-ui.button>
  </div>
</div>


<x-ui.label for="" class="mt-4">
  <span class="mr-2">{{ __('corpus.doc.work') }}</span>
  @if(($item->work_id ?? false))
    @can('collaborate.corpus.work.view')
      <a class="text-primary" target="_blank" href="{{ route('collaborate.works.show', ['work' => $item->work_id]) }}">
        <x-ui.icon name="arrow-up-right-from-square" size="3" />
      </a>
    @endcan
  @endif
</x-ui.label>
<div class="flex items-start group">
  <div class="flex-grow">
    <div class="work-id-input mt-2 font-medium text-norma-gray-900">
      <div class="doc-title">{{ $item->work->title ?? '-' }}</div>
      <div class="doc-title-translation">{{ $item->work->title_translation ?? '' }}</div>
    </div>
  </div>

  <div class="pb-1 ml-1 flex-shrink-0 pt-2">
    <x-ui.button
        styling="outline"
        class="tippy"
        data-tippy-content="{{ __('corpus.work.select_work') }}"
        @click="function () { onSelect = function (detail, type) { updateFields($event.target, type, detail); }; selectingWork = true; selectingWorkLoaded = true; }"
    >
      <x-ui.icon name="magnifying-glass" />
    </x-ui.button>

    <x-ui.button
        styling="outline"
        class="tippy"
        data-tippy-content="{{ __('actions.remove') }}"
        @click="$el.closest('.group').querySelector('.doc-title').innerHTML = '-';$el.closest('.group').querySelector('.doc-title-translation').innerHTML = '';$el.closest('.legislation-group').querySelector('.work-id').value = ''"
    >
      <x-ui.icon name="times" />
    </x-ui.button>
  </div>
</div>
