<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="gavel" class="mr-3 ml-5" size="8" />
      <div>
        {{ __('my.nav.requirements') }}
        <span class="text-xs text-norma-gray-500 italic ml-3">{{ $norma->title ?? ($organisation->title ?? '') }}</span>
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <a target="_blank"
         href="https://success.norma.com/en/knowledge/getting-started-with-norma/your-legal-register/your-custom-legal-register"
         class="tippy" data-tippy-content="{{ __('help.suggested_article_help') }}">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>

  {{-- only available in single stream mode --}}
  @if ($norma)
    @if ($norma->compilation_in_progress)
      <div class="text-white bg-secondary p-5 my-10">
        {{ __('customer.norma.compilation_in_progress_info') }}
      </div>
    @endif
    <div x-data="{ showingRerences: true }">
      <x-corpus.work.work-requirements-data-table
        :paginator="$works"
        :fields="['panel_my_register']"
        :route="route('my.corpus.requirements.index')"
        :striped="false"
        :headings="false"
        simple-paginate
        searchable
        :search-placeholder="__('corpus.reference.search_requirements') . '...'"
        :search-suggest-route="route('my.corpus.works.search-suggest-all', ['key' => 'requirements-search'])"
        search-suggest-target="corpus-requirements-search-suggest-requirements-search"
        filterable
        :filters="['jurisdictionTypes', 'domains', 'topics', 'controls', 'works', 'bookmarked']"
        side-filters
      >
        {{-- <x-slot name="leftActions">
          <div x-cloak class="mb-1 tippy inline-block"
               data-tippy-content="{{ __('corpus.reference.hide_show_references') }}">
            <x-ui.icon x-show="showingRerences" @click="showingRerences = !showingRerences" name="toggle-on"
                       class="cursor-pointer text-primary " size="8" />
            <x-ui.icon x-show="!showingRerences" @click="showingRerences = !showingRerences" name="toggle-off"
                       class="cursor-pointer text-norma-gray-400" size="8" />
          </div>
        </x-slot> --}}
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
                <turbo-frame id="download-progress" loading="lazy" x-bind:src="'{{ route('my.corpus.requirements.index.export.excel', $filters) }}'.replace(/excel/, type)">
                </turbo-frame>

                @if ($filtersApplied)
                  <div class="mt-5 italic px-10 text-center">
                    {{ __('corpus.work.export_filter_warning') }}
                  </div>
                @endif
              </div>
            </x-ui.modal>
          </div>
        </x-slot>
      </x-corpus.work.work-requirements-data-table>

    </div>
  @else
    <x-ui.empty-state-icon icon="up-left"
                           :title="__('corpus.work.switch_to_single_stream')" />
  @endif

</x-layouts.app>
