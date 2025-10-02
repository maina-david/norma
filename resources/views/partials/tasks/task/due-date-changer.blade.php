@if($canUpdate)
<x-ui.dropdown>
  <x-slot:trigger>
    <div class="text-primary underline cursor-pointer">
      <x-ui.timestamp :timestamp="$task->due_on"/>
    </div>
  </x-slot:trigger>

  <div class="px-4 py-2 w-screen-75 max-w-xs">
    <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">

      <x-ui.input
        type="date"
        name="due_on"
        label="{{ __('assess.assessment_item.next_due_date') }}"
      />

      <div class="mt-4">
        <x-ui.button theme="primary" type="submit">
          {{ __('actions.save') }}
        </x-ui.button>
      </div>
    </x-ui.form>
  </div>
</x-ui.dropdown>
@else
<x-ui.timestamp class="text-norma-gray-500" :timestamp="$task->due_on"/>
@endif
