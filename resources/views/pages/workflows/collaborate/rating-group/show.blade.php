<x-ui.show-field :label="__('workflows.rating_group.name')" :value="$resource->name" />

<x-ui.show-field :label="__('workflows.rating_group.description')" :value="$resource->description" />

<x-ui.show-field :label="__('workflows.rating_type.created_at')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>
