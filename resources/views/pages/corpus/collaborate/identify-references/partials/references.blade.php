<div class="flex flex-col overflow-hidden bg-white" x-init="refPage = {{ request('page', 1) }}">
  <div class="flex-grow w-full overflow-hidden">
    <div class="h-full overflow-y-auto custom-scroll space-y-1 pr-1">
      @forelse ($references as $reference)
        @include('pages.corpus.collaborate.identify-references.partials.reference-card', [
            'active' => $reference->id === $activeReferenceId,
        ])
      @empty
        <div class="p-10 flex justify-center">
          <x-ui.form method="POST" :action="route('collaborate.work-expressions.identify.references.insert-below', [
              'expression' => $expression->id,
              'reference' => $workReference->id,
          ])">
            <x-ui.button @click.stop="" type="submit" theme="primary" class="tippy ml-5"
                         data-tippy-content="{{ __('corpus.reference.create_first_reference') }}">
              <x-ui.icon name="plus" size="4" class="mr-2" />
              {{ __('corpus.reference.create_first_reference') }}

            </x-ui.button>
          </x-ui.form>
        </div>
      @endforelse
    </div>
  </div>

  <div class="flex-shrink-0 bg-white px-2 pb-1">
    {{ $references->links() }}
  </div>
</div>
