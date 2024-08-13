<x-ui.input
  type="select"
  :label="$label"
  :name="$name"
  :options="$roles"
  :value="old($name, $value)"
  {{ $attributes }}
  @change="$dispatch('changed', $el.value)"
/>
