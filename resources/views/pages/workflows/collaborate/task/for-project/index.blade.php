@can('collaborate.workflows.task.create')
    <x-slot:actions>
        <x-workflows.collaborate.task-create-wizard-button :project-id="$project->id" />
    </x-slot:actions>
@endcan


<x-ui.card>
    @include('pages.workflows.collaborate.project.partials.project-nav')

    @include('pages.crud.index-table')
</x-ui.card>
