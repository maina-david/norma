<div class="flex flex-col items-center py-8">
  @if(!($previewing ?? false) && $reference->requirementDraft)

    <div class="py-4">
      <div class="flex items-center">
        <x-ui.icon name="circle-info" class="text-info-darker" />
        <span class="ml-2 text-info-darker font-semibold">{{ __('corpus.reference.requirement_requested') }}</span>
      </div>
    </div>

    <div class="flex space-x-4 items-center mt-4">
      @can('collaborate.corpus.reference.requirement.apply')
        <x-ui.confirm-button
            styling="outline"
            button-theme="primary"
            method="put"
            :label="__('corpus.reference.approve')"
            :confirmation="__('corpus.reference.approve_draft_requirement')"
            :route="route('collaborate.work-expressions.references.requirement.apply', ['expression' => $expression->id, 'reference' => $reference->id])"
        >
          {{ __('corpus.reference.approve') }}
        </x-ui.confirm-button>
      @endcan

      @if(($task && $task->isAssignee()) || auth()->user()->can('collaborate.corpus.reference.requirement.delete'))
        <x-ui.delete-button
          styling="outline"
          :route="route('collaborate.work-expressions.references.requirement.draft.delete', ['expression' => $expression->id, 'reference' => $reference->id])"
        >
          {{ __('corpus.reference.delete_draft_requirement') }}
        </x-ui.delete-button>
      @endif
    </div>

  @else

    <div class="py-4">
      <div class="flex items-center">
        <x-ui.icon name="circle-info" class="text-info-darker" />
        <span class="ml-2 text-info-darker font-semibold">{{ __('corpus.reference.has_requirements') }}</span>
      </div>
    </div>

    @if(!($previewing ?? false))
      @can('collaborate.corpus.reference.requirement.delete-applied')
        <x-ui.delete-button
            styling="outline"
            :route="route('collaborate.work-expressions.references.requirement.delete', ['expression' => $expression->id, 'reference' => $reference->id])"
        >
          {{ __('corpus.reference.delete_requirement') }}
        </x-ui.delete-button>
      @endcan
    @endif

  @endif

</div>
