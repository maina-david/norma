<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="gavel" class="mr-3 ml-5 text-libryo-gray-400" size="8" />
      <div>
        {{ __('my.nav.requirements') }}
        <span class="text-xs text-libryo-gray-500 italic ml-3">{{ $pertaining }}</span>
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <a target="_blank"
         href="https://success.libryo.com/en/knowledge/getting-started-with-libryo/your-legal-register/your-custom-legal-register">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>


  <div>
    <x-corpus.reference.reference-data-table :paginator="$items"
                                             :route="route('my.corpus.references.index')"
                                             :fields="['detailed']"
                                             :headings="false"
                                             filterable
                                             searchable
                                             :search-suggest-route="route('my.corpus.references.search-suggest', ['key' => 'detailed-requirements-page'])"
                                             search-suggest-target="corpus-reference-search-suggest-detailed-requirements-page"
                                             :search-placeholder="__('corpus.reference.search_requirements') . '...'"
                                             :filters="['jurisdictionTypes','domains', 'tags', 'works']"
                                             :striped="false">
    </x-corpus.reference.reference-data-table>
  </div>
</x-layouts.app>
