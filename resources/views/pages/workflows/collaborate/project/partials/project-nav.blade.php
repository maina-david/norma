<a href="{{ route('collaborate.projects.show', ['project' => $project->id]) }}"
   class="relative whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors transition ease-in-out duration-200 cursor-pointer {{ request()->route()->getName() === 'collaborate.projects.show' ? 'border-primary text-primary' : 'border-transparent text-norma-gray-500 hover:border-primary hover:text-primary' }}">
    {{ __('workflows.project.dashboard') }}
</a>
<a href="{{ route('collaborate.projects.tasks.index', ['project' => $project->id]) }}"
   class="relative whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors transition ease-in-out duration-200 cursor-pointer {{ request()->route()->getName() === 'collaborate.projects.tasks.index' ? 'border-primary text-primary' : 'border-transparent text-norma-gray-500 hover:border-primary hover:text-primary' }}">
    {{ __('workflows.project.tasks') }}
</a>
@can('collaborate.corpus.work.ingest-by-spreadsheet')
    <a href="{{ route('collaborate.projects.ingest.import', ['project' => $project->id]) }}"
       class="relative whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors transition ease-in-out duration-200 cursor-pointer {{ request()->route()->getName() === 'collaborate.projects.ingest.import' ? 'border-primary text-primary' : 'border-transparent text-norma-gray-500 hover:border-primary hover:text-primary' }}">
        {{ __('workflows.project.import_by_spreadsheet') }}
    </a>
@endcan
