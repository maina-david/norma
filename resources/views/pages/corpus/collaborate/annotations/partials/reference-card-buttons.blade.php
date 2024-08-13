@php
use App\Enums\Corpus\ReferenceType;
@endphp

<div class="flex items-center">

  <div class="flex items-center mt-2 space-x-2 flex-grow">

    @if(ReferenceType::citation()->is($reference->type))

      @if(!$reference->refRequirement && !$reference->requirementDraft && auth()->user()->can('collaborate.corpus.reference.requirement.create'))
        <x-ui.form
            :action="route('collaborate.work-expressions.references.requirement.store', ['expression' => $expression->id, 'reference' => $reference->id, 'task' => $task->id ?? null])"
            method="post"
        >
          <x-ui.button type="submit" styling="outline" size="md" class="tippy" :data-tippy-content="__('corpus.reference.request_requirement')">
            <x-ui.icon name="marker" size="4" />
          </x-ui.button>
        </x-ui.form>
      @endif

      @if(!$reference->has_consequences && !$reference->has_consequences_update && auth()->user()->can('collaborate.corpus.reference.consequence.create'))
      {{-- // removing this for the launch for now: TODO chat with Dave about this --}}
        {{-- <x-ui.form
            :action="route('collaborate.work-expressions.references.consequence.store', ['expression' => $expression->id, 'reference' => $reference->id, 'task' => $task->id ?? null])"
            method="post"
        >
          <x-ui.button type="submit" styling="outline" size="md" class="tippy" :data-tippy-content="__('corpus.reference.request_consequence_group')">
            <x-ui.icon name="scale-balanced" size="4" />
          </x-ui.button>
        </x-ui.form> --}}
      @endif

    @endif
  </div>

  <div class="flex flex-shrink-0">
  </div>
</div>
