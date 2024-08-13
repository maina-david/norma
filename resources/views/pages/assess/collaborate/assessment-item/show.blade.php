<x-ui.tabs :active="request('tab', 'details')">
  <x-slot name="nav">
    <x-ui.tab-nav name="details">{{ __('assess.assessment_item.show_title') }}</x-ui.tab-nav>
    <x-ui.tab-nav name="explanations">{{ __('nav.explanations') }}</x-ui.tab-nav>
    <x-ui.tab-nav name="guidance">{{ __('assess.guidance_note.guidance_notes') }}</x-ui.tab-nav>
    <x-ui.tab-nav name="context">{{ __('compilation.context_question.index_title') }}</x-ui.tab-nav>
  </x-slot>

  <x-ui.tab-content name="details">
    @include('pages.assess.collaborate.assessment-item.partials.tabs.details')
  </x-ui.tab-content>

  <x-ui.tab-content name="explanations">
    @include('pages.assess.collaborate.assessment-item.partials.tabs.descriptions')
  </x-ui.tab-content>

  <x-ui.tab-content name="guidance">
    @include('pages.assess.collaborate.assessment-item.partials.tabs.guidance')
  </x-ui.tab-content>

  <x-ui.tab-content name="context">
    @include('pages.assess.collaborate.assessment-item.partials.tabs.context')
  </x-ui.tab-content>

</x-ui.tabs>
