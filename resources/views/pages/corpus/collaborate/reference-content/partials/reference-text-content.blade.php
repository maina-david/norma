<div class="p-4 border-t border-norma-gray-200 relative">
  @can('collaborate.corpus.reference.update')
    <x-ui.form method="put" :action="route('collaborate.work-expressions.creation.references.content.update', ['expression' => $expression->id, 'reference' => $reference->id])">

      <div class="mb-4">
        <x-ui.input name="title" :value="$reference->refPlainText?->plain_text" :label="__('interface.title')" required />
      </div>

      <x-ui.textarea wysiwyg="full" name="content" :value="$reference->htmlContent?->cached_content" :label="__('corpus.reference.text')" />

      <div class="flex justify-end mt-4">
        <x-ui.button type="submit" styling="outline">
          {{ __('actions.save') }}
        </x-ui.button>
      </div>
    </x-ui.form>


  @else
    <div class="mt-2 norma-legislation">
      {!! $reference->htmlContent?->cached_content !!}
    </div>
  @endcan

</div>
