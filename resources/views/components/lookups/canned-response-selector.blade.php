<div>
  <x-ui.input
    {{ $attributes }}
    type="select"
    :name="$name"
    :value="$value"
    :label="$label ?? __('lookups.canned_response.populate_with_canned_response')"
    :options="$options"
  />
</div>
