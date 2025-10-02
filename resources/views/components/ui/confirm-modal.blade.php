@props(['route', 'method' => 'delete', 'label' => __('actions.delete'), 'confirmation' => __('actions.delete_confirmation'), 'danger' => true])

<form {{ $attributes }} action="{{ $route }}" method="POST">

  <x-ui.modal>

    <x-slot name="trigger">
      <div @click="open = true">
        {{ $slot }}
      </div>
    </x-slot>

    <div class="mb-4">
      <div class="font-semibold text-xl mb-2">{{ $label }}?</div>
      <div>{{ $confirmation ?? __('actions.confirmation') }}</div>
    </div>

    {{ $body ?? '' }}

    <x-slot name="footer">
      <button @click="open = false" type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-norma-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-norma-gray-700 hover:bg-norma-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">
        {{ __('actions.cancel') }}
      </button>
      <button type="submit"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto sm:text-sm {{ $danger ? 'bg-negative hover:bg-negative focus:ring-negative' : 'bg-primary hover:bg-primary focus:ring-primary' }}">
        {{ $label }}
      </button>
    </x-slot>

  </x-ui.modal>


  @csrf
  @method($method)
</form>
