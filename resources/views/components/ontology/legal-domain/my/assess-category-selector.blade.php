<div {{ $attributes }}>
  @foreach ($legalDomains as $domain)
    <x-ontology.legal-domain.my.assess-category-selector-item @selected="$dispatch($event)" :domain="$domain" />
  @endforeach
</div>
