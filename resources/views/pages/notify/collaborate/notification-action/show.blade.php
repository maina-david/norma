<x-ui.show-field :label="__('corpus.reference.text')" :value="$resource->text" />

<x-ui.show-field :label="__('collaborators.group.created_at')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>
