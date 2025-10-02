<turbo-frame id="doc-toc-item-{{ $doc->id }}-{{ !is_null($tocItem) ? $tocItem->id : 'root' }}">
  <li x-data="{
      handleItemClick: function(id) {
          if (id === '') return;
          window.setTimeout(function() { window.scrollToItemWhenAvailable('#' + id, '.norma-legislation', 'smooth'); }, 200);
      }
  }" class="relative py-1 pl-2 pr-1 list-none text-sm">
    @if (!is_null($tocItem))
      <div class="flex justify-between space-x-1">

        <div>
          @if ($tocItem->children_count)
            @if (!$showingChildren)
              <a href="{{ route('my.docs.toc', ['doc' => $doc->id, 'itemId' => $tocItem->id, 'show' => 1]) }}">
                <x-ui.icon name="plus-square" class="mr-1 text-norma-gray-400" />

              </a>
            @else
              <a href="{{ route('my.docs.toc', ['doc' => $doc->id, 'itemId' => $tocItem->id, 'show' => 0]) }}">
                <x-ui.icon name="minus-square" class="mr-1 text-norma-gray-400" />
              </a>
            @endif
          @endif
        </div>

        <div class="min-w-0 flex-1">
          @if ($resource)
            <a @click="handleItemClick('{{ $tocItem->uri_fragment }}')"
               href="{{ route('my.content-resources.show', ['resource' => $resource->id, 'targetId' => $doc->id]) }}"
               data-turbo-frame="content-full-text-{{ $doc->id }}">
              {{ $tocItem->label }}
            </a>
          @else
            @if ($tocItem->children_count)
              @if (!$showingChildren)
                <a href="{{ route('my.docs.toc', ['doc' => $doc->id, 'itemId' => $tocItem->id, 'show' => 1]) }}">
                  {{ $tocItem->label }}

                </a>
              @else
                <a href="{{ route('my.docs.toc', ['doc' => $doc->id, 'itemId' => $tocItem->id, 'show' => 0]) }}">
                  {{ $tocItem->label }}
                </a>
              @endif
            @else
              {{ $tocItem->label }}
            @endif

          @endif
        </div>
      </div>
    @endif

    @if (!empty($items) && $showingChildren)
      <ul role="list" class="divide-y divide-norma-gray-200 py-1">
        @foreach ($items as $i)
          @include('partials.corpus.toc-item.tree-item', [
              'doc' => $doc,
              'tocItem' => $i,
              'showingChildren' => false,
              'resource' => $i->contentResource,
              'items' => [],
          ])
        @endforeach
      </ul>
    @endif
  </li>
</turbo-frame>
