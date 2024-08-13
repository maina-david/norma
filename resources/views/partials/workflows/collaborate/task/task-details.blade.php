@php
  use App\Enums\Workflows\ProjectType;use App\Enums\Workflows\TaskComplexity;use App\Models\Workflows\TaskApplication;
  use App\Enums\Workflows\TaskStatus;
  use App\Enums\Workflows\TaskPriority;
  $task->load(['parent']);
@endphp
<div class="mb-4">
  <div>
    <div class="flex justify-between pt-2 w-full space-x-2">

      <div>
        @if(!$task->user_id)
          @can('collaborate.workflows.task.assign-to-self')
            <x-ui.confirm-button
                :route="route('collaborate.tasks.application.assign-self', ['task' => $task->id])"
                method="POST"
                :label="__('workflows.task.assignment.assign_to_self')"
                :confirmation="__('workflows.task.assignment.assign_to_self_confirmation')"
                styling="outline"
            >
              {{ __('workflows.task.assignment.assign_to_self') }}
            </x-ui.confirm-button>
          @else

            @if(TaskApplication::where('task_id', $task->id)->where('user_id', Auth::id())->exists())
              <x-ui.confirm-button
                  :route="route('collaborate.tasks.application.destroy', ['task' => $task->id])"
                  method="DELETE"
                  :label="__('workflows.task.assignment.cancel_application')"
                  :confirmation="__('workflows.task.assignment.cancel_application_confirmation')"
                  styling="outline"
                  button-theme="secondary"
              >
                {{ __('workflows.task.assignment.cancel_application') }}
              </x-ui.confirm-button>

            @else
              @can('collaborate.workflows.task-application.create')
                <x-ui.confirm-button
                    :route="route('collaborate.tasks.application.create', ['task' => $task->id])"
                    method="POST"
                    :label="__('workflows.task.assignment.apply_for_task')"
                    :confirmation="__('workflows.task.assignment.apply_for_task_confirmation')"
                    styling="outline"
                >
                  {{ __('workflows.task.assignment.apply_for_task') }}
                </x-ui.confirm-button>
              @endcan
            @endif

          @endif
        @endcan

        @if($task->user_id && $task->user_id === auth()->id() && TaskStatus::todo()->is($task->task_status))
          @can('collaborate.workflows.task.remove-assignment-from-self')
            <x-ui.confirm-button
                :route="route('collaborate.tasks.application.remove-assignment-from-self', ['task' => $task->id])"
                method="DELETE"
                :label="__('workflows.task.assignment.remove_from_self')"
                :confirmation="__('workflows.task.assignment.remove_from_self_confirmation')"
                styling="outline"
                button-theme="secondary"
            >
              {{ __('workflows.task.assignment.remove_from_self') }}
            </x-ui.confirm-button>
          @endcan
        @endif
      </div>


      <x-workflows.task-status-switcher :task="$task"/>
    </div>
  </div>

  <table>
    @if($task->project_type)
      <tr>
        <x-ui.th>{{ __('workflows.task.project_type.title') }}</x-ui.th>
        <x-ui.td>
          {{ ProjectType::tryFrom($task->project_type)?->label() ?? '-' }}
        </x-ui.td>
      </tr>
    @endif
    <x-ui.th>{{ __('workflows.task.task_type') }}</x-ui.th>
    <x-ui.td>

      @if($task->taskType->course_link)
        <a target="_blank" class="text-primary flex items-start" href="{{ $task->taskType->course_link }}">
          <span class="mr-2">{{ $task->taskType->name }}</span>
          <x-ui.icon name="external-link" size="3"/>
        </a>
      @else
        {{ $task->taskType->name }}
      @endif

    </x-ui.td>
    </tr>
    <tr>
      <x-ui.th>{{ __('workflows.task.priority') }}</x-ui.th>
      <x-ui.td>{{ $task->priority ? TaskPriority::fromValue($task->priority)?->label() : '-' }}</x-ui.td>
    </tr>
    <tr>
      <x-ui.th>{{ __('workflows.task.complexity') }}</x-ui.th>
      <x-ui.td>{{ $task->complexity ? $task->complexity_label : '-' }}</x-ui.td>
    </tr>
    <tr>
      <x-ui.th>{{ __('workflows.task.hours') }}</x-ui.th>
      <x-ui.td>{{ $task->units }}</x-ui.td>
    </tr>
    @if($task->approximate_earnings)
      <tr>
        <x-ui.th>{{ __('workflows.task.approximate_earnings') }}</x-ui.th>
        <x-ui.td>{{ $task->approximate_earnings ?? '-' }}</x-ui.td>
      </tr>
    @endif
    <tr>
      <x-ui.th>{{ __('workflows.task.created_at') }}</x-ui.th>
      <x-ui.td>
        <x-ui.timestamp :timestamp="$task->created_at"/>
      </x-ui.td>
    </tr>
    <tr>
      <x-ui.th>{{ __('workflows.task.start_date') }}</x-ui.th>
      <x-ui.td>
        <x-ui.timestamp type="datetime" :timestamp="$task->start_date"/>
      </x-ui.td>
    </tr>
    <tr>
      <x-ui.th>{{ __('workflows.task.date_due') }}</x-ui.th>
      <x-ui.td>
        <x-ui.timestamp type="datetime" :timestamp="$task->due_date"/>
      </x-ui.td>
    </tr>
    <tr>
      <x-ui.th>{{ __('workflows.task.legislation') }}</x-ui.th>
      <x-ui.td>
        @if($task->taskType->taskRoute)
          <a target="_top" class="text-primary" href="{{ $task->taskType->taskRoute->generateRoute($task) }}">
            <span>{{ $task->document->title ?? '-' }}</span>
          </a>
        @else
          <span>{{ $task->document->title ?? '-' }}</span>
        @endif
      </x-ui.td>
    </tr>
    <tr>
      <x-ui.th>{{ __('workflows.task.group') }}</x-ui.th>
      <x-ui.td>{{ $task->group->title ?? '-' }}</x-ui.td>
    </tr>
    @can('collaborate.workflows.task.view-assignee')
      <tr>
        <x-ui.th>{{ __('workflows.task.assigned_to') }}</x-ui.th>
        <x-ui.td>{{ $task->assignee->full_name ?? '-' }}</x-ui.td>
      </tr>
    @endcan
    @can('collaborate.workflows.task.view-manager')
      <tr>
        <x-ui.th>{{ __('workflows.task.manager') }}</x-ui.th>
        <x-ui.td>{{ $task->manager->full_name ?? '-' }}</x-ui.td>
      </tr>
    @endcan
  </table>

  @if($task->description || ($task->parent_task_id && $task->parent->description))
    <div class="px-4 my-4">
      <x-ui.label for="description">
        <span class="text-libryo-gray-500 uppercase text-xs font-medium">{{ __('assess.assessment_item.description') }}</span>
      </x-ui.label>
      <div class="text-sm py-2">
        <x-ui.collaborate.wysiwyg-content :content="$task->parent->description ?? ''"/>
      </div>
      <div class="text-sm py-2">
        <x-ui.collaborate.wysiwyg-content :content="$task->description"/>
      </div>
    </div>
  @endif

  @include('partials.workflows.collaborate.task.task-ratings', ['ratings' => $task->ratings])

  <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
    @can('collaborate.storage.attachment.viewAny')
      <div>
        <div class="flex items-center space-x-1 mb-4">
          <x-ui.icon name="paperclip"/>
          <div class="font-semibold">
            <span>{{ __('workflows.task.attachments') }}</span>
          </div>
        </div>

        <x-turbo::frame :id="[$task, 'attachments']" loading="lazy"
                       :src="route('collaborate.task.attachments.index', $task->id)">
          <x-ui.skeleton/>
        </x-turbo::frame>
      </div>
    @endcan

    <div>
      <div class="flex items-center mb-4">
        <x-ui.icon name="comments"/>
        <span class="ml-3 mr-1 font-semibold">{{ __('workflows.task.comments') }}</span>
      </div>

      <x-turbo::frame :id="[$task, 'comments']" loading="lazy" :src="route('collaborate.comments.index', $task->id)">
        <x-ui.skeleton/>
      </x-turbo::frame>
    </div>
  </div>

</div>

