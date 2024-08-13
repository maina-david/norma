@php
  use App\Enums\Tasks\TaskStatus;
  use App\Services\Customer\ActiveLibryosManager;
  $canUpdate = $user->can('update', $task);
  $stack = collect(session('_ref_back', []));
  $previous = $stack->last();
  $previous = $previous ? url($previous) : route('my.tasks.tasks.index');
@endphp
<div class="mb-6">
  <div class="flex justify-between items-center">
    <div class="">
      <div class="text-lg font-semibold flex item-center">
        <x-tasks.task-type :type="$task->taskable_type" linkable :task="$task" :text="__('tasks.task.see_related')"/>
        <h3 class="ml-4">{!! $task->title !!}</h3>
      </div>
      <div>
        @include('partials.tasks.task.title-changer')
      </div>
    </div>

    <div>
      @if ($user->can('delete', $task))
        <div class="flex flex-row justify-between">
          <div class="mr-2">
            <x-ui.confirm-modal :route="route('my.tasks.tasks.destroy', ['task' => $task])" data-turbo-frame="_top">
              <x-ui.button theme="negative" styling="outline">{{ __('actions.delete') }}</x-ui.button>

              <x-slot:body>
                <input type="hidden" name="referer" value="{{ $previous }}">
              </x-slot:body>
            </x-ui.confirm-modal>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div class="mt-1 text-sm text-libryo-gray-900 font-normal wysiwyg-content">
    @include('partials.tasks.task.description-changer')
  </div>
</div>


<div class="shadow bg-white sm:rounded-lg px-4">
  <table class="w-full">
    @if($task->isCloned())
      <tr class="border-b">
        <x-ui.td>{{ __('tasks.note') }}</x-ui.td>
        <x-ui.td>
          {!! __('tasks.copied_task_details', ['route' => route('my.corpus.references.show', ['reference' => $task->taskable_id]), 'requirement' => $task->taskable->refTitleText->text ?? '']) !!}
        </x-ui.td>
      </tr>
    @endif
    @if(!app(ActiveLibryosManager::class)->isSingleMode() && $task->libryo)
      <tr class="border-b">
        <x-ui.td>{{ __('customer.libryo.libryo_stream') }}</x-ui.td>
        <x-ui.td>
          {{ $task->libryo->title }}
        </x-ui.td>
      </tr>
    @endif
    <tr class="border-b">
      <x-ui.td>{{ __('tasks.status') }}</x-ui.td>
      <x-ui.td>
        @include('partials.tasks.task.status-changer')
      </x-ui.td>
    </tr>
    <tr class="border-b">
      <x-ui.td>{{ __('timestamps.created_at') }}</x-ui.td>
      <x-ui.td>
        <x-ui.timestamp class="text-libryo-gray-500" :timestamp="$task->created_at"/>
      </x-ui.td>
    </tr>

    <tr class="border-b">
      <x-ui.td>{{ __('tasks.due_on') }}</x-ui.td>
      <x-ui.td>
        @include('partials.tasks.task.due-date-changer')
      </x-ui.td>
    </tr>

    <tr class="border-b">
      <x-ui.td>{{ __('tasks.project') }}</x-ui.td>
      <x-ui.td>
        @include('partials.tasks.task.project-changer')
      </x-ui.td>
    </tr>

    <tr class="border-b">
      <x-ui.td>{{ __('tasks.assigned_to') }}</x-ui.td>
      <x-ui.td>
        @include('partials.tasks.task.assignee-changer')
      </x-ui.td>
    </tr>

    <tr class="border-b">
      <x-ui.td>
        {{ __('tasks.followers') }}
      </x-ui.td>
      <x-ui.td>
        @include('partials.tasks.task.followers-changer')
      </x-ui.td>
    </tr>

    <tr class="border-b">
      <x-ui.td>{{ __('tasks.priority') }}</x-ui.td>
      <x-ui.td>
        <span class="text-libryo-gray-500">
          @include('partials.tasks.task.priority-changer')
        </span>
      </x-ui.td>
    </tr>

    @if (!$isActionsModule)
      <tr class="border-b">
        <x-ui.td>
          <span class="flex items-center">
            <span class="mr-2">{{ __('tasks.task_repeats') }}</span>
            <x-ui.icon class="tippy" data-tippy-content="{{ __('tasks.task_repeats_help') }}" name="question-circle"/>
          </span>
        </x-ui.td>
        <x-ui.td>
          <span class="text-libryo-gray-500">
            @include('partials.tasks.task.frequency-changer')
          </span>
        </x-ui.td>
      </tr>
    @endif

    @if($task->previous || $task->next)
      <tr class="border-b">
        <x-ui.td>{{ __('tasks.task_history') }}</x-ui.td>
        <x-ui.td>
          <div class="flex items-center space-x-4">
            @if($task->previous)
              <a target="_top" class="text-primary underline"
                 href="{{ route('my.tasks.tasks.show', ['task' => $task->previous->hash_id]) }}">
                {{ __('tasks.see_previous_version') }}
              </a>
            @endif

            @if($task->next)
              <a target="_top" class="text-primary underline"
                 href="{{ route('my.tasks.tasks.show', ['task' => $task->next->hash_id]) }}">
                {{ __('tasks.see_next_version') }}
              </a>
            @endif
          </div>
        </x-ui.td>
      </tr>
    @endif
  </table>
</div>


<div class="grid lg:grid-cols-12 lg:gap-4 bg-libryo-gray-50 border-t border-libryo-gray-100 p-5">
  {{-- LEFT SIDE --}}
  <div class="lg:col-span-7">
    <x-ui.tabs x-cloak>
      <x-slot name="nav">
        <x-ui.tab-nav name="files">
          <x-ui.icon name="folder-open" class="mr-2"/>
          {{ __('storage.file.files') }}
        </x-ui.tab-nav>
        <x-ui.tab-nav name="activities">
          <x-ui.icon name="analytics" class="mr-2"/>
          {{ __('tasks.activity') }}
        </x-ui.tab-nav>
      </x-slot>

      <x-ui.tab-content name="files">
        <div class="p-4">
          <turbo-frame loading="lazy"
                       src="{{ route('my.drives.files.for.task.index', ['task' => $task]) }}"
                       id="files-for-task-{{ $task->id }}">
            <x-ui.skeleton/>
          </turbo-frame>
        </div>
      </x-ui.tab-content>

      <x-ui.tab-content name="activities">
        <div class="p-4">
          <turbo-frame loading="lazy" src="{{ route('my.tasks.task-activities.index', ['task' => $task]) }}"
                       id="activities-for-task-{{ $task->id }}">
            <x-ui.skeleton/>
          </turbo-frame>
        </div>
      </x-ui.tab-content>

    </x-ui.tabs>
  </div>

  {{-- RIGHT SIDE --}}
  <div class="lg:col-span-5">
    <x-ui.tabs x-cloak>
      <x-slot name="nav">
        @ifOrgHasModule('comments')
        <x-ui.tab-nav name="comments">
          <x-ui.icon name="comments" class="mr-2"/>
          {{ __('comments.comments') }}
        </x-ui.tab-nav>
        @endifOrgHasModule

        <x-ui.tab-nav name="reminders">
          <x-ui.icon name="alarm-clock" class="mr-2"/>
          {{ __('reminders.reminders') }}
        </x-ui.tab-nav>

      </x-slot>


      @ifOrgHasModule('comments')
      <x-ui.tab-content name="comments">
        <div class="p-4">
          <turbo-frame src="{{ route('my.comments.for.commentable', ['type' => 'task', 'id' => $task->id]) }}"
                       loading="lazy" id="comments-for-task-{{ $task->id }}">
            <x-ui.skeleton/>
          </turbo-frame>
        </div>
      </x-ui.tab-content>
      @endifOrgHasModule

      <x-ui.tab-content name="reminders">
        <div class="p-4">
          <turbo-frame loading="lazy"
                       src="{{ route('my.notify.reminders.for.related.index', ['relation' => 'task', 'id' => $task->id]) }}"
                       id="reminders-for-task-{{ $task->id }}">
            <x-ui.skeleton/>
          </turbo-frame>
        </div>
      </x-ui.tab-content>
    </x-ui.tabs>

  </div>
</div>
