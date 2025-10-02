<div>
  @foreach ($row->profile->languages as $lang)
    <div class="flex items-center">
      <div class="mr-2">
        {{ $languages[$lang->language_code]['name'] ?? $lang->language_code }}
      </div>
      @if ($lang->proficiency)
        <div class="text-xs text-norma-gray-700 border border-primary px-1 rounded">
          {{ \App\Enums\Collaborators\LanguageProficiency::lang()[$lang->proficiency] ?? $lang->proficiency }}
        </div>
      @endif
    </div>
  @endforeach
</div>
