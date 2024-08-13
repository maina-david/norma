@php
  use App\Enums\Auth\UserActivityType;
  $filterName = $controls ? 'controls' : 'topics';
@endphp

@if (!$categories->isEmpty())
  @if($controls)
    <div class="text-xl mb-4">
      <x-ui.icon name="gamepad-modern" class="mr-3" />
      <span>{{ __('assess.assessment_item_response.control_topics') }}</span>
    </div>
    @else
    <div class="text-xl mb-4">
      <x-ui.icon name="tags" class="mr-3" />
      <span>{{ __('ontology.tag.topics') }}</span>
    </div>
  @endif
  <div class="italic text-sm mb-4">
    <x-ui.icon name="filter" size="3" class="mr-1" />
    <span>{{ __('corpus.work.filter_requirements_by') }}: </span>
  </div>

  @foreach ($categories as $category)
    <div class="mb-4 text-sm">
      <x-ui.icon name="tags" class="mr-3" size="4" />
      @if ($linkToDetailed)
        <a
         data-turbo-frame="{{ $linkTarget ?? '_top' }}"
         href="{{ route('my.corpus.references.index', [$filterName . '[]' => $category->id]) }}"
         @click="window.axios.post('{{ route('my.user.activities.track') }}', {type: {{ UserActivityType::searchTerm()->value }}, details: {term: '{{ $search }}', category_id: {{ $category->id }}}})"
         class="text-primary cursor-pointer"
        >
          {{ $category->display_label }}
        </a>
      @else
        <a
         data-turbo-frame="{{ $linkTarget ?? '_top' }}"
         @click="$dispatch('filtered', { value: {{ $category->id }}, filter: '{{ $filterName }}' }); window.axios.post('{{ route('my.user.activities.track') }}', {type: {{ UserActivityType::searchTerm()->value }}, details: {term: '{{ $search }}', category_id: {{ $category->id }}}})"
         class="text-primary cursor-pointer"
        >
          {{ $category->display_label }}
        </a>
      @endif

    </div>
  @endforeach
@endif
