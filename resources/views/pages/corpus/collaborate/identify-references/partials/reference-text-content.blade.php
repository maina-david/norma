<div class="p-4 border-t border-libryo-gray-200 relative">
  @if (auth()->user()->can('collaborate.corpus.reference.update') && $reference->contentDraft)
    <x-ui.form method="put" :action="route('collaborate.work-expressions.identify.references.content.update', [
        'expression' => $expression->id,
        'reference' => $reference->id,
    ])">

      <div class="mb-4 notranslate">
        <x-ui.input name="title" :value="$reference->contentDraft->title" :label="__('interface.title')" required />
      </div>

      <div class="notranslate" x-data="{ show: false }" x-init="setTimeout(() => show = true, 400)"
           x-bind:style="show ? 'visibility: visible;' : 'visibility: hidden;'">
        <x-ui.textarea wysiwyg="content" name="content"
                       :value="$reference->contentDraft->html_content" :label="__('corpus.reference.text')" />
      </div>


      @if (!$reference->requirementDraft && !$reference->refRequirement)
        <div class="my-10">
          <x-ui.input type="checkbox" name="request_requirement" :label="__('corpus.reference.has_requirements')" />
        </div>
      @endif


      <div class="flex justify-between mt-4">

        <x-ui.modal>
          <x-slot name="trigger">
            <x-ui.button theme="gray" styling="outline" @click="open = true">
              <x-ui.icon name="eye" class="mr-4" size="4" />
              {{ __('corpus.reference.preview') }}
            </x-ui.button>
          </x-slot>

          <turbo-frame id="reference_{{ $reference->id }}_content_draft_preview"
                       src="{{ route('collaborate.work-expressions.identify.references.preview-draft', ['reference' => $reference->id]) }}"
                       loading="lazy">
          </turbo-frame>
        </x-ui.modal>


        <x-ui.button type="submit" styling="outline">
          {{ __('actions.save') }}
        </x-ui.button>
      </div>
    </x-ui.form>
  @else
    <div class="mt-2 libryo-legislation">
      {!! $reference->htmlContent?->cached_content !!}
    </div>
  @endif


  @if ($reference->contentVersions->isNotEmpty())
    <div class="mt-10 font-semibold mb-3">
      {{ trans_choice('corpus.reference_content_version.version_count', $reference->contentVersions->count(), ['value' => $reference->contentVersions->count()]) }}:
    </div>
    @foreach ($reference->contentVersions as $version)
      <div x-data="{ show: false }" class="py-1">
        <x-ui.card class="cursor-pointer border border-libryo-gray-200 text-primary" @click="show = !show">
          <span class="font-semibold">{{ __('interface.date_created') }}:</span> {{ $version->created_at }}
        </x-ui.card>
        <x-ui.card x-show="show" class="border border-libryo-gray-200 mt-0.5">
          @include('pages.corpus.collaborate.identify-references.partials.preview-content', [
              'title' => $version->title,
              'htmlContent' => $version->versionContentHtml?->html_content,
          ])
        </x-ui.card>
      </div>
    @endforeach
  @endif

</div>
