<div>
  <x-ontology.legal-domain-selector
    annotations
    multiple
    name="legal_domains[]"
    :label="__('nav.legal_domains')"
    :required="!($stepMeta['optional'] ?? false)"
  />
</div>
