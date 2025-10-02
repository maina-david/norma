<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.back-referrer-button fallback="{{ route('my.context-questions.index') }}" />

      <div class="ml-4">{{ __('compilation.context_question.applicability_for_norma', ['norma' => $norma->title]) }}</div>
    </div>

    <div class="text-sm text-norma-gray-500 mt-4 font-normal">
      <div>{{ __('compilation.context_question.applicability_for_norma_description_1') }}</div>
      <div>{{ __('compilation.context_question.applicability_for_norma_description_2') }}</div>
    </div>

    <div>
      <div class="mt-8 font-semibold">
        {{ $question->toQuestion() }}
      </div>

      @if($explanation)
        <div class="text-sm text-norma-gray-500 mt-2 font-normal max-w-screen-75">
          <x-ui.collaborate.wysiwyg-content :content="$explanation->description" />
        </div>
      @endif
    </div>
  </x-slot>


  <div class="mb-10">
    <div class="w-full bg-white pb-8 px-4 sm:p-10 shadow" id="context_question_{{ $question->id }}_table">
      @include('pages.compilation.my.context-question.detail-table')
    </div>
  </div>

  @include('pages.compilation.my.context-question.relations')

</x-layouts.app>
