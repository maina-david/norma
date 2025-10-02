@php
  use App\Enums\Notify\LegalUpdatePublishedStatus;
  $status = LegalUpdatePublishedStatus::tryFrom($resource->status);
@endphp
<x-ui.tabs>
  <x-slot name="nav">
    <x-ui.tab-nav name="details">{{ __('corpus.work.types.legal_update') }}</x-ui.tab-nav>

    @if ($resource->contentResource || $resource->created_from_doc_id)
      <x-ui.tab-nav name="related">{{ __('corpus.work.source_document') }}</x-ui.tab-nav>
    @endif

    @if($resource->createdFromDoc?->firstContentResource?->textContentResource)
      <x-ui.tab-nav name="text">{{ __('corpus.doc.plain_text') }}</x-ui.tab-nav>
    @endif

    @if($resource->maintainedWorks?->isNotEmpty())
      <x-ui.tab-nav name="maintained_works">{{ __('notify.legal_update.maintained_works') }}</x-ui.tab-nav>
    @endif

  </x-slot>

  <x-ui.tab-content name="details">
    <div>
      <x-ui.show-field :label="__('auth.user.status')">
        <div class="flex items-center space-x-4">
          <div>{{ $status->label() }}</div>

          @can('collaborate.notify.legal-update.publish')
            @if ($status !== LegalUpdatePublishedStatus::PUBLISHED)
              <x-ui.confirm-button
                  styling="outline"
                  :route="route('collaborate.legal-updates.publish', $resource->id)"
                  method="PUT"
                  :label="__('notify.legal_update.publish')"
                  :confirmation="__('notify.legal_update.publish_confirmation')"
              >
                {{ __('notify.legal_update.publish') }}
              </x-ui.confirm-button>
            @endif

            @if ($status === LegalUpdatePublishedStatus::PUBLISHED)
              <x-ui.confirm-button
                  button-theme="negative"
                  styling="outline"
                  :route="route('collaborate.legal-updates.not-applicable', $resource->id)"
                  method="PUT"
                  :label="__('notify.legal_update.not_applicable')"
                  :confirmation="__('notify.legal_update.not_applicable_confirmation')"
              >
                {{ __('notify.legal_update.mark_not_applicable') }}
              </x-ui.confirm-button>
            @endif
          @endcan

        </div>
      </x-ui.show-field>

      <div class="mt-4">
        <x-ui.show-field type="checkbox" :label="__('notifications.collaborate.update_is_late')" :value="$resource->late"/>
      </div>

      <x-ui.show-field type="date" :value="$resource->release_at" :label="__('notify.legal_update.release_date')"/>

      <x-ui.show-field :value="$resource->title" :label="__('interface.title')"/>

      <x-ui.show-field :value="$resource->title_translation" :label="__('corpus.work.title_translation')"/>

      @if($resource->created_from_doc_id && $resource->createdFromDoc)
        <x-ui.show-field :label="__('notify.legal_update.created_from')">
          <span>{{ $resource->created_from_doc_id }} - </span>
          <span>{{ $resource->createdFromDoc->title }}</span>
        </x-ui.show-field>
      @endif

      <x-ui.show-field :value="$resource->notifiable->title ?? null"
                       :label="__('notify.legal_update.published_in_terms_of')"/>

      <x-ui.show-field :value="$resource->notifyReference?->refPlainText?->plain_text"
                       :label="__('notify.legal_update.notify_against')"/>

      <x-ui.show-field :value="$resource->source->title ?? null" :label="__('corpus.work.source')"/>

      <x-ui.show-field :value="$resource->language_code" :label="__('notify.legal_update.language')"/>
      <x-ui.show-field :label="__('notify.legal_update.primary_jurisdiction')">
        @if ($resource->primaryLocation)
          <div class="flex">
            <div class="h-8 w-8 mr-5">
              <x-ui.country-flag :country-code="$resource->primaryLocation->flag"
                                 class="h-8 w-8 rounded-full tippy"
                                 data-tippy-content="{{ $resource->primaryLocation->title }}"/>
            </div>
            {{ $resource->primaryLocation?->title }}
          </div>
        @else
          -
        @endif
      </x-ui.show-field>
      <x-ui.show-field :value="$resource->work_number" :label="__('notify.legal_update.work_number')"/>
      <x-ui.show-field :value="$resource->publication_number" :label="__('notify.legal_update.gazette_number')"/>
      <x-ui.show-field :value="$resource->publication_document_number"
                       :label="__('notify.legal_update.notice_number')"/>
      <x-ui.show-field type="date" :value="$resource->publication_date"
                       :label="__('notify.legal_update.publication_date')"/>
      <x-ui.show-field type="date" :value="$resource->effective_date"
                       :label="__('notify.legal_update.effective_date')"/>

      <x-ui.show-field :label="__('notify.legal_update.highlights')">
        <x-ui.collaborate.wysiwyg-content :content="$resource->highlights ?? '-'"/>
      </x-ui.show-field>

      <x-ui.show-field :label="__('notify.legal_update.summary_of_highlights')">
        <x-ui.collaborate.wysiwyg-content :content="$resource->update_report ?? '-'"/>
      </x-ui.show-field>

      <x-ui.show-field :label="__('notify.legal_update.changes_to_requirements')">
        <x-ui.collaborate.wysiwyg-content :content="$resource->changes_to_register ?? '-'"/>
      </x-ui.show-field>
    </div>
  </x-ui.tab-content>

  @if ($resource->contentResource)
    <x-ui.tab-content name="related" class="h-screen-75">
      <embed class="w-full h-full" src="{{ asset($resource->contentResource->path) }}">
    </x-ui.tab-content>
  @endif


  @if($resource->createdFromDoc?->firstContentResource?->textContentResource)
    <x-ui.tab-content name="text" class="h-screen-75">
      <div
        class="h-full overflow-y-auto custom-scroll"
        data-lazy-source="{{ asset($resource->createdFromDoc->firstContentResource->textContentResource->path) }}"
      ></div>
    </x-ui.tab-content>
  @endif

  @if($resource->maintainedWorks?->isNotEmpty())
    <x-ui.tab-content name="maintained_works">
      <div class="pt-4 no-shadow">
        <x-ui.table no-search :rows="$resource->maintainedWorks">
          <x-slot:head>
            <x-ui.th>{{ __('corpus.work.title') }}</x-ui.th>
          </x-slot:head>

          <x-slot:body>
            @foreach($resource->maintainedWorks as $work)
              <x-ui.tr :loop="$loop">
                <x-ui.th>
                  @can('collaborate.corpus.work.view')
                    <a href="{{ route('collaborate.works.show', ['work' => $work->id]) }}" class="text-primary">
                  <span class="flex flex-col w-full">
                    <span>{{ $work->title }}</span>
                    <span class="text-norma-gray-600 font-normal">{{ $work->title_translation }}</span>
                  </span>
                    </a>
                  @else
                    <span class="flex flex-col w-full">
                  <span>{{ $work->title }}</span>
                  <span class="text-norma-gray-600 font-normal">{{ $work->title_translation }}</span>
                </span>
                  @endcan
                </x-ui.th>
              </x-ui.tr>
            @endforeach
          </x-slot:body>
        </x-ui.table>
      </div>
    </x-ui.tab-content>

  @endif


</x-ui.tabs>
