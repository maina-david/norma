@php
  $isChecked = in_array($category['id'], $value);
@endphp
<div
    class="category-group"
    x-data="{
      open: false,
      initTree: function () {
        this.initOpenItems();
      },
      initOpenItems: function() {
        var that = this;
        setTimeout(function () {
          that.open = (that.openItems || {})[{{ $category['id'] }}] || false;
        }, 100);
      },
      updateCheckStatus(nodes, checked, indeterminate) {
        nodes.forEach(function (node) {
          node.checked = checked;
          node.indeterminate = indeterminate;
        });
      },
      changeParentStatus: function (event, checked) {
        var root = event.target.closest('.category-tree');
        var parents = document.querySelectorAll('.category-item[data-tree-id=' + event.target.getAttribute('data-parent-id') + ']');
        var parentId = parents[0].getAttribute('data-id');
        var siblings = Array.from(root.querySelectorAll('.category-item[data-parent-id=' + event.target.getAttribute('data-parent-id') + ']'));
        var allChecked = !siblings.some(function (option) {
          return !option.checked;
        });

        if (allChecked) {
          this.updateCheckStatus(parents, true, false);

          if (parentId) {
            $dispatch('changed', checked.concat([parentId]));
          }

          return;
        }

        if (parentId) {
          $dispatch('changed', this.removeFromCollection(checked, [parentId]));
        }

        var someChecked = siblings.some(function (option) {
          return option.checked;
        });

        this.updateCheckStatus(parents, false, someChecked);
      },
      removeFromCollection: function (collection, toRemove) {
        return collection.filter(function (item) {
            return !toRemove.includes(item);
        });
      },
      handleChange: function(event) {
        var ids = [];
        Array.from(event.target.closest('.category-group').querySelectorAll('.category-item'))
          .forEach(function (item) {
            item.checked = event.target.checked;
            ids.push(item.value);
          });

        var existing = Array.from($refs['filter-inputs-categories'].options).map(function (option) {
          return option.value;
        });

        if (event.target.checked) {
          existing = existing.concat(ids);
          $dispatch('changed', existing);
          this.changeParentStatus(event, existing);

          return;
        }

        existing = this.removeFromCollection(existing, ids);

        $dispatch('changed', existing);
        this.changeParentStatus(event, existing);
      },
      toggleOpen: function () {
        this.open = !this.open;
        this.openItems[{{ $category['id'] }}] = this.open;
        this.saveOpenState();
      },
    }"
    x-init="initTree"
>

  <div class="flex items-center">
    <div class="flex-grow">
      <x-ui.input
        class="category-item"
        type="checkbox"
        name="categories[]"
        no-label-id
        checkbox-value="{{ $category['id'] }}"
        id="{{ Str::slug($category['display_label'], '_') }}"
        data-parent-id="tree-{{ $parent }}"
        data-tree-id="tree-{{ $category['id'] }}"
        data-id="{{ $category['id'] }}"
        @change="handleChange($event)"
        :value="$isChecked"
      >
        <x-slot:label>
          @if($required ?? false)
            <span class="font-bold tippy" data-tippy-content="{{ __('compilation.context_question.please_complete_all_questions') }}">{{ $category['display_label'] }}*</span>
          @else
            {{ $category['display_label'] }}
          @endif
        </x-slot:label>
      </x-ui.input>
    </div>

    @if(!empty($category['children']))
      <a x-show="!open" class="text-norma-gray-600 px-1" href="#" @click.prevent="toggleOpen" style="display: none;">
        <x-ui.icon name="plus" size="4" />
      </a>
      <a x-show="open" class="text-norma-gray-600 px-1" href="#" @click.prevent="toggleOpen" style="display: none;">
        <x-ui.icon name="minus" size="4" />
      </a>
    @endif
  </div>

  <div x-show="open" class="space-y-2 pl-6 pt-2 category-children" style="display: none;">
    @foreach($category['children'] as $child)
      @include('partials.ontology.my.category.category-filter-tree-item', ['category' => $child, 'parent' => $category['id']])
    @endforeach
  </div>

</div>
