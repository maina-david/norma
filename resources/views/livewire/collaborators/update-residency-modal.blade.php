<div>
  @if($shouldUpdate)
    <x-ui.modal :closable="false" x-init="function () { this.open = true; }">
      <x-slot:trigger>
        <div></div>
      </x-slot:trigger>

      <div class="max-w-md text-sm px-2">
        <div class="mb-4 font-semibold">
          {{ __('collaborators.collaborator.your_profile') }}
        </div>

        <div class="text-norma-gray-600 space-y-2">
          <p>
            {!! __('collaborators.collaborator.confirm_residency') !!}
          </p>
        </div>

        <div class="flex justify-end items-center mt-2 pt-4">
          <x-ui.button styling="outline" theme="primary" class="ml-2" wire:click="confirmResidency">
            {{ __('collaborators.collaborator.read_understood') }}
          </x-ui.button>
        </div>
      </div>
    </x-ui.modal>
  @endif
</div>

