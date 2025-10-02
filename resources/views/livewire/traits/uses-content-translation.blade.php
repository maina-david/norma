@if ($organisation->translation_enabled)
  <div class="flex justify-end">
    <x-requirements.summary.lang-selector
        :original="$originalLanguage"
        :tomselect="false"
        name="language"
        wire:change="translate($event.target.value)"
    />
  </div>
@endif

<div class="norma-legislation p-3" wire:loading.class="text-norma-gray-300">
  {!! $content !!}
</div>
