<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.back-referrer-button fallback="{{ route('my.context-questions.index') }}" />

      <div class="ml-4">
        {{ $question->toQuestion() }}
      </div>
    </div>
  </x-slot>

  <x-customer.norma.norma-data-table
    :base-query="$baseQuery"
    :route="route('my.context-questions.show', ['question' => $question])"
    searchable
    :fields="['title_for_applicability', 'applicability_yes', 'applicability_no', 'applicability_unanswered']"
    actionable
    :actions="['applicability_answer_yes','applicability_answer_no']"
    :actions-route="route('my.context-questions.actions.for.question', ['question' => $question])"
    :paginate="50"
  />
</x-layouts.app>
