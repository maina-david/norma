<x-ui.form :action="route('my.tasks.tasks.store')" method="post">
  <input type="hidden" name="norma_id" value="{{ $norma->id }}" />
  @include('partials.tasks.task.form', [ 'isCreateForm'=> true,
  'taskableType' => $taskableType,
  'taskableId' => $taskableId])
</x-ui.form>
