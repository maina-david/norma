
@isset($task)
  <div class="-mx-4 pt-2">
    <x-turbo::frame target="_top" id="workflow-task-header" :src="route('collaborate.workflow.tasks.header', ['expression' => $task->document->work_expression_id])" />
  </div>
@endisset

