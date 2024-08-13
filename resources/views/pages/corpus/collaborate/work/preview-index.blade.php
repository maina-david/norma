<x-layouts.collaborate>

    <div x-data="{ showingActionAreas: false, showingContextQuestions: false, showingDomains: false, showingLocations: false }">
        <x-ui.card class="mb-3 sm:p-5">
            <x-ui.icon name="eye" /> <span>Show</span>

            <x-ui.icon @click="showingActionAreas = !showingActionAreas" class="cursor-pointer ml-2"
                       x-bind:class="showingActionAreas ? 'text-primary' : ''"
                       name="clipboard-list-check" />

            <x-ui.icon @click="showingContextQuestions = !showingContextQuestions" class="cursor-pointer ml-2"
                       x-bind:class="showingContextQuestions ? 'text-primary' : ''"
                       name="question" />

            <x-ui.icon @click="showingDomains = !showingDomains" class="cursor-pointer ml-2"
                       x-bind:class="showingDomains ? 'text-primary' : ''"
                       name="scale-balanced" />

            <x-ui.icon @click="showingLocations = !showingLocations" class="cursor-pointer ml-4"
                       x-bind:class="showingLocations ? 'text-primary' : ''"
                       name="location-dot" />
        </x-ui.card>
        <x-corpus.work.collaborate.requirements-preview-data-table
                                                                   :paginator="$works"
                                                                   :fields="['work_panel']"
                                                                   :route="route(
                                                                       'collaborate.corpus.requirements.preview.index',
                                                                   )"
                                                                   :striped="false"
                                                                   :headings="false"
                                                                   simple-paginate
                                                                   searchable
                                                                   :search-placeholder="__(
                                                                       'corpus.reference.search_requirements',
                                                                   ) . '...'"
                                                                   filterable
                                                                   :filters="[
                                                                       'domains',
                                                                       'locations',
                                                                       'questions',
                                                                       'no_questions',
                                                                   ]">
            </x-corpus.work.work-requirements-data-table>
    </div>


</x-layouts.collaborate>
