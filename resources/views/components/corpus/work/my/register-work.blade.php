@props(['work', 'isChild' => false, 'showFlag' => true])
<div {{ $attributes->merge(['class' => 'border-b border-libryo-gray-100 pb-2 mb-5 print:mb-1']) }}>
  <div>
    <div class="p-2 flex flex-row justify-between">
      <div>
        <x-corpus.work.work-type :work="$work" />
        {{-- remove if not reverted after launch --}}
        <a class="hover:text-primary text-sm font-semibold"
           href="{{ route('my.corpus.works.show', ['work' => $work->id, 'view' => 'text']) }}">{{ $work->title }}</a>
        {{-- <span class="text-sm font-semibold">{{ $work->title }}</span> --}}
        @if ($work->title_translation)
          <span class="text-libryo-gray-500 text-sm">[{{ $work->title_translation }}]</span>
        @endif

      </div>

      @if ($showFlag)
        <div class="">
          <x-corpus.work.my.work-flags :work="$work" show-multiple-flags />
        </div>
      @endif
    </div>

    @if (!$work->references->isEmpty())
      <div x-show="showingRerences">
        <div>
          @foreach ($work->references as $ref)
            <div class="group">
              <div class="pl-10 flex items-center">
                @include('bookmarks.bookmark-icon', ['bookmarks' => $ref->bookmarks, 'turboKey' => "reference-{$ref->id}", 'routePrefix' => 'my.reference.bookmarks', 'routePayload' => ['reference' => $ref->id]])

                <x-ui.icon name="arrow-turn-down-right" size="3" class="text-libryo-gray-400 mr-3" />
                {{-- remove if not reverted after launch --}}
                <a href="{{ route('my.corpus.references.show', ['reference' => $ref->id]) }}"
                   class="cursor-pointer text-primary hover:text-primary-darker text-xs">{{ $ref->refPlainText?->plain_text }}</a>
                {{-- <span class="text-xs text-libryo-gray-600">{{ $ref->refPlainText?->plain_text }}</span> --}}
              </div>


              @if (!empty($ref->_searchHighlights))
                <div class="mb-3">
                  @foreach ($ref->_searchHighlights as $highlight)
                    <div class="text-xs ml-16 ">
                      ... {!! $highlight !!} ...
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  @if ($work->relationLoaded('children') && !$work->children->isEmpty())
    @foreach ($work->children as $child)
      <div class="pl-10">
        <x-corpus.work.my.register-work :work="$child" :is-child="true" :show-flag="false" />
      </div>
    @endforeach
  @endif
</div>
