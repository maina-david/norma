<x-ontology.legal-domain-selector
  @change="$dispatch('changed', {{ $multiple ? 'Array.from($el.selectedOptions).map(function (item) { return item.value; })' : '$el.value' }})"
  :name="$name"
  :value="$value"
  :multiple="$multiple"
  :label="$label"
/>
