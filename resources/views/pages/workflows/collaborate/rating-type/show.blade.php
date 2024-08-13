<x-ui.show-field :label="__('workflows.rating_type.name')" :value="$resource->name" />

<x-ui.show-field :label="__('workflows.rating_type.description')" :value="$resource->description" />

<x-ui.show-field :label="__('workflows.rating_type.icon')" :value="$resource->icon" />

<x-ui.show-field :label="__('workflows.rating_type.course_link')">
  @if ($resource->course_link)
    <a class="text-primary" href="{{ $resource->course_link }}" target="_blank">{{ $resource->course_link }}</a>
  @endif
</x-ui.show-field>

<x-ui.show-field :label="__('workflows.rating_type.created_at')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>
