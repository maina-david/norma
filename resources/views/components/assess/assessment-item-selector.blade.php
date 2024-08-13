@props(['name' => 'tags[]', 'value' => null, 'options' => [], 'multiple' => false, 'route' => route('collaborate.assessment-items.index.json'), 'placeholder' => __('nav.assessment_items')])

<x-ui.remote-select
  @change="$dispatch('changed', $el.value)"
  :name="$name"
  :options="$options"
  :multiple="$multiple"
  :route="$route"
  :value="$value"
  data-removable
  :data-load-start-searching="0"
  :placeholder="$placeholder"
  {{ $attributes }}
/>
