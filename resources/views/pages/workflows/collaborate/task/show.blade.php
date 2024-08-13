@php
  use App\Enums\Workflows\TaskStatus;
  $user = auth()->user();
@endphp
<div class="mb-8 flex justify-between items-center">
  <div class="flex items-center">
    <span
        class="inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium bg-dark text-white whitespace-nowrap mr-1">#{{ $resource->id }}</span>
    <span class="font-semibold leading-4">{{ $resource->title }}</span>
  </div>

  @if ($withEdit ?? false)
    @can('collaborate.workflows.task.update')
      <x-ui.button styling="outline" type="link" :href="route('collaborate.tasks.edit', $resource->id)" target="_top">
        <span>{{ __('actions.edit') }}</span>
      </x-ui.button>
    @endcan
  @endif
</div>

<x-ui.tabs>

  <x-slot name="nav">
    <x-ui.tab-nav name="details">{{ __('workflows.task.details') }}</x-ui.tab-nav>
    <x-ui.tab-nav name="related">{{ __('workflows.task.related_tasks') }}</x-ui.tab-nav>

    @if($resource->taskType->has_checks && ($user->id === $resource->user_id || $user->can('collaborate.workflows.task-check.viewAny')))
      <x-ui.tab-nav name="checks">{{ __('workflows.task.checks') }}</x-ui.tab-nav>
    @endif

    @collaborateViewAny(['workflows.task-transition'])
    <x-ui.tab-nav name="transitions">{{ __('workflows.task.transitions') }}</x-ui.tab-nav>
    @endcollaborateViewAny

    @collaborateViewAny(['payments.payment-request'])
    <x-ui.tab-nav name="payments">{{ __('workflows.task.payments') }}</x-ui.tab-nav>
    @endcollaborateViewAny
  </x-slot>

  <div class="no-shadow">

    @collaborateViewAny(['workflows.task-transition'])
    <x-ui.tab-content name="transitions">
      <x-ui.table no-search :rows="$resource->transitions">
        <x-slot name="head">
          <x-ui.td>{{ __('workflows.task.status') }}</x-ui.td>
          <x-ui.td>{{ __('workflows.task.assigned_to') }}</x-ui.td>
          <x-ui.td>{{ __('timestamps.date') }}</x-ui.td>
        </x-slot>

        <x-slot name="body">
          @foreach ($resource->transitions as $transition)
            <x-ui.tr :loop="$loop">
              <x-ui.td>{{ $transition->task_status ? TaskStatus::fromValue($transition->task_status)->label() : '-' }}</x-ui.td>
              <x-ui.td>{{ $transition->user->full_name ?? '-' }}</x-ui.td>
              <x-ui.td>
                <x-ui.timestamp type="datetime" :timestamp="$transition->created_at"/>
              </x-ui.td>
            </x-ui.tr>
          @endforeach
        </x-slot>
      </x-ui.table>
    </x-ui.tab-content>
    @endcollaborateViewAny

    @collaborateViewAny(['payments.payment-request'])
    <x-ui.tab-content name="payments">

      @if ($resource->paymentRequests->isEmpty())
        <div class="flex justify-end my-3">
          <x-ui.confirm-button theme="primary" styling="outline"
                               :route="route('collaborate.payments.payment-requests.store', [
                                     'task' => $resource->id,
                                 ])"
                               method="POST"
                               :label="__('payments.payment_request.make_for_task')"
                               :confirmation="__('payments.payment_request.confirm_create')">
            {{ __('payments.payment_request.make_for_task') }}
          </x-ui.confirm-button>
        </div>
      @endif
      <x-ui.table no-search :rows="$resource->paymentRequests">
        <x-slot name="head">
          <x-ui.td>{{ __('collaborators.team.team') }}</x-ui.td>
          <x-ui.td>{{ __('workflows.task.units') }}</x-ui.td>
          <x-ui.td>{{ __('payments.payment_request.paid') }}</x-ui.td>
          <x-ui.td>{{ __('timestamps.date') }}</x-ui.td>
          @can('collaborate.payments.payment-request.delete')
            <x-ui.td></x-ui.td>
          @endcan
        </x-slot>

        <x-slot name="body">
          @foreach ($resource->paymentRequests as $req)
            <x-ui.tr :loop="$loop">
              <x-ui.td>{{ $req->team->title ?? '-' }}</x-ui.td>
              <x-ui.td>{{ $req->units }}</x-ui.td>
              <x-ui.td>{{ $req->paid ? 'Yes' : 'No' }}</x-ui.td>
              <x-ui.td>
                <x-ui.timestamp type="datetime" :timestamp="$req->created_at"/>
              </x-ui.td>
              @can('collaborate.payments.payment-request.delete')
                <x-ui.td>
                  <x-ui.delete-button
                      :route="route('collaborate.payments.payment-requests.destroy', ['payment_request' => $req->id])"
                      button-theme="negative">{{ __('actions.delete') }}</x-ui.delete-button>
                </x-ui.td>
              @endcan
            </x-ui.tr>
          @endforeach
        </x-slot>
      </x-ui.table>
    </x-ui.tab-content>
    @endcollaborateViewAny


    @if($resource->taskType->has_checks && ($user->id === $resource->user_id || $user->can('collaborate.workflows.task-check.viewAny')))

      <x-ui.tab-content name="checks">
        @include('partials.workflows.collaborate.task.task-checks', ['task' => $resource])
      </x-ui.tab-content>

    @endif


    <x-ui.tab-content name="details">
      @include('partials.workflows.collaborate.task.task-details', ['task' => $resource])
    </x-ui.tab-content>

    <x-ui.tab-content name="related">
      @include('partials.workflows.collaborate.task.task-siblings', ['task' => $resource])
    </x-ui.tab-content>
  </div>


</x-ui.tabs>
