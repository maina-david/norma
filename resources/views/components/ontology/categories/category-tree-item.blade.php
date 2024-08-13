@props(['node', 'applied' => false, 'required' => false])

<div class="category-group" x-data="{ open: false, onCheck: function (el, val) { el.closest('.category-group').querySelectorAll('.category-item').forEach(function (e) { e.checked = val; }); } }">
  <div class="flex items-center">
    <div class="flex-grow">
      <x-ui.input
          class="category-item"
          type="checkbox"
          name="categories[]"
          id="category_{{ $node['id'] }}"
          no-label-id
          :checkbox-value="$node['id']"
          @change="onCheck($event.target, $event.target.checked)"
          :value="$applied"
      >
        <x-slot:label>
          @if($required)
            <span class="font-bold tippy" data-tippy-content="{{ __('compilation.context_question.please_complete_all_questions') }}">
              {{ $node['display_label'] }}*
            </span>
          @else
            {{ $node['display_label'] }}
          @endif
        </x-slot:label>
      </x-ui.input>
    </div>

    @if(!empty($node['children']))
      <a x-show="!open" class="text-libryo-gray-600 px-1" href="#" @click.prevent="open = true" style="display: none;">
        <x-ui.icon name="plus" size="4" />
      </a>
      <a x-show="open" class="text-libryo-gray-600 px-1" href="#" @click.prevent="open = false" style="display: none;">
        <x-ui.icon name="minus" size="4" />
      </a>
    @endif
  </div>

  <div x-show="open" class="space-y-2 pl-6 pt-2 category-children" style="display: none;">
    @foreach($node['children'] as $child)
      <x-ontology.categories.category-tree-item :node="$child" :key="$child['id']" />
    @endforeach
  </div>
</div>
