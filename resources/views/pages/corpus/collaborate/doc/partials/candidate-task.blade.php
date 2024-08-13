  <div class="flex items-center flex-shrink-0 h-full">
    @if(($task ?? false) && $task->id != '0')
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
        <div>
          <button class="flex items-center border border-libryo-gray-200 rounded-r-full">
            @if($task ?? false)
              @if($task->taskType ?? false)
                <x-workflows.task-type.task-type-badge text-size="text-sm" class="rounded-l-none" :type="$task->taskType"/>
              @endif
            @else
              <span class="text-sm mx-4">----</span>
            @endif
          </button>

        </div>
      </x-slot>
    </x-ui.dropdown>
  </div>

