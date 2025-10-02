<div class="h-full overflow-hidden flex flex-col bg-white">
  <div class="flex-grow text-sm pt-2 overflow-y-auto custom-scroll">
    @foreach($references as $reference)
      <div
        x-data="{selectors: {{ json_encode($reference->refSelector?->selectors ?? []) }} }"
        @click="function () { evaluateSelector($event.target.closest('.toc-ref'), this.selectors) }"
        class="px-2 py-1 border-b border-norma-gray-100 cursor-pointer toc-ref"
        id="toc{{ $reference->id }}"
        data-volume="{{ $reference->volume }}"
      >
        <x-ui.form method="GET" :action="route('collaborate.work-expressions.references.activate', ['expression' => $expression->id, 'reference' => $reference->id])">
          <button class="text-left font-semibold" type="submit" style="margin-left:{{ $reference->level }}rem">
            {{ $reference->refPlainText?->plain_text }}
          </button>
        </x-ui.form>
      </div>
    @endforeach
  </div>

  <div class="flex-shrink-0 px-2 pt-1 no-pagination-text pagination-tight overflow-x-auto custom-scroll">
    {{ $references->withQueryString()->links() }}
  </div>

</div>
