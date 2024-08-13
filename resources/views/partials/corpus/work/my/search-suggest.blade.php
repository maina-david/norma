@if (!$works->isEmpty())
  <div class="mt-10">
    <div class="text-xl mb-4">
      <x-ui.icon name="gavel" class="mr-3" />
      <span>{{ __('corpus.work.requirement_documents') }}</span>
    </div>
    <div class="italic text-sm mb-4">
      <span>{{ __('corpus.work.found_requirements_with_title_matching') }}: </span>
      <span class="text-primary">{{ $search }}</span>
    </div>

    @foreach ($works as $work)
      <div class="mb-4 text-sm flex">
        <x-ui.icon name="gavel" class="mr-3" size="4" />
        @if ($linkToDetailed)
          <a data-turbo-frame="{{ $linkTarget ?? '_top' }}"
             href="{{ route('my.corpus.references.index', ['works[]' => $work->id]) }}"
             class="text-primary cursor-pointer"
          >
            {{ $work->title }}
            @if($work->title_translation)
              [{{ $work->title_translation }}]
            @endif

            @foreach ($work->parents as $parent)
              <div class="text-libryo-gray-600 mt-1 text-xs">
                {{ $parent->title }}
                @if($parent->title_translation)
                  [{{ $parent->title_translation }}]
                @endif
              </div>
            @endforeach
          </a>
        @else
          <a class="text-primary cursor-pointer"
             @click="$dispatch('filtered', { value: {{ $work->id }}, filter: 'works' });"
          >
            {{ $work->title }}
            @if($work->title_translation)
              [{{ $work->title_translation }}]
            @endif

            @foreach ($work->parents as $parent)
              <div class="text-libryo-gray-600 mt-1 text-xs">
                {{ $parent->title }}
                @if($parent->title_translation)
                  [{{ $parent->title_translation }}]
                @endif
              </div>
            @endforeach
          </a>
        @endif
      </div>
    @endforeach
  </div>
@endif
