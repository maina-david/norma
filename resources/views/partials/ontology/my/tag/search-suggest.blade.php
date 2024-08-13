  @php
    use App\Enums\Auth\UserActivityType;
  @endphp

  @if (!$tags->isEmpty())
    <div class="text-xl mb-4">
      <x-ui.icon name="tags" class="mr-3" />
      <span>{{ __('ontology.tag.topics') }}</span>
    </div>
    <div class="italic text-sm mb-4">
      <x-ui.icon name="filter" size="3" class="mr-1" />
      <span>{{ __('corpus.work.filter_requirements_by') }}: </span>
    </div>

    @foreach ($tags as $tag)
      <div class="mb-4 text-sm">
        <x-ui.icon name="tags" class="mr-3" size="4" />
        @if ($linkToDetailed)
          <a data-turbo-frame="{{ $linkTarget ?? '_top' }}"
             href="{{ route('my.corpus.references.index', ['tags[]' => $tag->id]) }}"
             @click="window.axios.post('{{ route('my.user.activities.track') }}', {type: {{ UserActivityType::searchTerm()->value }}, details: {term: '{{ $search }}', tag_id: {{ $tag->id }}}})"
             class="text-primary cursor-pointer">{{ $tag->title }}</a>
        @else
          <a data-turbo-frame="{{ $linkTarget ?? '_top' }}"
             @click="$dispatch('filtered', { value: {{ $tag->id }}, filter: 'tags' }); window.axios.post('{{ route('my.user.activities.track') }}', {type: {{ UserActivityType::searchTerm()->value }}, details: {term: '{{ $search }}', tag_id: {{ $tag->id }}}})"
             class="text-primary cursor-pointer">{{ $tag->title }}</a>
        @endif

      </div>
    @endforeach
  @endif
