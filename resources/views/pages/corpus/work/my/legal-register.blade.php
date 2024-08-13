<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="gavel" class="mr-3 ml-5" size="8" />
      <div>
        {{ __('corpus.work.legal_register_export') }}
        <span class="text-xs text-libryo-gray-500 italic ml-3">{{ $libryo->title ?? ($organisation->title ?? '') }}</span>
      </div>
    </div>
  </x-slot>

  {{-- only available in single stream mode --}}
  @if ($libryo)
    <div class="flex justify-center mt-10 print:hidden">
      {{-- <x-ui.modal>
        <x-slot name="trigger">
          <x-ui.button @click="open = true" size="xl" theme="primary" class="tippy"
                       data-tippy-content="{{ __('interface.export') }}">
            <x-ui.icon name="download" class="mr-2" />
            {{ __('interface.export') }}
          </x-ui.button>
        </x-slot>

        <div class=" w-screen-50">
          <turbo-frame id="download-progress" loading="lazy"
                       src="{{ route('my.corpus.requirements.index.export.excel', $filters) }}">
          </turbo-frame>
        </div>
      </x-ui.modal> --}}

      <div>
        <x-ui.button @click="window.print()" size="xl" theme="primary" styling="outline" class="tippy ml-10"
                     data-tippy-content="{{ __('actions.print_page') }}">
          <x-ui.icon name="print" class="mr-2" />
          {{ __('actions.print_page') }}
        </x-ui.button>
      </div>
    </div>

    <div class="relative mt-10 mb-5 print:hidden">
      <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t border-libryo-gray-300"></div>
      </div>
      <div class="relative flex justify-center">
        <span class="px-2 bg-libryo-gray-50 text-sm text-libryo-gray-500"> {{ __('corpus.work.export_preview') }} </span>
      </div>
    </div>

    <div x-data="{ showingRerences: true }">
      <x-corpus.work.work-requirements-data-table :base-query="$baseQuery"
                                                  :fields="['panel_my_register']"
                                                  :route="route('my.corpus.requirements.legal-register')"
                                                  :striped="false"
                                                  :headings="false"
                                                  simple-paginate
                                                  filterable
                                                  :filters="['jurisdictionTypes', 'domains']"
                                                  :paginate="500">
        <x-slot name="leftActions">
          <div x-cloak class="mb-1 tippy inline-block"
               data-tippy-content="{{ __('corpus.reference.hide_show_references') }}">
            <x-ui.icon x-show="showingRerences" @click="showingRerences = !showingRerences" name="toggle-on"
                       class="cursor-pointer text-primary " size="8" />
            <x-ui.icon x-show="!showingRerences" @click="showingRerences = !showingRerences" name="toggle-off"
                       class="cursor-pointer text-libryo-gray-400" size="8" />
          </div>
        </x-slot>
      </x-corpus.work.work-requirements-data-table>

    </div>
  @else
    <x-ui.empty-state-icon icon="up-left"
                           :title="__('corpus.work.switch_to_single_stream')" />
  @endif

</x-layouts.app>
