@props(['name' => 'tags[]', 'multiple' => false, 'route' => route('collaborate.tags.index.json')])

<x-ui.remote-select
  @change="$dispatch('changed', $el.value)"
  :name="$name"
  :multiple="$multiple"
  :route="$route"
  value=""
  data-removable
  :placeholder="__('nav.tags')"
  {{ $attributes }}
/>
