@php
  $resource->load(['checks.collaborator']);
  $checked = $resource->checks->some(fn ($item) => now()->isSameDay($item->created_at));
  $canViewCollaborator = auth()->user()->can('collaborate.workflows.task-check.view-collaborator');
@endphp

@if(!$checked && $resource->taskType->has_checks && ($user->id === $resource->user_id || $user->can('collaborate.workflows.task-check.create')))
  <div class="flex justify-end my-3">
    <x-ui.confirm-button
        theme="primary"
        styling="outline"
        :route="route('collaborate.tasks.check', ['task' => $resource->id])"
        method="POST"
        :label="__('workflows.task.mark_as_checked')"
        :confirmation="__('workflows.task.mark_as_checked_confirmation')"
    >
      {{ __('workflows.task.mark_as_checked') }}

      <x-slot:formBody>
        <x-ui.input
          type="checkbox"
          name="no_entries"
          label="{{ __('workflows.task.no_entries') }}"
        />
      </x-slot:formBody>
    </x-ui.confirm-button>
  </div>
@endif

<x-ui.table no-search :rows="$resource->checks">
  <x-slot name="head">
    <x-ui.td>{{ __('workflows.task.checked_by') }}</x-ui.td>
    <x-ui.td>{{ __('workflows.task.no_entries') }}</x-ui.td>
    <x-ui.td>{{ __('timestamps.date') }}</x-ui.td>
  </x-slot>

  <x-slot name="body">
    @foreach ($resource->checks as $check)
      <x-ui.tr :loop="$loop">
        <x-ui.td>
          @if($check->user_id !== auth()->id() && !$canViewCollaborator)
              Collaborator
          @else
            {{ $check->collaborator->full_name ?? '-' }}
          @endif
        </x-ui.td>
        <x-ui.td>{{ $check->no_entries ? __('interface.yes') : __('interface.no') }}</x-ui.td>
        <x-ui.td>
          <x-ui.timestamp type="datetime" :timestamp="$check->created_at"/>
        </x-ui.td>
      </x-ui.tr>
    @endforeach
  </x-slot>
</x-ui.table>
