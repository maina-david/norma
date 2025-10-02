@php
  use App\Enums\Workflows\TaskStatus;
  $user = auth()->user();
  $canEdit = $user->can('collaborate.notify.legal-update.manage-without-task') || TaskStatus::inProgress()->is($task->task_status ?? null);
@endphp
<x-ui.tabs>
  <x-slot name="nav">
    <x-ui.tab-nav name="details">{{ __('corpus.work.types.legal_update') }}</x-ui.tab-nav>

    @if($resource->contentResource ?? false)
      <x-ui.tab-nav name="related">{{ __('notify.legal_update.related_document') }}</x-ui.tab-nav>
    @endif

    @if($resource->createdFromDoc?->firstContentResource?->textContentResource ?? false)
      <x-ui.tab-nav name="text">{{ __('corpus.doc.plain_text') }}</x-ui.tab-nav>
    @endif
  </x-slot>

  <x-ui.tab-content name="details">
    <div>
      @if($canEdit && !($noUpload ?? false))
        @can('collaborate.notify.legal-update.upload-related-document')
          <x-ui.file-selector :required="!($resource->id ?? false)" name="content_resource_file"
                              :label="__('notify.legal_update.upload_related_document')"/>

          @error('content_resource_file')
          <div class="text-sm text-red-400">{{ $message }}</div>
          @enderror
        @endcan
      @endif

      @if ($canEdit && $user->canAny(['collaborate.notify.legal-update.update', 'collaborate.notify.legal-update.create']))
        @include('partials.notify.collaborate.legal-update.editable-form')
      @else
        @include('partials.notify.collaborate.legal-update.show-form')
      @endif


      @if($resource->automated_update_report ?? false)
        <div class="pb-4">
          <x-ui.show-field :label="__('notify.legal_update.automated_highlights')">
            <x-ui.collaborate.wysiwyg-content :content="$resource->automated_highlights ?? '-'"/>
          </x-ui.show-field>
        </div>
      @endif

      @if($canEdit && $user->can('collaborate.notify.legal-update.update-highlights'))
        <x-ui.textarea
          wysiwyg="minimal"
          :label="__('notify.legal_update.highlights')"
          name="highlights"
          :value="old('highlights', $resource->highlights ?? '')"
        />

      @else
        <x-ui.show-field :label="__('notify.legal_update.highlights')">
          <x-ui.collaborate.wysiwyg-content :content="$resource->highlights ?? '-'"/>
        </x-ui.show-field>
      @endif


      @if($resource->automated_update_report ?? false)
        <div class="pb-4">
          <x-ui.show-field :label="__('notify.legal_update.automated_summary_of_highlights')">
            <x-ui.collaborate.wysiwyg-content :content="$resource->automated_update_report ?? '-'"/>
          </x-ui.show-field>
        </div>
      @endif

      @if($canEdit && $user->can('collaborate.notify.legal-update.update-summary-of-highlights'))
        <x-ui.textarea
          wysiwyg="minimal"
          :label="__('notify.legal_update.summary_of_highlights')"
          name="update_report"
          :value="old('update_report', $resource->update_report ?? '')"
        />
      @else
        <x-ui.show-field :label="__('notify.legal_update.summary_of_highlights')">
          <x-ui.collaborate.wysiwyg-content :content="$resource->update_report ?? '-'"/>
        </x-ui.show-field>
      @endif



      @if($canEdit && $user->can('collaborate.notify.legal-update.update-changes-to-register'))
        <x-ui.textarea
          x-ref="changesToRegisterField"
          wysiwyg="minimal"
          :label="__('notify.legal_update.changes_to_requirements')"
          name="changes_to_register"
          :value="old('changes_to_register', $resource->changes_to_register ?? '')"
        />

        <div>
          <x-lookups.canned-response-selector field="changes_to_register"
                                              @change="tinymce.get('changes_to_register').setContent('<p>' + $el.value + '</p>');"/>
        </div>
      @else
        <x-ui.show-field :label="__('notify.legal_update.changes_to_requirements')">
          <x-ui.collaborate.wysiwyg-content :content="$resource->changes_to_register ?? '-'"/>
        </x-ui.show-field>
      @endif

      @if($canEdit)
        <div class="mt-8">
          <x-ui.input
            class="app-action-selection"
            type="checkbox"
            id="late"
            name="late"
            :label="__('notifications.collaborate.update_is_late')"
            :value="old('late',$resource->late ?? 0)" />
        </div>
      @endif

    </div>
  </x-ui.tab-content>

  @if($resource->contentResource ?? false)
    <x-ui.tab-content name="related" class="h-screen-75 overflow-y-auto norma-legislation custom-scroll">
      @if($resource->contentResource->mime_type !== 'text/html')
        <embed class="w-full h-full" src="{{ asset($resource->contentResource->path) }}">
      @else
        <turbo-frame
          id="content-full-text-{{ $legalUpdate->id }}"
          src="{{ route('collaborate.content-resources.show', ['resource' => $legalUpdate->contentResource->id, 'targetId' => $legalUpdate->id,]) }}" />
      @endif
    </x-ui.tab-content>
  @endif


  @if($resource->createdFromDoc?->firstContentResource?->textContentResource ?? false)
    <x-ui.tab-content name="text" class="h-screen-75">
      <div
        class="h-full overflow-y-auto custom-scroll"
        data-lazy-source="{{ asset($resource->createdFromDoc->firstContentResource->textContentResource->path) }}"
      ></div>
    </x-ui.tab-content>
  @endif

</x-ui.tabs>


@if(!($noActions ?? false))
  <x-slot name="footer">
    <div></div>
    <div>
      <x-ui.back-button :fallback="route('collaborate.legal-updates.index')"/>
      @can('collaborate.notify.legal-update.publish')
        <x-ui.button
          type="submit"
          theme="primary"
          name="and_close"
          value="true"
          styling="outline"
        >{{ __('actions.save_and_close') }}</x-ui.button>
      @endcan
      <x-ui.button
        type="submit"
        theme="primary"
      >{{ __('actions.save') }}</x-ui.button>
    </div>
  </x-slot>
@endif

