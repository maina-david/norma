<x-layouts.collaborate fluid no-padding>

  <x-slot name="header">
    @include('pages.corpus.collaborate.annotations.partials.header')
  </x-slot>

  <div class="flex flex-col overflow-hidden">
    <x-turbo::frame class="flex-grow w-full overflow-hidden" id="work_expression_content" src="{{ route('collaborate.work-expressions.volume.show', ['expression' => $expression->id, 'volume' => 1, ...request()->query()]) }}">
    </x-turbo::frame>
  </div>

</x-layouts.collaborate>
