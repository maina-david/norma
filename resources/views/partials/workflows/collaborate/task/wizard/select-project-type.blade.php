@php use App\Enums\Workflows\ProjectType; @endphp
@php
$options = ProjectType::forSelector();
@endphp

<div>
  <x-ui.input
    :label="__('workflows.task.project_type.title')"
    name="project_type"
    :options="$options"
    type="select"
    required
  />
</div>
