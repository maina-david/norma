<div class="flex justify-end">
  <div class="max-w-screen-90 w-96" x-data="{}">
    <form x-ref="taskTypeForm">
      <input type="hidden" name="tab" value="activities">
      <x-ui.input
        type="select"
        :options="['' => ''] + $taskTypes"
        name="task_type"
        :value="request('task_type')"
        :placeholder="__('tasks.task_type')"
        @change="$refs.taskTypeForm.requestSubmit()"
      />
    </form>
  </div>
</div>
<div class="border-b border-libryo-gray-200 my-6"></div>

@foreach($tasks as $task)
  <div class="flex items-center">
    <div class="flex-grow">
      <div>
        <a href="{{ route('collaborate.tasks.show', $task->id) }}" class="flex text-primary" target="_blank">
          <span class="font-semibold flex items-start">
            <span class="mr-1">{{ $task->title }}</span>
            <x-ui.icon name="external-link" size="3" />
          </span>
        </a>
      </div>

      <div class="flex flex-wrap items-center -mt-2">
        @foreach($task->ratings as $rating)
          @if($rating->type)
            <div class="flex items-center space-x-1 mr-8 mt-2 tippy" data-tippy-content="{{ $rating->type->name }}">
              <span class="">{{ number_format($rating->score, 2) }}</span>
              <x-ui.icon class="" :name="$rating->type->icon" size="4" />
            </div>
          @endif
        @endforeach
      </div>
    </div>

    <div class="flex-shrink-0 font-semibold text-sm">
      {{ $task->completed_at?->format('dS M Y') }}
    </div>
  </div>
  <div class="border-b border-libryo-gray-200 my-6"></div>
@endforeach

<div>
  {{ $tasks->withQueryString()->appends('tab', 'activities')->links() }}
</div>
