<x-ui.show-field :label="__('ontology.tag.title')" :value="$resource->title" />

<x-ui.show-field :label="__('arachno.source.created_at')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>
