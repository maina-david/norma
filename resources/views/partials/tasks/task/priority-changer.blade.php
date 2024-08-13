@php
  use App\Enums\Tasks\TaskPriority;
  $priorities = TaskPriority::forSelector();
  $onCLick = $canUpdate ? '$event.target.closest("form").requestSubmit()' : '';
@endphp

<x-ui.form method="{{ $canUpdate ? 'PUT' : 'GET' }}" action="{{ $canUpdate ? route('my.tasks.tasks.update', ['task' => $task->id]) : '#' }}">
  <div class="flex items-center flex-wrap" x-data="{}">
    @foreach($priorities as $item)
      <div class="flex items-center space-x-2 md:mr-6 mt-1">
        <input
            @click="{{ $onCLick }}"
            class="libryo-radio text-primary focus:ring-primary cursor-pointer disabled:text-libryo-gray-500 disabled:cursor-default"
            type="radio"
            name="{{ $canUpdate ? "priority" : '' }}"
            id="priority_{{ $task->id }}_{{ $item['value'] }}"
            value="{{ $canUpdate ? $item['value'] : '' }}"
            {{ $task->priority === $item['value'] ? 'checked' : '' }}
            {{ $canUpdate ? '' : 'disabled' }}
        />

        <x-ui.label class="{{ $canUpdate ? 'cursor-pointer' : 'cursor-default' }}" for="priority_{{ $task->id }}_{{ $item['value'] }}">{{ $item['label'] }}</x-ui.label>
      </div>
    @endforeach
  </div>
</x-ui.form>
