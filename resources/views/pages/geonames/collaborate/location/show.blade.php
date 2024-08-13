<x-ui.show-field :label="__('geonames.location.title')" :value="$resource->title" />
<x-ui.show-field :label="__('geonames.location.country')" :value="$resource->country->title ?? '-'" />
<x-ui.show-field :label="__('geonames.location.slug')" :value="$resource->slug" />
<x-ui.show-field :label="__('geonames.location.type')" :value="$resource->type->title ?? '-'" />
<x-ui.show-field :label="__('geonames.location.level')" :value="$resource->level" />
@if($resource->parent)
  <x-ui.show-field :label="__('ontology.legal_domain.parent')">
    <a class="text-primary" href="{{ route('collaborate.jurisdictions.show', $resource->parent->id) }}">
      {{ $resource->parent->title }}
    </a>
  </x-ui.show-field>
@endif
<x-ui.show-field :label="__('geonames.location.flag')">
  <x-ui.country-flag class="h-6 w-6 rounded-full" :countryCode="$resource->flag" />
</x-ui.show-field>
<x-ui.show-field :label="__('geonames.location.location_code')" :value="$resource->location_code" />
<x-ui.show-field :label="__('geonames.location.currency')" :value="$resource->currency" />
@if ($resource->children_count)
<a href="{{ route('collaborate.jurisdictions.children', $resource->id) }}" class="inline-block mt-2 font-semibold text-primary">
  {{ __("interface.view") }} {{ $resource->children_count }} {{ __('geonames.location.children') }}
</a>
@endif