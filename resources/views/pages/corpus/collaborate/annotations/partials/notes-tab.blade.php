

<div class="overflow-hidden px-2 bg-white" x-show="showNotes" style="display: none;">
  <x-ui.tabs>
    <x-slot:nav>
      <x-ui.tab-nav name="work">{{ __('corpus.work_expression.work_notes') }}</x-ui.tab-nav>
      <x-ui.tab-nav name="expression">{{ __('corpus.work_expression.work_expression_notes') }}</x-ui.tab-nav>
    </x-slot:nav>

    <x-ui.tab-content name="work" class="mt-2">
      <x-turbo::frame loading="lazy" class="overflow-hidden" id="work-notes" src="{{ route('collaborate.notes.index', ['work' => $expression->work_id]) }}">
      </x-turbo::frame>
    </x-ui.tab-content>

    <x-ui.tab-content name="expression" class="mt-2">
      <x-turbo::frame loading="lazy" class="overflow-hidden" id="expression-notes" src="{{ route('collaborate.notes.index', ['work' => $expression->work_id, 'expression' => $expression->id]) }}">
      </x-turbo::frame>
    </x-ui.tab-content>
  </x-ui.tabs>
</div>




