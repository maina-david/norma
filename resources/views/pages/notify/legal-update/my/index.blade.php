<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="bell" class="mr-3 text-norma-gray-400" size="8" />
      <div>
        {{ __('my.nav.updates') }}
        <span class="text-xs text-norma-gray-500 italic ml-3">{{ $subTitle }}</span>
      </div>
    </div>
  </x-slot>
  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <a target="_blank"
         href="https://success.norma.com/en/knowledge/getting-started-with-norma/notifications/navigating-your-notifications">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>

  <div class="mt-5">
    <x-notify.legal-update.legal-update-data-table
                                                   :base-query="$query"
                                                   :route="route('my.notify.legal-updates.index')"
                                                   :fields="['detailed']"
                                                   searchable
                                                   :search-placeholder="__('notify.legal_update.search_updates') . '...'"
                                                   filterable
                                                   :filters="['jurisdictionTypes', 'domains', 'status', 'from', 'to', 'bookmarked']"
                                                   :headings="false"
                                                   :paginate="15">
      <x-slot name="actionButton">
        <div x-data="{type: 'excel'}">
          <x-ui.modal>
            <x-slot name="trigger">
              <x-ui.button @click="type = 'excel';setTimeout(function () {open = true;}, 1000)" theme="primary" styling="outline" class="tippy" data-tippy-content="{{ __('interface.export_excel') }}">
                <x-ui.icon name="file-excel" />
              </x-ui.button>

              <x-ui.button @click="type = 'pdf';setTimeout(function () {open = true;}, 1000)" theme="primary" styling="outline" class="tippy" data-tippy-content="{{ __('interface.export_pdf') }}">
                <x-ui.icon name="file-pdf" />
              </x-ui.button>
            </x-slot>

            <div class=" w-screen-50">
              <turbo-frame id="download-progress" loading="lazy" x-bind:src="'{{ route('my.legal-updates.export.excel', $filters) }}'.replace(/excel/, type)">
              </turbo-frame>
            </div>
          </x-ui.modal>
        </div>
      </x-slot>
    </x-notify.legal-update.legal-update-data-table>
  </div>

</x-layouts.app>
