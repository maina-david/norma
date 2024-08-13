

<x-layouts.collaborate fluid no-padding>
  <x-slot name="pageHead">
    @vite(['resources/js/collaborate/document-editor.js'])
  </x-slot>

  <x-slot name="header">
    <x-turbo::frame target="_top" id="workflow-task-header" :src="route('collaborate.workflow.tasks.header', ['expression' => $expression->id])" />
  </x-slot>

  <x-ui.form class="h-full" method="PUT" :action="route('collaborate.document-editor.update', ['expression' => $expression->id, 'task' => $task?->id, 'volume' => $pagination->currentPage()])">
    <div class="h-full flex flex-col">

      <div class="flex-shrink-0 flex justify-between items-center p-2">
        <div class="flex items-center space-x-4">
          <div class="flex flex-col w-96">
            <x-corpus.work.legal-doc-source-selector />
          </div>

          <div>
            <x-ui.modal>
              <x-slot:trigger>
                <x-ui.button @click="open = true" styling="outline">
                  {{ __('corpus.work_expression.file_browser') }}
                </x-ui.button>
              </x-slot:trigger>

              <x-turbo::frame id="document_file_browser" :src="route('collaborate.document-editor.file-browser.show', ['expression' => $expression->id])" loading="lazy">
              </x-turbo::frame>

            </x-ui.modal>
          </div>
        </div>

        <div>
          <x-ui.button type="submit" styling="outline" theme="primary">{{ __('actions.save') }}</x-ui.button>
        </div>
      </div>

      <div class="flex-grow">
        <textarea data-current="{{ $pagination->currentPage() }}" data-last="{{ $pagination->lastPage() }}" name="content" id="libryo-document-editor" class="hidden h-full w-full">{{ $pagination->first() }}</textarea>
      </div>

      <div class="flex-shrink-0 p-2">
        {{ $pagination->links() }}
      </div>
    </div>
  </x-ui.form>


</x-layouts.collaborate>
