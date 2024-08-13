<x-layouts.app>
  <x-slot name="header">
    <div class="flex flex-row items-center">
      <div class="mr-6">
        <x-ui.back-referrer-button fallback="{{ route('my.notify.legal-updates.index') }}" />
      </div>
      <div class="">
        {{ $update->title }}
      </div>
    </div>
  </x-slot>

  @if($update->source->hide_content ?? false)
    @include('partials.corpus.work.copyright-restricted', ['source' => $update->source, 'target' => $update->createdFromDoc->docMeta->source_url ?? null])
  @else
    <x-notify.legal-update.update-preview :legal-update="$update" />

    @if ($update->source?->source_content)
      <div class="mt-1 text-sm italic">
        {!! $update->source?->source_content !!}
      </div>
    @endif
  @endif

</x-layouts.app>
