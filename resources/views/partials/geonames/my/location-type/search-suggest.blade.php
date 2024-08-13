  @if (!$locationTypes->isEmpty())
    <x-ui.search-suggestion-category :title="__('geonames.location_type.jurisdiction_types')"
                                     :filterByText="__('corpus.work.filter_requirements_by')"
                                     icon="earth-africa">
      @foreach ($locationTypes as $locationType)
        <div class="mb-4 text-sm">
          <x-ui.icon name="earth-africa" class="mr-3" size="4" />
          @if ($linkToDetailed)
            <a data-turbo-frame="{{ $linkTarget ?? '_top' }}"
               href="{{ route('my.corpus.references.index', ['jurisdictionTypes[]' => $locationType->id]) }}"
               class="text-primary cursor-pointer">{{ __('corpus.location_type.' . $locationType->adjective_key) }}</a>
          @else
            <a data-turbo-frame="{{ $linkTarget ?? '_top' }}"
               @click="$dispatch('filtered', { value: {{ $locationType->id }}, filter: 'jurisdictionTypes' });"
               class="text-primary cursor-pointer">{{ __('corpus.location_type.' . $locationType->adjective_key) }}</a>
          @endif
        </div>
      @endforeach
    </x-ui.search-suggestion-category>
  @endif
