<x-ontology.legal-domain.collaborate.layout :domain="$resource"  class="max-w-7xl w-screen">
<div>
  <x-ui.show-field :label="__('ontology.legal_domain.title')" :value="$resource->title" />

  <x-ui.show-field :label="__('ontology.legal_domain.parent')" :value="$resource->parent->title ?? '-'" />

  <x-ui.show-field :label="__('ontology.legal_domain.top_parent')"
                   :value="$resource->top_parent_id !== $resource->id ? ($resource->topParent->title ?? '-') : '-'" />

  <x-ui.show-field :label="__('collaborators.group.created_at')">
    <x-ui.timestamp :timestamp="$resource->created_at" />
  </x-ui.show-field>
</div>
</x-ontology.legal-domain.collaborate.layout>
