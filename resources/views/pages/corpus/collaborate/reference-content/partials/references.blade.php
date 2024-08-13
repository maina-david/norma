<div class="flex flex-col overflow-hidden bg-white" x-data="{ selected: [], toggleSelection: function (item) { var ind = this.selected.indexOf(item); if (ind === -1) { this.selected.push(item); } else { this.selected.splice(ind, 1); } } }">
  <div class="w-full pr-12 h-28">
    <div class="flex-shrink-0 flex items-center justify-end w-full" x-show="selected.length > 0">
      @can('collaborate.corpus.reference.create')
        <x-ui.form method="POST" :action="route('collaborate.work-expressions.creation.references.indent', ['expression' => $expression->id, 'reference' => 'bulk'])">
          <input type="hidden" x-bind:value="selected.join(',')" name="references">
          <x-ui.button @click.stop="" type="submit" styling="flat" theme="primary" class="tippy" data-tippy-content="{{ __('corpus.reference.increase_level') }}">
            <x-ui.icon name="indent" size="4" />
          </x-ui.button>
        </x-ui.form>

        <x-ui.form method="POST" :action="route('collaborate.work-expressions.creation.references.outdent', ['expression' => $expression->id, 'reference' => 'bulk'])">
          <input type="hidden" x-bind:value="selected.join(',')" name="references">
          <x-ui.button @click.stop="" type="submit" styling="flat" theme="primary" class="tippy" data-tippy-content="{{ __('corpus.reference.decrease_level') }}">
            <x-ui.icon name="outdent" size="4" />
          </x-ui.button>
        </x-ui.form>
      @endcan
    </div>
  </div>
  <div class="flex-grow w-full overflow-hidden">
    <div class="h-full overflow-y-auto custom-scroll space-y-1 pr-1">
      @foreach($references as $reference)
        @include('pages.corpus.collaborate.reference-content.partials.reference-card', ['active' => $reference === $activeReference])
      @endforeach
    </div>
  </div>

  <div class="flex-shrink-0 bg-white px-2 pb-1">
    {{ $references->links() }}
  </div>
</div>


