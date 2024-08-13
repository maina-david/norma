<div>
  <x-ui.show-field :label="__('assess.assessment_item.description')" :value="$resource->description" />

  <x-ui.show-field :label="__('collaborators.group.created_at')">
    <x-ui.timestamp :timestamp="$resource->created_at" />
  </x-ui.show-field>
</div>
