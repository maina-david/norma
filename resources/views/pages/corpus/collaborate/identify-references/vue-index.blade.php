@php
  use App\Enums\Workflows\TaskStatus;
  $permissions = ($preview ?? false) ? [] : array_values(auth()->user()->permissions);
  $canOverride = auth()->user()->can('collaborate.corpus.work-expression.identify-without-task');
  $inProgress = TaskStatus::inProgress()->is($task->task_status ?? -1);

  if (!$inProgress && !$canOverride) {
    $permissions = [];
  }
@endphp

<x-layouts.collaborate fluid no-padding>
  <x-slot name="pageHead">
    <script>
      window.magi = {!! json_encode(config('services.norma_ai.enabled')) !!};
      window.pageMeta = {!! json_encode($pageMeta) !!};
      window.activateReference = {!! json_encode(request()->query('activate')) !!};
      window.hasNotes = {!! json_encode($hasNotes) !!};
      window.plainTextSource = {!! $plainTextSource ? "'{$plainTextSource}'" : 'null' !!};
      window.appliedFilters = {!! json_encode($filters) !!};
      window.consumer = {{ auth()->user()->id }};
      window.permissions = {!! json_encode($permissions) !!};
      window.workReference = {!! json_encode($workReference) !!};
      window.expression = {!! json_encode($expression->only(['id', 'work_id'])) !!};
      window.work = {!! json_encode($expression->work->only(['id', 'title', 'primary_location_id'])) !!};
      window.task = {!! json_encode(['id' => $task->id ?? null, 'is_assignee' => Auth::id() == ($task->user_id ?? null)]) !!};
    </script>
    @vite(['resources/js/vue/pages/identify/index.js'])
  </x-slot>

  <x-slot name="header">
    <x-turbo::frame
      target="_top"
      id="workflow-task-header"
      :src="route('collaborate.workflow.tasks.header', ['expression' => $expression->id])"
    />
  </x-slot>

  <div class="h-full w-full" id="app"></div>

</x-layouts.collaborate>
