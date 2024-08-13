<x-ui.form :action="route('my.tasks.tasks.update', ['task' => $task->id])" method="put">
  @include('partials.tasks.task.form', ['isCreateForm' => false])
</x-ui.form>
