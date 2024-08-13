<a class="text-primary"
   href="{{ route('collaborate.projects.show', ['project' => $row->id]) }}">{{ $row->title }}</a>
<div class="text-xs text-gray-400">
    {{ $row->legalDomains->implode('title', ', ') }}
</div>
<div class="flex justify-end w-full text-xs">
    <div><a
           href="{{ route('collaborate.tasks.index', ['workflow' => $row->board_id, 'project' => $row->id]) }}">{{ __('workflows.project.view_in_workflows') }}</a>
    </div>
</div>
