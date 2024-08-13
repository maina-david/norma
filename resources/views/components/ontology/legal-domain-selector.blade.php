<x-ui.input
  {{ $attributes->merge([ 'placeholder' => __('nav.legal_domains')]) }}
  type="select"
  :value="$value"
  :name="$name"
  :label="$label"
  :options="$legalDomains"
  :multiple="$multiple"
  @change="$dispatch('changed', $el.value)"
/>
