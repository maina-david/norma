@php
use App\Models\Workflows\Task;
$siblings = Task::siblings(auth()->user(), null, $resource);
@endphp

@if($siblings->isNotEmpty())
<div class="divide-y divide-libryo-gray-200 mt-6" data-turbo="false">
    @foreach($siblings as $sibling)
      <div class="mb-2 py-2">
        <div class="flex justify-between items-center">
          <div>
            <span class="inline-flex rounded-l-full items-center py-0.5 px-2 text-xs font-medium bg-dark text-white whitespace-nowrap">#{{ $sibling->id }}</span>
            <span class="inline-flex rounded-r-full items-center py-0.5 px-2 text-xs font-medium text-white whitespace-nowrap -ml-1.5" style="background-color:{{ $sibling->taskType->colour }};">{{ $sibling->taskType->name }}</span>
          </div>

          <div>
            <x-workflows.tasks.task-status-badge :status="$sibling->task_status" />
          </div>
        </div>

        <div class="mt-3 leading-4">
          <a class="text-primary font-medium" href="{{ route('collaborate.tasks.show', $sibling->id) }}" target="_top">
            {{ $sibling->title }}
          </a>
        </div>
      </div>

    @endforeach
  </div>
@endif
