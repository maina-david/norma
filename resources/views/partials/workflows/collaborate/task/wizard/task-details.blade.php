@php
  use App\Models\Workflows\Task;
  $board->load(['taskTypes']);
  $types = $board->taskTypes->keyBy('id');
  $taskOrder = explode(',', $board->task_type_order);
@endphp

<x-ui.tabs>
  <x-slot:nav>
    @foreach($taskOrder as $type)
      <x-ui.tab-nav name="type_{{ $type }}">
        {{ $types->get($type)->name }}
      </x-ui.tab-nav>
    @endforeach
  </x-slot:nav>

  @foreach($taskOrder as $type)
    @php
      $defaults = $board->task_type_defaults[$type] ?? [];
      $defaults['title'] = sprintf('%s - %s', $types->get($type)->name, $taskTitle);
      $defaults = new Task($defaults)
    @endphp

    <x-ui.tab-content name="type_{{ $type }}">
      @include('partials.workflows.collaborate.task.defaults', [
        'prefix' => "type_{$type}_",
        'creating' => true,
        'defaults' => $defaults
      ])
    </x-ui.tab-content>
  @endforeach
</x-ui.tabs>


