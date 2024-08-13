<x-layouts.collaborate fluid no-padding>

  <x-slot name="pageHead">
    @vite(['resources/js/collaborate/annotations.js'])
  </x-slot>

  <x-slot name="header">
    <x-turbo::frame target="_top" id="workflow-task-header" :src="route('collaborate.workflow.tasks.header', ['expression' => $expression->id])" />
  </x-slot>

  <div class="h-full overflow-hidden" x-data="{ refPage: 1 }">
    <div x-init="setTimeout(function () { window.Split(['#split-left-{{ $expression->id }}', '#split-right-{{ $expression->id }}'], { sizes: [55, 45] }); }, 1000)" class="splitter flex flex-row h-full overflow-hidden">
      <div id="split-left-{{ $expression->id }}" class="h-full">
        <div class="flex-grow grid gap-1 overflow-hidden grid-cols-2 h-full">
          <x-turbo::frame
            class="flex-grow w-full overflow-hidden col-span-2"
            id="work_expression_content"
            src="{{ route('collaborate.work-expressions.identify.show', ['expression' => $expression->id]) }}"
          >
            <x-ui.skeleton />
          </x-turbo::frame>
        </div>
      </div>

      <div id="split-right-{{ $expression->id }}" class="h-full overflow-hidden flex flex-col">
        <div class="flex-shrink-0 flex justify-between my-2 px-2">
          <div>
            @can('collaborate.corpus.content-resource.upload')
              <x-ui.modal>
                <x-slot name="trigger">
                  <x-ui.button theme="gray" styling="outline" @click="open = true">
                    <x-ui.icon name="upload" class="mr-4" size="4" />
                    {{ __('corpus.content-resource.file_upload') }}
                  </x-ui.button>
                </x-slot>

                <x-corpus.content-resource.collaborate.resource-uploader />
              </x-ui.modal>
            @endcan
          </div>
          <div>
            @can('collaborate.corpus.reference.generate-content-drafts')
              <x-ui.confirm-button :route="route('collaborate.work-expressions.identify.references.generate-drafts', [
                'expression' => $expression->id,
            ])"
                                   button-theme="gray"
                                   styling="outline"
                                   :label="__('corpus.reference.generate_updates')"
                                   method="POST"
                                   :confirmation="__('corpus.reference.generate_updates_confirm')"
                                   danger>
                <x-ui.icon name="recycle" class="mr-3" size="4" />
                {{ __('corpus.reference.generate_updates') }}
              </x-ui.confirm-button>
            @endcan
          </div>
          <div class="space-y-2 flex flex-col items-end">
            @can('collaborate.corpus.reference.delete-non-requirements')
              <x-ui.confirm-button
                  :route="route('collaborate.work-expressions.identify.references.delete-non-requirements', ['expression' => $expression->id])"
                  button-theme="negative"
                  styling="outline"
                  :label="__('corpus.reference.delete_non_requirements')"
                  method="POST"
                  :confirmation="__('corpus.reference.delete_non_requirements_confirm')"
              >
                <x-slot:formBody>
                  <input type="hidden" name="redirect_back" x-bind:value="'{{ route('collaborate.work-expressions.identify.references.index', ['expression' => $expression->id]) }}?page=' + refPage">
                </x-slot:formBody>

                <x-ui.icon name="trash" class="mr-3" size="4" />
                {{ __('corpus.reference.delete_non_requirements') }}
              </x-ui.confirm-button>
            @endcan

            @can('collaborate.corpus.reference.apply-content-drafts')
              <x-ui.confirm-button :route="route('collaborate.work-expressions.identify.references.apply-drafts', [
                'expression' => $expression->id,
            ])"
                                   button-theme="primary"
                                   styling="outline"
                                   :label="__('corpus.reference.apply_all_drafts')"
                                   method="POST"
                                   :confirmation="__('corpus.reference.apply_all_drafts_confirm')"
                                   danger>
                <x-slot:formBody>
                  <input type="hidden" name="redirect_back" x-bind:value="'{{ route('collaborate.work-expressions.identify.references.index', ['expression' => $expression->id]) }}?page=' + refPage">
                </x-slot:formBody>

                <x-ui.icon name="check" class="mr-3" size="4" />
                {{ __('corpus.reference.apply_all_drafts') }}
              </x-ui.confirm-button>
            @endcan
          </div>

        </div>

        <div class="flex-grow overflow-y-auto custom-scroll">
          <x-turbo::frame
            class="flex flex-col overflow-hidden"
            id="work_expression_references"
            src="{{ route('collaborate.work-expressions.identify.references.index', ['expression' => $expression->id]) }}"
          >
            <x-ui.skeleton />
          </x-turbo::frame>
        </div>

      </div>

    </div>
  </div>

</x-layouts.collaborate>
