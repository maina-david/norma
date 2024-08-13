@if(isset($chainTasks))
  <div class="flex items-center flex-shrink-0 h-full" x-data="{reload: false}" x-effect="if (reload) { setTimeout(function () { window.location.reload(); }, 500) }">
    @if($task ?? false)
      <x-ui.modal
        margin="sm:mt-20"
        @closed="document.querySelector('#annotate_task_details').setAttribute('src', '{{ route('collaborate.tasks.show', $task->id) }}')"
      >
        <x-slot name="trigger">
          <button @click="open = true" class="flex">
            <x-ui.badge class="bg-black{{ $task->taskType ? ' rounded-r-none' : '' }}">
              <span class="px-1">#{{ $task->id }}</span>
            </x-ui.badge>
          </button>
        </x-slot>

        <div class="w-screen max-w-5xl text-base px-4 pt-4">
          <x-turbo::frame id="annotate_task_details" :src="route('collaborate.tasks.show', $task->id)" loading="lazy">
            <x-ui.skeleton/>
          </x-turbo::frame>
        </div>

      </x-ui.modal>
    @endif

    <x-ui.dropdown position="left">
      <x-slot name="trigger">
        <div class="flex items-center">
          <button class="flex items-center border border-libryo-gray-200 rounded-r-full">
            @if($task ?? false)
              @if($task->taskType ?? false)
                <x-workflows.task-type.task-type-badge text-size="text-sm" class="rounded-l-none" :type="$task->taskType"/>
              @endif
            @else
              <span class="text-sm mx-4">----</span>
            @endif

            <x-ui.icon name="angle-down" class="ml-2 mr-4" size="sm"/>
          </button>

        </div>
      </x-slot>

      <div>
        @php
          $payload = [];
          if (isset($expression)) {
              $payload['expression'] = $expression->id;
              $payload['work'] = $expression->work_id;
          }
          if (isset($legalUpdate)) {
              $payload['legal_update'] = $legalUpdate->id;
              $payload['work'] = '0';
          }
        @endphp

        @foreach($chainTasks as $chainTask)
          @php
            $route = isset($task->taskType->taskRoute->route_name)
              ? route($task->taskType->taskRoute->route_name, [...$payload, 'task' => $chainTask->id])
              : '#';

            if ($chainTask->taskType->taskRoute ?? false) {
                $route = $chainTask->taskType->taskRoute->generateRoute($chainTask, $route);
            }
          @endphp
          <a target="_top" class="px-4 py-2 hover:bg-libryo-gray-100 flex items-center justify-between space-x-2" href="{{ $route  }}">
            <x-ui.badge small class="bg-black">#{{ $chainTask->id }}</x-ui.badge>

            <span class="pl-2 flex items-center flex-grow">
                <x-workflows.task-type.task-type-badge :type="$chainTask->taskType"/>
              </span>

            <x-workflows.tasks.task-status-badge :status="$chainTask->task_status"/>
          </a>
        @endforeach
      </div>
    </x-ui.dropdown>
  </div>
@endif
