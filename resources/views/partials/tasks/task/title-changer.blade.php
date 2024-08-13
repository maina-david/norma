@if($canUpdate)
  <x-ui.dropdown>
    <x-slot:trigger>
      <div class="text-primary underline cursor-pointer">
        <button class="text-primary font-semibold cursor-pointer mt-2">
          {{ __('tasks.edit_title') }}
        </button>
      </div>
    </x-slot:trigger>

    <div class="px-4 py-2 w-screen-75 max-w-xs">
      <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">
        <x-ui.input
          type="text"
          :value="old('title', $task->title ?? '')"
          name="title"
          label="{{ __('interface.title') }}"
        />

        <div class="mt-4">
          <x-ui.button theme="primary" type="submit">
            {{ __('actions.save') }}
          </x-ui.button>
        </div>
      </x-ui.form>
    </div>
  </x-ui.dropdown>
@endif


