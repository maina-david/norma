<div class="">

    <x-ui.input name="title" :value="$resource->title ?? null" label="Title" required />

    <x-ui.textarea name="description" wysiwyg="minimal" :value="$resource->description ?? null" label="Description" />

    <x-ui.input name="start_date" type="date" :value="isset($resource) ? $resource->start_date?->format('Y-m-d') ?? null : null" label="{{ __('workflows.project.start_date') }}" />

    <x-ui.input name="due_date" type="date" :value="isset($resource) ? $resource->due_date?->format('Y-m-d') ?? null : null" label="{{ __('workflows.project.due_date') }}" />
    <x-ui.input name="context_due_date" type="date" :value="isset($resource) ? $resource->context_due_date?->format('Y-m-d') ?? null : null"
                label="{{ __('workflows.project.context_due_date') }}" />
    <x-ui.input name="actions_due_date" type="date" :value="isset($resource) ? $resource->actions_due_date?->format('Y-m-d') ?? null : null"
                label="{{ __('workflows.project.actions_due_date') }}" />



    <x-workflows.board.board-selector name="board_id" :value="old('board_id', $resource->board_id ?? null)" :label="__('workflows.project.default_workflow')" />

    <x-collaborators.group-selector name="group_id" :value="old('group_id', $resource->group_id ?? null)" :label="__('workflows.task.group')" :group="$resource->group ?? null" required />


    <div class="w-full">

        <x-collaborators.collaborate.production-pod-selector
                                                             :value="old(
                                                                 'production_pod_id',
                                                                 $resource->production_pod_id ?? null,
                                                             )"
                                                             name="production_pod_id"
                                                             label="{{ __('workflows.project.production_pod') }}" />
    </div>

    <div class="w-full">
        <x-workflows.project.project-size-selector
                                                   :value="old(
                                                       'project_size',
                                                       $resource->project_size ??
                                                           App\Enums\Workflows\ProjectSize::MEDIUM->value,
                                                   )"
                                                   name="project_size"
                                                   label="{{ __('workflows.project.project_size') }}" />
    </div>

    <div class="w-full">
        <x-workflows.project.project-rag-status-selector
                                                         :value="old(
                                                             'rag_status',
                                                             $resource->rag_status ??
                                                                 App\Enums\Workflows\ProjectRagStatus::GREEN->value,
                                                         )"
                                                         name="rag_status"
                                                         label="{{ __('workflows.project.rag_status') }}" />
    </div>

    <div class="w-full">

        <x-ui.language-selector
                                :value="old('language_code', $resource->language_code ?? 'eng')"
                                name="language_code"
                                label="{{ __('workflows.project.language_code') }}" />
    </div>


    <div class="w-full">

        <x-customer.organisation.organisation-selector
           :id="isset($resource) ? $resource->organisation_id : null"
           :title="isset($resource) && $resource->organisation_id ? $resource->organisation->title : null"
           :required="false"
           route="collaborate.organisations.search"
        />
    </div>

    <div class="w-full">

        <x-workflows.tasks.task-assignment-selector name="owner_id" :value="old('owner_id', $resource->owner_id ?? null)" :label="__('workflows.project.owner')"
                                                    :user="isset($resource)
                                                        ? ($resource->owner_id
                                                            ? $resource->owner
                                                            : null)
                                                        : null" />
    </div>
    <div class="w-full">

        <x-collaborators.collaborator-selector name="tracking_specialists[]" multiple :value="old('tracking_specialists', $resource?->trackingSpecialists ?? null)"
                                               :label="__('workflows.project.tracking_specialists')" />
    </div>
    <div class="w-full">


        <x-arachno.source.source-selector :label="__('arachno.source.sources')"
                                          multiple
                                          :value="old(
                                              'sources',
                                              isset($resource->sources)
                                                  ? $resource->sources->pluck('id')->toArray()
                                                  : [],
                                          )" name="sources[]" :allow-empty="true" />
    </div>
    <div class="w-full">
        <x-geonames.location.location-selector
                                               data-tomselect-no-reinitialise
                                               required
                                               :location="isset($resource->location) ? $resource->location : null"
                                               :value="old('location_id', $resource->location_id ?? null)"
                                               :label="__('geonames.location.jurisdiction')"
                                               name="location_id"
                                               class="w-44" />
    </div>
    <div class="w-full">
        <x-ontology.legal-domain-selector
                                          data-tomselect-no-reinitialise
                                          name="domains[]"
                                          :label="__('ontology.legal_domain.index_title')"
                                          multiple
                                          annotations
                                          :value="old(
                                              'domains',
                                              isset($resource->legalDomains)
                                                  ? $resource->legalDomains->pluck('id')->toArray()
                                                  : [],
                                          )" />
    </div>
</div>


<x-slot name="footer">
    <div></div>
    <div>
        <x-ui.back-button :fallback="route('collaborate.action-areas.index')" />
        <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
    </div>
</x-slot>
