  @if (!$domains->isEmpty())
    <div class="mt-10">
      <div class="text-xl mb-4">
        <x-ui.icon name="hashtag" class="mr-3" />
        <span>{{ __('ontology.legal_domain.categories') }}</span>
      </div>
      <div class="italic text-sm mb-4">
        <x-ui.icon name="filter" size="3" class="mr-1" />
        <span>{{ __('corpus.work.filter_requirements_by') }}: </span>
      </div>

      @foreach ($domains as $domain)
        <div class="mb-4 text-sm">
          <x-ui.icon name="hashtag" class="mr-3" size="4" />
          @if ($linkToDetailed)
            <a data-turbo-frame="{{ $linkTarget ?? '_top' }}"
               href="{{ route('my.corpus.references.index', ['domains[]' => $domain->id]) }}"
               class="text-primary cursor-pointer">{{ $domain->title }}</a>
          @else
            <a data-turbo-frame="{{ $linkTarget ?? '_top' }}"
               @click="$dispatch('filtered', { value: {{ $domain->id }}, filter: 'domains' });"
               class="text-primary cursor-pointer">{{ $domain->title }}</a>
          @endif

        </div>
      @endforeach
    </div>
  @endif
