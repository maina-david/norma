<div class="mt-4">

  @if(!($previewing ?? false))
    <div class="flex items-center space-x-4 mb-4">
      <x-corpus.reference.link-type-selector
        :expression-id="$expression->id"
        :reference-id="$reference->id"
        :task-id="$task->id ?? null"
        :work-id="$reference->work_id"
        :has-consequences="$reference->hasConsequenceRelations()"
        :has-requirement="$reference->refRequirement || $reference->requirementDraft"
      />
    </div>
  @endif

  <x-corpus.reference.link-type-relations :previewing="$previewing ?? false" :task="$task" :reference="$reference" :references="$reference->linkedParents" type="parent" :expression="$expression" />
  <x-corpus.reference.link-type-relations :previewing="$previewing ?? false" :task="$task" :reference="$reference" :references="$reference->linkedChildren" type="child" :expression="$expression" />
</div>
