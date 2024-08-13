<div>

  <div class="flex items-center justify-end">
    @if (!empty($libryo))
      @if($relation === 'reference')
        <x-ui.modal>
          <x-slot name="trigger">

            <div class="grid justify-items-end mb-5 mr-4">
              <x-ui.button theme="primary" @click="open = true">
                <x-ui.icon name="magnifying-glass" size="3" class="mr-3" />
                {{ __('tasks.suggested_tasks') }}
              </x-ui.button>
            </div>

          </x-slot>

          <div class="max-w-[80vw]">
            <turbo-frame
                src="{{ route('my.tasks.tasks.for.related.suggest', ['relation' => $relation, 'id' => $related->id, 'libryo' => $libryo]) }}"
                loading="lazy"
                id="suggested-task-for-related-{{ $relation }}-{{ $related->id }}"
            >
              <div class="px-8 my-10">
                <x-ui.skeleton flat />

                <div class="pt-8 w-full">
                  <div class="text-center italic lg">{{ loadingQuote() }}</div>
                </div>
              </div>
            </turbo-frame>
          </div>
        </x-ui.modal>
      @endif

      <x-ui.modal>
        <x-slot name="trigger">

          <div class="grid justify-items-end mb-5 mr-4">
{{--            <x-ui.button theme="primary" @click="open = true">--}}
{{--              <x-ui.icon name="plus" size="3" class="mr-3" />--}}
{{--              {{ __('tasks.create_task') }}--}}
{{--            </x-ui.button>--}}


            <x-tasks.create-task-button taskable-type="{{ $taskableType }}" taskable-id="{{ $taskableId }}" libryo-id="{{ $libryo->id }}" />
          </div>

        </x-slot>

        <turbo-frame src="{{ route('my.tasks.tasks.for.related.create', [
            'relation' => $relation,
            'id' => $related->id,
            'libryo' => $libryo,
        ]) }}"
                     loading="lazy" id="create-task-for-related-{{ $relation }}-{{ $related->id }}">
          <x-ui.skeleton />
        </turbo-frame>
      </x-ui.modal>
    @endif
  </div>

  @if ($query->count() === 0)
    <x-ui.empty-state-icon icon="tasks"
                           :title="__('tasks.no_tasks_added')"
                           :subline="__('tasks.when_added')" />
  @else
    <x-tasks.task.task-data-table :base-query="$query"
                                  :route="route('my.tasks.tasks.for.related.index', ['relation' => $relation, 'id' => $related])"
                                  :fields="['compact']"
                                  :headings="false"
                                  :filters="['assignee', 'statuses', 'type', 'priority', 'archived']"
                                  :paginate="100"
    />
  @endif

</div>
