@php
  use App\Enums\Compilation\ContextQuestionAnswer;

  $options = ContextQuestionAnswer::lang();

  $generateOnChange = function($type, $current = []) {
      $type = (string) $type;
      $remove = array_values(array_filter($current, fn ($item) => $item !== $type));
      $add = [...$current, $type];

      return sprintf('$dispatch(\'changed\', $event.target.checked ? %s : %s)', json_encode($add), json_encode($remove));
  };

  $allChecked = collect($options)->keys()->reduce(fn ($a, $b) => $a && in_array($b, $value), true);
@endphp
<div
  class="border-t border-norma-gray-200 pt-4 check-group"
  x-data="{
    toggleChildren: function (event) {
      var ids = [];
      Array.from(event.target.closest('.check-group').querySelectorAll('.check-item'))
        .forEach(function (item) {
          item.checked = event.target.checked;
          ids.push(item.value);
        });

        $dispatch('changed', event.target.checked ? ids : []);
    },

    handleOnChange: function(event) {
      var existing = Array.from($refs['filter-inputs-answer'].options).map(function (option) {
        return option.value;
      });

      if (event.target.checked) {
        existing.push(event.target.value);
        $dispatch('changed', existing);
        return;
      }

      existing = existing.filter(function (item) {
          return item != event.target.value;
      });

      $dispatch('changed', existing);
    }
  }"
>
  <div class="mb-3">
    <x-ui.input
      type="checkbox"
      name="answer[]"
      label=""
      @change="toggleChildren($event)"
      :value="$allChecked"
      id="all"
    >
      <x-slot:label>
        <span class="font-bold">{{ __('assess.assessment_item_response.answers') }}</span>
      </x-slot:label>
    </x-ui.input>
  </div>


  <div class="space-y-2">
    @foreach($options as $key => $label)
      <x-ui.input
          class="check-item"
          type="checkbox"
          name="answer[]"
          label="{{ $label }}"
          @change="handleOnChange($event)"
          checkbox-value="{{ $key }}"
          value="{{ in_array($key, $value) }}"
          id="{{ Str::slug($label, '_') }}"
      />
    @endforeach
  </div>

</div>

