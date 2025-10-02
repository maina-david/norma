<div class="mt-6">
  <div>
    <span class="text-sm mt-4 text-norma-gray-500">
      {{ __('collaborators.collaborator.language') }}
    </span>
  </div>

  @php($allLanguages = \App\Support\Languages::all())

  @foreach ($resource->profile->languages as $language)
    <div class="mt-3">
      <div class="">
        {{ $allLanguages[$language->language_code]['name'] ?? $language->language_code }}:
      </div>
      <div class="
           text-sm text-norma-gray-700">
        @if ($language->proficiency)
          {{ \App\Enums\Collaborators\LanguageProficiency::lang()[$language->proficiency] ?? $language->proficiency }}
        @else
          -
        @endif
      </div>
    </div>
  @endforeach
</div>
