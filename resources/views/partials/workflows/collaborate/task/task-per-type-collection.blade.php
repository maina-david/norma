@foreach($tasks as $task)
  @include('partials.workflows.collaborate.task.task-summary-card', ['task' => $task])
@endforeach

