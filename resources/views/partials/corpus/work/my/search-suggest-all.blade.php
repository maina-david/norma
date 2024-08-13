@php
use Illuminate\Support\Str;
@endphp

@if ($search === '' || is_null($search))
  <div class="mt-10">
    <x-ui.empty-state-icon icon="arrow-up"
                           :title="__('interface.start_typing_to_search') . '...'" />

    @if ($tag)
      <div class="text-center mt-3 italic">
        {{ __('interface.try_searching_tag') }}
        <span class="bg-libryo-gray-50 mx-1 p-1">{{ Str::limit($tag->title, 100, '...') }}</span>
      </div>
    @endif

  </div>
@else
  <x-ui.my.word-search :search-str="$search"
                       @click="$dispatch('filtered', { value: '{{ $search }}', filter: 'search' });"
                       :frame-target="$linkTarget ?? '_top'" />

  <turbo-frame id="search-suggest-location-types-requirements-search"
               src="{{ route('my.geonames.location-types.search-suggest', ['key' => 'requirements-search', 'search' => $search]) }}"
               loading="lazy">
    <x-ui.skeleton delayed />
  </turbo-frame>

  <turbo-frame id="search-suggest-works-requirements-search"
               src="{{ route('my.corpus.works.search-suggest', ['key' => 'requirements-search', 'search' => $search]) }}"
               loading="lazy">
    <x-ui.skeleton delayed />
  </turbo-frame>

  <turbo-frame id="search-suggest-legal-domains-requirements-search"
               src="{{ route('my.ontology.legal-domains.search-suggest', ['key' => 'requirements-search', 'search' => $search]) }}"
               loading="lazy">
    <x-ui.skeleton delayed />
  </turbo-frame>

  <div class="flex flex-row mt-10">
    <div class="grow mr-8">

      <turbo-frame id="search-suggest-categories-requirements-search"
                   src="{{ route('my.ontology.categories.search-suggest', ['key' => 'requirements-search', 'search' => $search]) }}"
                   loading="lazy">
        <x-ui.skeleton delayed />
      </turbo-frame>
    </div>
    <div class="grow mr-4 min-w-1/2">

    </div>
  </div>

  <div class="flex flex-row mt-10">
    <div class="grow mr-8">

      <turbo-frame id="search-suggest-categories-controls-requirements-search"
                   src="{{ route('my.ontology.categories.controls.search-suggest', ['key' => 'requirements-search', 'search' => $search]) }}"
                   loading="lazy">
        <x-ui.skeleton delayed />
      </turbo-frame>
    </div>
    <div class="grow mr-4 min-w-1/2">

    </div>
  </div>
@endif
