@php
use App\Services\Storage\MimeTypeManager;
@endphp
<x-layouts.settings>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="question-circle" size="10" class="mr-5 text-norma-gray-600" /> {{ __('settings.nav.applicability') }}
    </div>
  </x-slot>

  <x-ui.card>
    <x-ui.form method="post" :action="route('my.compilation.context-questions.import.upload')" enctype="multipart/form-data">
      <x-ui.input type="file" name="file"
                  accept="{{ implode(',', app(MimeTypeManager::class)->getAcceptedExcelMimes()) }}" />
      <div class="flex justify-end">
        <x-ui.button type="submit" class="mt-10">{{ __('actions.upload') }}</x-ui.button>
      </div>
    </x-ui.form>
  </x-ui.card>
</x-layouts.settings>
