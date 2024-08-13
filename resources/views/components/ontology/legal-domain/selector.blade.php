<x-ontology.legal-domain-selector
  :name="$name ?? 'legal_domains'"
  :label="$label ?? __('ontology.legal_domain.index_title')"
  :value="$value ?? ''"
  :multiple="$multiple ?? false"
  :location-id="$locationId ?? null"
/>
