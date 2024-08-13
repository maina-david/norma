<x-ui.show-field :label="__('collaborators.group.title')" :value="$resource->title" />

<x-ui.show-field :label="__('collaborators.group.description')" :value="$resource->description" />

<x-ui.show-field :label="__('collaborators.group.created_at')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>
