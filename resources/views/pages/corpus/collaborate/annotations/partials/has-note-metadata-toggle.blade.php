@can('viewAny', App\Models\Workflows\Note::class)
  <button
      id="has_note_indicator"
      data-tippy-content="{{ __('corpus.work_expression.show_notes') }}"
      x-bind:class="showNotes ? 'border-primary text-primary' : 'border-transparent'"
      class="tippy h-8 w-8 flex items-center justify-center border-t-2{{ $hasNotes ? ' bg-primary rounded-full text-white hover:text-secondary' : ' hover:text-primary' }}"
      @click="showToC = false;showNotes = !showNotes"
  >
    <x-ui.icon name="notes"/>
  </button>
@endcan
