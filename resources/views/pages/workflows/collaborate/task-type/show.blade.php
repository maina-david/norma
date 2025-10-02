@php
  use App\Enums\Workflows\TaskTypeOption;
  use App\Enums\Workflows\WorkStatusOption;
  use App\Enums\Workflows\AutoArchiveOption;
  use App\Enums\Workflows\WorkExpressionStatusOption;

  $archiveOptions = AutoArchiveOption::lang();
  $archiveNone = AutoArchiveOption::NONE->label();
  $typeOptions = TaskTypeOption::lang();
  $typeNone = TaskTypeOption::NONE->label();
  $workOptions = WorkStatusOption::lang();
  $workExpressionOptions = WorkExpressionStatusOption::lang();
  $workNone = WorkStatusOption::NONE->label();
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <x-ui.show-field :label="__('workflows.task_type.name')" :value="$resource->name" />

  <x-ui.show-field :label="__('workflows.task_type.course_link')">
    @if ($resource->course_link)
      <a class="text-primary" href="{{ $resource->course_link }}" target="_blank">{{ $resource->course_link }}</a>
    @endif
  </x-ui.show-field>

  <x-ui.show-field :label="__('workflows.task_type.colour')" >
    <span class="text-white px-4 py-1" style="background-color:{{ $resource->colour }}">{{ $resource->colour }}</span>
  </x-ui.show-field>

  <x-ui.show-field :label="__('workflows.task_type.rating_group')" :value="$resource->ratingGroup->name ?? null" />

  <x-ui.show-field :label="__('workflows.task_type.task_route_id')" :value="$resource->taskRoute->name ?? null" />

  <div class="md:col-span-2 font-semibold border-b border-norma-gray-200 mt-6">
    {{ __('workflows.task_type.validations') }}
  </div>

  <x-ui.show-field :label="__('workflows.task_type.legal_statements')" :value="$typeOptions[$resource->validations['status']['statements'] ?? ''] ?? $typeNone" />
  <x-ui.show-field :label="__('workflows.task_type.identified_citations')" :value="$typeOptions[$resource->validations['status']['identified_citations'] ?? ''] ?? $typeNone" />
  <x-ui.show-field :label="__('workflows.task_type.citations')" :value="$typeOptions[$resource->validations['status']['citations'] ?? ''] ?? $typeNone" />
  <x-ui.show-field :label="__('workflows.task_type.metadata')" :value="$typeOptions[$resource->validations['status']['metadata'] ?? ''] ?? $typeNone" />
  <x-ui.show-field :label="__('workflows.task_type.summaries')" :value="$typeOptions[$resource->validations['status']['summaries'] ?? ''] ?? $typeNone" />
  <x-ui.show-field :label="__('workflows.task_type.work_highlights')" :value="$typeOptions[$resource->validations['status']['work_highlights'] ?? ''] ?? $typeNone" />
  <x-ui.show-field :label="__('workflows.task_type.work_update_report')" :value="$typeOptions[$resource->validations['status']['work_update_report'] ?? ''] ?? $typeNone" />
  <x-ui.show-field :label="__('workflows.task_type.work_status')" :value="$workOptions[$resource->validations['status']['work'] ?? ''] ?? $workNone" />
  <x-ui.show-field :label="__('workflows.task_type.auto_archive')" :value="$archiveOptions[$resource->validations['on_todo']['auto_archive'] ?? ''] ?? $archiveNone" />
  <x-ui.show-field :label="__('workflows.task_type.work_expression_status')" :value="$workExpressionOptions[$resource->validations['status']['work_expression'] ?? ''] ?? $workNone" />

  <div class="mt-4">
    <x-ui.show-field type="checkbox" :label="__('workflows.task_type.is_parent')" :value="$resource->is_parent ?? null" />
  </div>

  <div class="mt-2">
    <x-ui.show-field type="checkbox" :label="__('workflows.task_type.assignee_can_close')" :value="$resource->assignee_can_close ?? null" />
  </div>

  <div class="mt-2">
    <x-ui.show-field type="checkbox" :label="__('workflows.task_type.has_checks')" :value="$resource->has_checks ?? false" />
  </div>

  <div class="mt-2">
    <x-ui.show-field type="checkbox" :label="__('workflows.task_type.complexity_set')" value="{{ TaskTypeOption::APPLIED === TaskTypeOption::tryFrom($resource->validations['status']['complexity'] ?? -1) }}" />
  </div>

</div>
