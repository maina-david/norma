<div>
  @if ($showSelf)

    @if ($work->relationLoaded('children') && $work->children->isNotEmpty())
      <div class="text-xl mb-3 ml-4">{{ __('corpus.work.primary_document') }}</div>
      <div>
        <x-corpus.work.work-panel :work="$work" show-type :show-flag="false">
          @include('partials.corpus.work.show-for-requirements', ['work' => $work])
        </x-corpus.work.work-panel>
      </div>
    @else
      @include('partials.corpus.work.show-for-requirements', ['work' => $work])
    @endif
  @endif


  @if ($work->relationLoaded('children') && $work->children->isNotEmpty())
    <div class="text-xl mb-3 ml-4 mt-5">{{ __('corpus.work.subsidiary_documents') }}</div>
    <div>
      @foreach ($work->children as $childWork)
        <x-corpus.work.work-panel :work="$childWork" show-type :show-flag="false">
          @include('partials.corpus.work.show-for-requirements', ['work' => $childWork])
        </x-corpus.work.work-panel>
      @endforeach
    </div>

  @endif
</div>
