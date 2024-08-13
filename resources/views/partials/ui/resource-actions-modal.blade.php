<x-ui.modal id="actions-modal-target" is-open>
  <x-slot:trigger></x-slot:trigger>

  <x-ui.form id="resource-{{ $action }}" method="POST" action="{{ $route }}" class="w-full overflow-x-auto bg-white">
    <input type="hidden" name="action-validated" value="{{ Hash::make(1) }}" />
    <input type="hidden" name="action" value="{{ $action }}" />

    @foreach($resources as $resource)
      <input type="hidden" name="actions-checkbox-{{ $resource }}" value="true" />
    @endforeach

    <div class="px-4 pb-4 max-w-lg w-screen overflow-y-auto custom-scroll">
      <div class="font-semibold text-lg">{{ $label }}</div>

      @include($view)

      <div class="flex justify-end mt-2">
        <x-ui.button type="button" @click="open = false" theme="primary" styling="outline">
          {{ __('actions.cancel') }}
        </x-ui.button>

        <x-ui.button type="submit" @click="open = false" theme="primary" styling="outline" class="ml-4">
          {{ $submitLabel }}
        </x-ui.button>
      </div>

    </div>

  </x-ui.form>

</x-ui.modal>
