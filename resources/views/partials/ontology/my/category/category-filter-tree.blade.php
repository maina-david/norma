@php
  use App\Cache\Ontology\Collaborate\CategorySelectorCache;
//
//  $categoryOnChange = function($type, $current = []) {
//      $type = (string) $type;
//      $remove = array_values(array_filter($current, fn ($item) => $item !== $type));
//      $add = [...$current, $type];
//
//      return sprintf('$dispatch(\'changed\', $event.target.checked ? %s : %s)', json_encode($add), json_encode($remove));
//  };
//
//  $allChecked = collect($options)->keys()->reduce(fn ($a, $b) => $a && in_array($b, $value), true);
$options = collect($options);
$flatOptions = $options->reduce(fn ($a, $b) => array_merge($a, $b['children_ids'], [$b['id']]), []);
$industry = $options->where('id', 111000)->first();
@endphp

<div
  class="border-t border-libryo-gray-200 pt-4 category-tree"
  x-init="prepareTree"
  x-data="{
    openItems: {},
    prepareTree: function () {
      this.getOpenState();
      this.toggleParentStatuses();
    },
    toggleParentStatuses() {
      var parents = Array.from(document.querySelectorAll('.category-item[checked]')).map(function (item) {
        return item.getAttribute('data-parent-id');
      });

      Array.from(new Set(parents)).forEach(function (parent) {
        document.querySelectorAll('.category-item[data-tree-id=' + parent +']:not([checked])')
          .forEach(function (unchecked) {
            unchecked.indeterminate = true;
          });
      });
    },
    toggleChildren: function (event) {
      var ids = [];
      Array.from(event.target.closest('.category-tree').querySelectorAll('.category-item'))
        .forEach(function (item) {
          item.checked = event.target.checked;
          ids.push(item.value);
        });

        $dispatch('changed', event.target.checked ? ids : []);
    },
    getOpenState() {
      var stored = window.localStorage.getItem('app-tree-state');
      try {
        this.openItems = JSON.parse(stored ?? '{}');
      } catch (ex) {
        this.openItems = {};
      }
    },
    saveOpenState() {
      window.localStorage.setItem('app-tree-state', JSON.stringify(this.openItems));
    },
  }"
>
  <div class="mb-3">
    <x-ui.input
        class="category-item"
        type="checkbox"
        name="categories[]"
        label=""
        @change="toggleChildren($event)"
        :value="count($flatOptions) === count($value)"
        data-tree-id="tree-root"
        id="all"
    >
      <x-slot:label>
        <span class="font-bold">{{ __('assess.categories') }}</span>
      </x-slot:label>
    </x-ui.input>
  </div>


  <div class="space-y-2 category-children">
    @if($industry)
      @include('partials.ontology.my.category.category-filter-tree-item', [
        'category' => $industry,
        'parent' => 'root',
        'required' => true,
      ])
    @endif

    @foreach($options as $availableCategory)
      @if($availableCategory['id'] !== ($industry['id'] ?? 0))
        @include('partials.ontology.my.category.category-filter-tree-item', ['category' => $availableCategory, 'parent' => 'root'])
      @endif
    @endforeach
  </div>

</div>

