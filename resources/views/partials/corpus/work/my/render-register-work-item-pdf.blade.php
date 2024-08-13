<div class=border-b border-libryo-gray-100 pb-2 mb-5 print:mb-1"">
  <div>
    <div class="p-2 flex flex-row justify-between">
      <div>
        <x-corpus.work.work-type :work="$work" />
        <a class="hover:text-primary text-sm font-semibold"
           href="{{ route('my.corpus.works.show', ['work' => $work->id, 'view' => 'text']) }}">{{ $work->title }}</a>
        @if ($work->title_translation)
          <span class="text-libryo-gray-500 text-sm">[{{ $work->title_translation }}]</span>
        @endif
      </div>
    </div>

    @if (!$work->references->isEmpty())
      <div>
        <div>
          @foreach ($work->references as $ref)
            <div class="group">
              <div class="pl-10 flex items-center">
                <x-ui.icon name="arrow-turn-down-right" size="3" class="text-libryo-gray-400 mr-3" />
                <a href="{{ route('my.corpus.references.show', ['reference' => $ref->id]) }}"
                   class="cursor-pointer text-primary hover:text-primary-darker text-xs">{{ $ref->refPlainText?->plain_text }}</a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  @if ($work->relationLoaded('children') && !$work->children->isEmpty())
    @foreach ($work->children as $child)
      <div class="pl-10">
        @include('partials.corpus.work.my.render-register-work-item', [
            'work' => $child,
            'isChild' => true,
        ])
      </div>
    @endforeach
  @endif
</div>
