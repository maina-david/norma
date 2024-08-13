@php
  use App\Enums\Tasks\TaskStatus;
@endphp

@if($canUpdate)
  <div class="flex items-center">
    @if (!$task->watchers->isEmpty())
      @foreach ($task->watchers as $watcher)
        <div class="flex items-center relative space-x-2 mr-4 mt-1">
          <x-ui.user-avatar :user="$watcher" class="inline-block flex-shrink-0"/>
          <span class="ml-3 text-libryo-gray-500">{{ $watcher->full_name }}</span>
        </div>
      @endforeach
    @endif

    <x-ui.dropdown>
      <x-slot:trigger>
        <button class="flex items-center justify-center rounded-full bg-libryo-gray-200 h-8 w-8">
          <x-ui.icon name="plus" class="text-white pl-[0.06rem]" />
        </button>
      </x-slot:trigger>

      <div class="px-4 py-2 w-screen-75 max-w-xs">
        <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">
          <x-auth.user.my.user-selector
              :value="old('followers', isset($task) ? ($task->watchers?->pluck('id')->toArray() ?? []) : [])"
              name="followers[]"
              multiple
              data-with-remove
              :label="__('tasks.followers')"
          />

          <div class="mt-4">
            <x-ui.button theme="primary" type="submit">
              {{ __('actions.save') }}
            </x-ui.button>
          </div>
        </x-ui.form>
      </div>
    </x-ui.dropdown>
  </div>

@else
  @if ($task->assignee)
    <div class="flex flex-row items-center">
      <x-ui.user-avatar :user="$task->assignee"/>
      <div class="ml-3 text-libryo-gray-500">{{ $task->assignee->fullName }}</div>
    </div>
  @else
    <div>-</div>
  @endif
@endif
