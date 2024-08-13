<div>
    @if(request()->routeIs('my.projects.index'))
        <x-ui.button styling="flat" theme="primary" type="link"
                     :href="route('my.projects.edit', ['project' => $project->hash_id])">{{ __('actions.edit') }}
        </x-ui.button>
    @elseif(request()->routeIs('my.tasks.task-projects.index'))
        <x-ui.button styling="flat" theme="primary" type="link"
                     :href="route('my.tasks.task-projects.edit', ['project' => $project->hash_id])">{{ __('actions.edit') }}
        </x-ui.button>
    @endif
</div>
