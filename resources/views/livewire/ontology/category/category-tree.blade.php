<div>
  <div class="category-tree pb-4">
    <div class="mb-3">
      <x-ui.input
          class="category-item"
          type="checkbox"
          label=""
          name=""
          @change="$event.target.closest('.category-tree').querySelectorAll('.category-children .category-item').forEach(function (el) { el.checked = $event.target.checked; })"
          id="all-categories"
      >
        <x-slot:label>
          <span class="font-bold">{{ __('assess.categories') }}</span>
        </x-slot:label>
      </x-ui.input>
    </div>

    <div class="space-y-2 category-children">
      @if($industry)
        <x-ontology.categories.category-tree-item required :node="$industry" :key="$industry['id']" :applied="in_array($industry['id'], $applied)" />
      @endif

      @foreach($nodes as $node)
        <x-ontology.categories.category-tree-item :node="$node" :key="$node['id']" :applied="in_array($node['id'], $applied)" />
      @endforeach
    </div>
  </div>

</div>
