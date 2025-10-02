
@if($canUpdate)
  @include('partials.tasks.task.description-field')

  <div class="flex">
    <x-ui.dropdown>
      <x-slot:trigger>
        <button class="text-primary font-semibold cursor-pointer mt-2">
          {{ __('tasks.edit_description') }}
        </button>
      </x-slot:trigger>

      <div class="px-4 py-2 w-screen-75 max-w-lg">
        <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">
          <x-ui.input
              type="textarea"
              :value="old('description', $task->description ?? '')"
              name="description"
              label="{{ __('interface.description') }}" class="norma-editor-minimal"
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
  @include('partials.tasks.task.description-field')
@endif


