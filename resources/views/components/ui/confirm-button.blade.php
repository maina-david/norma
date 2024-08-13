@props(['route', 'method', 'label', 'confirmation', 'danger' => false, 'styling', 'buttonTheme', 'size', 'dataTurboFrame', 'isOpen' => false])

<x-ui.modal :is-open="$isOpen" teleport>

  <x-slot name="trigger">
    @if (isset($styling))
      <x-ui.button
        @click="open = true"
        :styling="$styling"
        :theme="$buttonTheme ?? 'primary'"
        type="button"
        :size="$size ?? 'md'"
        {{ $attributes }}
      >
        {{ $slot }}
      </x-ui.button>
    @else
      <button class="{{ $danger ? 'text-negative' : 'text-primary' }} font-semibold" type="button"
              @click="open = true">
        {{ $slot }}
      </button>
    @endif

  </x-slot>


  <form
      action="{{ $route }}"
      method="POST"
      {{ isset($dataTurboFrame) && $dataTurboFrame !== '' ? 'data-turbo-frame="' . $dataTurboFrame . '"' : '' }}
  >

    <div class="mb-4">
      <div class="font-semibold text-xl mb-2">{{ $label }}?</div>
      <div>{{ $confirmation }}</div>
    </div>

    {{ $formBody ?? null }}

    <div class="mt-10 sm:mt-8 flex flex-col sm:flex-row sm:space-x-4 space-y-3 sm:space-y-0 justify-between">
      <button @click="open = false" type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-libryo-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-libryo-gray-700 hover:bg-libryo-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">
        {{ __('actions.cancel') }}
      </button>
      <button @click="open = false" type="submit"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto sm:text-sm {{ $danger ? 'bg-negative hover:bg-negative focus:ring-negative' : 'bg-primary hover:bg-primary focus:ring-primary' }}">
        {{ $label }}
      </button>
    </div>

    @csrf
    @method($method)
  </form>

</x-ui.modal>
