<div class="py-2">

  <div class="flex flex-col items-center py-8">
    @if($reference->has_consequences_update)

      <div class="py-4">
        <div class="flex items-center">
          <x-ui.icon name="circle-info" class="text-info-darker" />
          <span class="ml-2 text-info-darker font-semibold">{{ __('corpus.reference.consequence_requested') }}</span>
        </div>
      </div>

      <div class="flex space-x-4 items-center mt-4">
        @if(!($previewing ?? false) && ($task && $task->isAssignee()) || auth()->user()->can('collaborate.corpus.reference.consequence.delete'))
          <x-ui.delete-button
              styling="outline"
              :route="route('collaborate.work-expressions.references.consequence.draft.delete', ['expression' => $expression->id, 'reference' => $reference->id])"
          >
            {{ __('corpus.reference.delete_draft_consequence') }}
          </x-ui.delete-button>
        @endif
      </div>

    @else

      <div class="py-4">
        <div class="flex items-center">
          <x-ui.icon name="circle-info" class="text-info-darker" />
          <span class="ml-2 text-info-darker font-semibold">{{ __('corpus.reference.consequence_approved') }}</span>
        </div>
      </div>

    @if(!($previewing ?? false))
        @can('collaborate.corpus.reference.consequence.delete')
          <x-ui.delete-button
              styling="outline"
              :route="route('collaborate.work-expressions.references.consequence.delete', ['expression' => $expression->id, 'reference' => $reference->id])"
          >
            {{ __('corpus.reference.delete_consequence') }}
          </x-ui.delete-button>
        @endcan

    @endif
    @endif
  </div>














  @foreach($reference->consequences as $consequence)
    <div class="flex py-1">
      <div>{{ $consequence->description }}</div>
    </div>
  @endforeach
</div>
