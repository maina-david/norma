<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="clipboard-list" class="mr-3 ml-5  text-norma-gray-400" size="8" />
      <div>
        {{ __('corpus.enablon.exports') }}
      </div>
    </div>
  </x-slot>

  <div class="print:hidden mt-4 mx-4">
    @foreach($downloadTypes as $type)
    <div class="mb-4">
      <div class="font-semibold mb-2">{{ __("corpus.enablon.types.{$type->value}_export") }}</div>

      <div class="flex items-center space-x-4">
        <x-ui.modal>
          <x-slot name="trigger">
            <x-ui.button @click="type = 'excel';setTimeout(function () {open = true;}, 1000)" theme="primary" styling="outline" class="tippy" data-tippy-content="{{ __('interface.export_excel') }}">
              Standard
            </x-ui.button>
          </x-slot>

          <div class=" w-screen-50" x-data="{}">
            <turbo-frame id="{{ $type->value }}-download-progress" loading="lazy" src="{{ route('my.enablon.exports.generate', ['type' => $type->value]) }}">
            </turbo-frame>
          </div>
        </x-ui.modal>

        @foreach($variants as $variant)
          <x-ui.modal>
            <x-slot name="trigger">
              <x-ui.button @click="type = 'excel';setTimeout(function () {open = true;}, 1000)" theme="primary" styling="outline" class="tippy" data-tippy-content="{{ __('interface.export_excel') }}">
                {{ ucfirst($variant) }}
              </x-ui.button>
            </x-slot>

            <div class=" w-screen-50" x-data="{}">
              <turbo-frame id="{{ $type->mapFor($variant) }}-download-progress" loading="lazy" src="{{ route('my.enablon.exports.generate', ['type' => $type->value, 'mapper' => $type->mapFor($variant)]) }}">
              </turbo-frame>
            </div>
          </x-ui.modal>
        @endforeach
      </div>
    </div>
    @endforeach
  </div>


</x-layouts.app>
