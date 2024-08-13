<x-ui.input
  {{ $attributes }}
  type="select"
  :value="$value"
  :name="$name"
  :label="$label"
  :options="$categories"
  :placeholder="$placeholder ?? __('nav.topics')"
/>
