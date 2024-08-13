@include('pages.workflows.collaborate.project.partials.project-nav')


<dl class="mt-10 grid grid-cols-1 text-sm leading-6 sm:grid-cols-3 gap-4">
    <div class="sm:4 col-span-3">
        <dd class="text-gray-700">{!! $resource->description !!}</dd>
    </div>

    <div class="bg-gray-50 rounded-lg p-5">
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.rag_status') }}</dt>
            <dd class="inline text-gray-700">
                {{ App\Enums\Workflows\ProjectRagStatus::from($resource->rag_status)->label() }}
            </dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.project_size') }}</dt>
            <dd class="inline text-gray-700">
                {{ App\Enums\Workflows\ProjectSize::from($resource->project_size)->label() }}
            </dd>
        </div>
    </div>
    <div class="bg-gray-50 rounded-lg p-5">
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('customer.organisation.organisation') }}</dt>
            <dd class="inline text-gray-700">{{ $resource->organisation?->title }}</dd>
        </div>
    </div>
    <div class="bg-gray-50 rounded-lg p-5">
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.production_pod') }}</dt>
            <dd class="inline text-gray-700">{{ $resource->productionPod?->title }}</dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.owner') }}</dt>
            <dd class="inline text-gray-700">{{ $resource->owner?->full_name }}</dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.tracking_specialists') }}</dt>
            <dd class="inline text-gray-700">
                {{ $resource->trackingSpecialists?->implode(fn($u) => $u->full_name, ', ') }}
            </dd>
        </div>
    </div>
    <div class="bg-gray-50 rounded-lg p-5">
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.start_date') }}</dt>
            <dd class="inline text-gray-700">
                <date
                      date={{ $resource->start_date?->format('Y-m-d') }}">
                    {{ $resource->start_date?->format('Y-m-d') }}</time>
            </dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.due_date') }}</dt>
            <dd class="inline text-gray-700">
                <date date={{ $resource->due_date?->format('Y-m-d') }}">
                    {{ $resource->due_date?->format('Y-m-d') }}</time>
            </dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.context_due_date') }}</dt>
            <dd class="inline text-gray-700">
                <date date={{ $resource->context_due_date?->format('Y-m-d') }}">
                    {{ $resource->context_due_date?->format('Y-m-d') }}</time>
            </dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.actions_due_date') }}</dt>
            <dd class="inline text-gray-700">
                <date date={{ $resource->actions_due_date?->format('Y-m-d') }}">
                    {{ $resource->actions_due_date?->format('Y-m-d') }}</time>
            </dd>
        </div>
    </div>
    <div class="bg-gray-50 rounded-lg p-5">
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.default_workflow') }}</dt>
            <dd class="inline text-gray-700">{{ $resource->board?->title }}</dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('geonames.location.jurisdiction') }}</dt>
            <dd class="inline text-gray-700 tippy" data-tippy-content="{{ $resource->location?->country?->title }}">
                {{ $resource->location?->title }}</dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('corpus.work.source') }}</dt>
            <dd class="inline text-gray-700">{{ $resource->sources->implode('title', ', ') }}</dd>
        </div>
        <div class="sm:pr-4 flex justify-between">
            <dt class="inline text-gray-500 mr-5">{{ __('workflows.project.language_code') }}</dt>
            <dd class="inline text-gray-700">{{ $resource->language_code }}</dd>
        </div>
    </div>


</dl>


@if ($resource->location)
    <div class="my-10">
        <a class="text-primary"
           href="{{ route('collaborate.corpus.requirements.preview.index', ['locations' => [$resource->location->id], 'domains' => $resource->legalDomains->modelKeys()]) }}">{{ __('workflows.project.see_live_requirements') }}</a>
    </div>
@endif


<table class="min-w-full divide-y divide-libryo-gray-300 mt-10">
    <thead class="bg-libryo-gray-50">
        <tr>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-libryo-gray-900 sm:pl-6">
            </th>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-libryo-gray-900 sm:pl-6">
                {{ __('workflows.project.tasks_pending_count') }}</th>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-libryo-gray-900 sm:pl-6">
                {{ __('workflows.project.tasks_todo_count') }}</th>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-libryo-gray-900 sm:pl-6">
                {{ __('workflows.project.tasks_in_progress_count') }}</th>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-libryo-gray-900 sm:pl-6">
                {{ __('workflows.project.tasks_in_review_count') }}</th>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-libryo-gray-900 sm:pl-6">
                {{ __('workflows.project.tasks_done_count') }}</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-libryo-gray-200 bg-white">
        @foreach ($taskTypes as $taskType)
            <tr>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-libryo-gray-500">{{ $taskType->name }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-libryo-gray-500">{{ $taskType->pending_count }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-libryo-gray-500">{{ $taskType->todo_count }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-libryo-gray-500">{{ $taskType->in_progress_count }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-libryo-gray-500">{{ $taskType->in_review_count }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-libryo-gray-500">{{ $taskType->done_count }}</td>
            </tr>
        @endforeach

    </tbody>
</table>
