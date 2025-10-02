@php use App\Services\Storage\MimeTypeManager; @endphp
<x-layouts.app>
  <x-slot name="header">
    @include('pages.compilation.my.context-question.applicability-header')
  </x-slot>

  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">

      <x-ui.modal>
        <x-slot name="trigger">
          <x-ui.button
            type="button"
            @click="open = true"
            styling="outline"
            theme="primary"
          >
            {{ __('compilation.context_question.upload_excel') }}
          </x-ui.button>
        </x-slot>

        <div class="max-w-xl w-screen">
          <div id="download-progress">
            <x-ui.form method="post" :action="route('my.compilation.context-questions.import.upload')" enctype="multipart/form-data">
              <x-ui.input
                type="file"
                name="file"
                label="{{ __('corpus.content-resource.file_upload') }}"
                accept="{{ implode(',', app(MimeTypeManager::class)->getAcceptedExcelMimes()) }}"
              />

              <div class="flex justify-end">
                <x-ui.button type="submit" class="mt-10">{{ __('actions.upload') }}</x-ui.button>
              </div>
            </x-ui.form>
          </div>
        </div>
      </x-ui.modal>

      <x-ui.button
        type="link"
        href="{{ route('my.compilation.context-questions.export') }}"
        styling="outline"
        class="ml-3"
        theme="primary"
      >
        {{ __('compilation.context_question.edit_offline') }}
      </x-ui.button>

    </div>

    <div class="pt-4 flex justify-end">
      <a target="_blank"
         href="https://success.norma.com/en/knowledge/online-applicability">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>

  <x-compilation.context-question.my.layout bordered>

    <x-compilation.context-question.context-question-data-table
        :base-query="$baseQuery"
        :route="route('my.context-questions.index')"
        searchable
        :fields="$norma ? ['title', 'yes_radio', 'no_radio', 'maybe_radio'] : ['title', 'normas_count', 'yes_count', 'no_count', 'maybe_count']"
        actionable
        :actions="[
      'applicability_answer_yes',
      'applicability_answer_no',
    ]"
        :actions-route="route('my.context-questions.actions')"
        :paginate="50"
        filterable
        :filters="['categories', 'answer']"
        side-filters
        submit-to-filter
    />

  </x-compilation.context-question.my.layout>
</x-layouts.app>
