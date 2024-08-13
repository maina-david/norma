@php
use App\Enums\Corpus\WorkType;
use App\Enums\Corpus\WorkStatus;
@endphp
<div>
  <x-ui.show-field :label="__('corpus.work.title')" :value="$resource->title" />

  <x-ui.show-field :label="__('corpus.work.title_translation')" :value="$resource->title_translation" />

  <div class="grid grid-cols-2 gap-2">
    <x-ui.show-field :label="__('corpus.work.short_title')" :value="$resource->short_title" />

    <x-ui.show-field :label="__('corpus.work.issuing_authority')" :value="$resource->issuing_authority" />

    <x-ui.show-field :label="__('corpus.work.work_type')" :value="WorkType::lang()[$resource->work_type]" />

    <x-ui.show-field :label="__('corpus.work.status')" :value="WorkStatus::lang()[$resource->status]" />

    <x-ui.show-field :label="__('corpus.work.primary_jurisdiction')"
      :value="$resource->primaryLocation->title ?? null" />

    @can('collaborate.corpus.work.set-organisation')
    <x-ui.show-field :label="__('corpus.work.types.site_specific')" :value="$resource->organisation->title ?? '-'" />
    @endcan

    <x-ui.show-field :label="__('corpus.work.source')" :value="$resource->source->title ?? null" />
  </div>


  <div class="break-all">
    <x-ui.show-field :label="__('corpus.work.source_url')" :value="$resource->source_url" />
  </div>

  <x-ui.show-field :label="__('corpus.work.catalogue_work_id')" :value="$resource->catalogue_work_id" />

  <x-ui.show-field :label="__('corpus.work.catalogue_doc')">
    @if ($resource->catalogueDoc)
    <a class="text-primary" target="_top"
      href="{{ route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => $resource->catalogueDoc->id]) }}">
      {{ $resource->catalogueDoc->title }}
    </a>

    @can('collaborate.corpus.work.link-to-doc')
    <div class="mt-4">
      <x-ui.confirm-button method="DELETE"
        :route="route('collaborate.corpus.works.unlink-from-doc', ['work' => $resource->id])"
        :label="__('corpus.doc.unlink_doc_and_work')" :confirmation="__('corpus.doc.unlink_doc_and_work_confirmation')"
        styling="outline" button-theme="negative">
        <x-slot:formBody>
          <input type="hidden" name="redirect_back" value="{{ url()->current() }}">
        </x-slot:formBody>
        {{ __('corpus.doc.unlink_doc_and_work') }}
      </x-ui.confirm-button>
    </div>
    @endcan
    @else
    @can('collaborate.corpus.work.link-to-doc')
    <x-ui.form class="space-y-2" method="POST" data-turbo-frame="_top"
      action="{{ route('collaborate.corpus.works.link-to-doc', ['work' => $resource->id]) }}">

      <div class="mt-5 sm:flex sm:items-center">
        <div class="sm:max-w-xs">
          <x-ui.input class="w-20" name="catalogue_doc_id" required></x-ui.input>
        </div>
        <x-ui.button theme="primary" class="ml-1" type="submit">{{ __('corpus.work.link_to_doc') }}</x-ui.button>
      </div>

    </x-ui.form>
    @endcan
    @endif
  </x-ui.show-field>

  <div class="grid grid-cols-2 gap-2">
    <x-ui.show-field :label="__('corpus.work.work_number')" :value="$resource->work_number" />

    <x-ui.show-field :label="__('corpus.work.gazette_number')" :value="$resource->gazette_number" />

    <x-ui.show-field :label="__('corpus.work.notice_number')" :value="$resource->notice_number" />

    <x-ui.show-field type="date" :label="__('corpus.work.publication_date')" :value="$resource->work_date" />

    <x-ui.show-field type="date" :label="__('corpus.work.effective_date')" :value="$resource->effective_date" />

    <x-ui.show-field type="date" :label="__('corpus.work.repealed_date')" :value="$resource->repealed_date" />

    <x-ui.show-field type="date" :label="__('corpus.work.comment_date')" :value="$resource->comment_date" />
  </div>

  <x-ui.show-field :label="__('corpus.work.highlights')">
    {!! $resource->highlights !!}
  </x-ui.show-field>

  <x-ui.show-field :label="__('corpus.work.summary_of_highlights')">
    {!! $resource->update_report !!}
  </x-ui.show-field>

  <x-ui.show-field :label="__('corpus.work.changes_to_register')">
    {!! $resource->changes_to_register !!}
  </x-ui.show-field>


  <x-ui.show-field :label="__('collaborators.group.created_at')">
    <x-ui.timestamp :timestamp="$resource->created_at" />
  </x-ui.show-field>

</div>
