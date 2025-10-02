{{-- <x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="gavel" class="mr-3 ml-5 text-norma-gray-400" size="8" />
      <div>
        {{ __('my.nav.requirements') }}
        <span class="text-xs text-norma-gray-500 italic ml-3">{{ $norma ? $norma->title : $organisation->title }}</span>
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <a target="_blank"
         href="https://success.norma.com/en/knowledge/getting-started-with-norma/your-legal-register/your-custom-legal-register">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>

  <div>

    @if ($norma && $norma->compilation_in_progress)
      <div class="text-white bg-secondary p-5 my-10">
        {{ __('customer.norma.compilation_in_progress_info') }}
      </div>
    @endif

    <x-corpus.work.work-requirements-data-table :base-query="$baseQuery"
                                                :fields="['panel_my']"
                                                :route="route('my.corpus.requirements.index')"
                                                filterable
                                                :filter-route="route('my.corpus.references.index')"
                                                searchable
                                                :search-placeholder="__('corpus.reference.search_requirements') . '...'"
                                                :search-suggest-route="route('my.corpus.references.search-suggest', ['key' => 'requirements-page'])"
                                                search-suggest-target="corpus-reference-search-suggest-requirements-page"
                                                :striped="true"
                                                :headings="false"
                                                :filters="['jurisdictionTypes','domains', 'tags']"
                                                :paginate="100">
    </x-corpus.work.work-requirements-data-table>

  </div>

</x-layouts.app> --}}
