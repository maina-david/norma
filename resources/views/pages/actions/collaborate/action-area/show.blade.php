<div>
  <x-ui.show-field :label="__('actions.action_area.title')" :value="$resource->title" />

  <x-ui.show-field :label="__('actions.action_area.subject')" :value="$resource->subjectCategory->display_label" />

  <x-ui.show-field :label="__('actions.action_area.control')" :value="$resource->controlCategory->display_label" />

  <x-ui.show-field :label="__('interface.date_created')">
    <x-ui.timestamp :timestamp="$resource->created_at" />
  </x-ui.show-field>
</div>
