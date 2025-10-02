<turbo-frame id="doc-toc-item-{{ $doc->id }}-{{ !is_null($tocItem) ? $tocItem->id : 'root' }}">
  <li x-data="{
      hoveringOverItem: false,
      handleItemClick: function(id) {
          if (id === '') return;
          window.setTimeout(function() { window.scrollToItemWhenAvailable('#' + id, '.norma-legislation', 'smooth'); }, 200);
      }
  }" class="relative py-1 pl-2 pr-1 list-none text-sm">
    @if (!is_null($tocItem))
      <div @mouseover="hoveringOverItem = true" @mouseleave="hoveringOverItem = false"
           class="flex justify-between space-x-1">

        <div>
          @if ($tocItem->children_count)
            @if (!$showingChildren)
              <a
                 href="{{ route('collaborate.docs.identify-toc', ['expression' => $expression, 'doc' => $doc->id, 'itemId' => $tocItem->id, 'show' => 1]) }}">
                <x-ui.icon name="plus-square" class="mr-1 text-norma-gray-400" />

              </a>
            @else
              <a
                 href="{{ route('collaborate.docs.identify-toc', ['expression' => $expression, 'doc' => $doc->id, 'itemId' => $tocItem->id, 'show' => 0]) }}">
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
                <a
                   href="{{ route('collaborate.docs.identify-toc', ['expression' => $expression, 'doc' => $doc->id, 'itemId' => $tocItem->id, 'show' => 1]) }}">
                  {{ $tocItem->label }}

                </a>
              @else
                <a
                   href="{{ route('collaborate.docs.identify-toc', ['expression' => $expression, 'doc' => $doc->id, 'itemId' => $tocItem->id, 'show' => 0]) }}">
                  {{ $tocItem->label }}
                </a>
              @endif
            @else
              {{ $tocItem->label }}
            @endif
          @endif

        </div>
        <div>
          @if(!($tocItem->has_reference ?? false))
            <x-ui.form
                method="POST"
                action="{{ route('collaborate.work-expressions.identify.references.insert-from-toc', ['tocItem' => $tocItem->id, 'expression' => $expression->id]) }}"
            >
              <input type="hidden" name="redirect_back" x-bind:value="refPage ? '{{ route('collaborate.work-expressions.identify.references.index', ['expression' => $expression->id]) }}?page=' + refPage : null">

              <div
                  @click="$el.closest('form').requestSubmit()"
                  class="cursor-pointer tippy w-6 text-norma-gray-400 hover:text-norma-gray-800"
                  data-tippy-delay="300"
                  data-tippy-content="{{ __('corpus.reference.insert_from_toc_item_tooltip') }}"
              >
                <x-ui.icon x-show="hoveringOverItem" name="rectangle-history-circle-plus" size="4" />
              </div>
            </x-ui.form>
          @endif
          <x-ui.form
            method="POST"
            action="{{ route('collaborate.work-expressions.identify.references.update-from-toc', ['tocItem' => $tocItem->id, 'expression' => $expression->id]) }}"
          >
            <input type="hidden" name="redirect_back" x-bind:value="refPage ? '{{ route('collaborate.work-expressions.identify.references.index', ['expression' => $expression->id]) }}?page=' + refPage : null">

            <div
              @click="$el.closest('form').requestSubmit()"
              class="cursor-pointer tippy w-6 text-norma-gray-400 hover:text-norma-gray-800 mt-0.5"
              data-tippy-delay="300"
              data-tippy-content="{{ __('corpus.reference.update_from_toc_item_tooltip') }}"
            >
              <x-ui.icon x-show="hoveringOverItem" name="recycle" size="4" />
            </div>
          </x-ui.form>

        </div>
      </div>
    @endif

    @if (!empty($items) && $showingChildren)
      <ul role="list" class="divide-y divide-norma-gray-200 py-1">
        @foreach ($items as $i)
          @include('partials.corpus.collaborate.toc-item.identify-tree-item', [
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
