<x-ui.modal>
  <x-slot:trigger>
    <a
        href="#"
        class="cursor-pointer text-primary block"
        @click.prevent="open = true"
        wire:tooltip="{{ __('actions.click_to_change') }}"
    >
      @if(isset($bulk))
        <span class="text-left block px-4">
          {{ $inLibryo ? __('compilation.context_question.inclusion.remove_from_stream') : __('compilation.context_question.inclusion.add_to_stream')  }}
        </span>
      @else
        <span class="text-center block px-4">
          {{ $inLibryo ? __('interface.yes') : __('interface.no') }}
        </span>
      @endif
    </a>
  </x-slot:trigger>

  <div class="px-4 note-form-body" x-data="{ submitError: null }">
    @if(!$errors->any())
    <div x-init="function () { if (open) { open = false; }}"></div>
    @endif
    <div class="font-semibold text-lg mb-4">{{ __('compilation.applicability.note') }}</div>
    <div class="mb-2">{{ __('actions.select_option') }}:</div>

    <div>
      @foreach($options as $option)
        <div class="mb-4">
          <label>
            <input
              required
              type="radio"
              @if($bulk)
                name="{{ $prefix }}bulk_type"
              @else
                wire:model="type"
              @endif
              value="{{ $option->value }}"
              key="option_{{ $option->value }}"
              @change="type = {{ $option->value }}"
            >
            <span class="ml-1">{{ $option->label() }}</span>
          </label>
        </div>
      @endforeach

      <div>
        <textarea
          @if($bulk)
            name="{{ $prefix }}bulk_comment"
          @else
            wire:model="comment"
          @endif
          required
          rows="8"
          class="px-3 py-2 border leading-normal rounded-md shadow-sm border-libryo-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 block mt-1 w-full"
        >{{ old('bulk_comment') }}</textarea>

      </div>

      @if($bulk)
        <div class="text-sm text-danger" x-html="submitError"></div>
      @endif

      <div class="border-t border-libryo-gray-200 mt-4 pt-4 flex items-center justify-end space-x-4">
        <x-ui.button styling="outline" theme="primary" type="button" @click.prevent="open = false">
          {{ __('actions.cancel') }}
        </x-ui.button>

        @if($bulk)
          <x-ui.button styling="outline" theme="primary" type="button" @click.prevent="submitError = {{ $bulk }}; if (!submitError) open = false;">
            {{ __('actions.save') }}
          </x-ui.button>
        @else
          <x-ui.button styling="outline" theme="primary" type="button" wire:click.prevent="toggleState">
            {{ __('actions.save') }}
          </x-ui.button>
        @endif

      </div>
    </div>
  </div>

</x-ui.modal>
