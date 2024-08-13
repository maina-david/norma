<section>
  <x-ui.table :rows="$tasks" no-search>

    <x-slot:body>
      @foreach($tasks as $task)
        <x-ui.tr :loop="$loop">
          <x-ui.td>
            <x-ui.modal class="-mt-2" margin="sm:mt-20" @closed="document.querySelector('#annotate_task_details').setAttribute('src', '{{ route('collaborate.tasks.show', $task->id) }}')">
              <x-slot name="trigger">
                <div @click="open = true" class="text-primary cursor-pointer pt-4">
                  <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-1">
                      <span class="inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium bg-dark text-white whitespace-nowrap">#{{ $task->id }}</span>
                      <x-workflows.tasks.task-priority-badge :priority="$task->priority" />
                      @if($task->due_date && !\App\Enums\Workflows\TaskStatus::done()->is($task->task_status) && !\App\Enums\Workflows\TaskStatus::archive()->is($task->task_status))
                        <span class="flex">
                          <span class="{{ now()->gt($task->due_date) ? 'bg-secondary' : 'bg-primary' }} inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium text-white whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($task->due_date)->diffForHumans(now(), \Carbon\Carbon::DIFF_RELATIVE_TO_NOW) }}
                          </span>
                        </span>
                      @endif
                    </div>

                    <div>
                      <x-workflows.tasks.task-status-badge :status="$task->task_status" />
                    </div>
                  </div>

                  <div class="mt-3 leading-4">
                    {{ $task->title }}
                  </div>
                </div>
              </x-slot>

              <div class="w-screen max-w-5xl text-base px-4 pt-4">
                <x-turbo::frame id="ask_details_{{ $task->id }}" :src="route('collaborate.tasks.show', $task->id)" loading="lazy">
                  <x-ui.skeleton />
                </x-turbo::frame>
              </div>

            </x-ui.modal>
          </x-ui.td>
        </x-ui.tr>
      @endforeach
    </x-slot:body>

  </x-ui.table>
</section>
