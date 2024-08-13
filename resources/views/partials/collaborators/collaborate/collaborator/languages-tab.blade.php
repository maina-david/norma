<div class="mt-6" x-data="{ items: [], start: {{ count(old('languages', $resource->profile->languages->toArray())) }} }">

  <div class="py-4">
    <x-ui.button styling="outline" class="flex-shrink-0 ml-4 flex items-center" @click="items.push((items[items.length - 1] ? items[items.length - 1] : start) + 1)">
      <x-ui.icon name="plus" />
      <span class="ml-2">{{ __('collaborators.add_language') }}</span>
    </x-ui.button>
  </div>

@foreach ($resource->profile->languages as $index => $language)
  <div class="mt-3 flex items-end lang">
    <div class="flex-grow grid grid-cols-2 gap-2">
      <x-ui.language-selector :tomselect="false" :value="old('languages.{{ $index }}', $language->language_code)" name="languages[{{ $index }}]" label="{!! __('collaborators.language_proficiency.title') !!}" required />
      <x-ui.input :tomselect="false" type="select" :value="old('proficiency.{{ $index }}', $language->proficiency)" name="proficiency[{{ $index }}]" :label="__('collaborators.language_proficiency.proficiency')" :options="\App\Enums\Collaborators\LanguageProficiency::forSelector()" />
    </div>

    <x-ui.button styling="outline" theme="negative" class="flex-shrink-0 ml-4 mb-1" @click="$event.target.closest('.lang').remove()">
      <x-ui.icon name="trash-alt" />
    </x-ui.button>
  </div>
@endforeach

  <template x-for="(item,index) in items" :key="item">
    <div class="mt-3 flex items-end lang">
      <div class="flex-grow grid grid-cols-2 gap-2">
        <x-ui.language-selector :tomselect="false" x-bind:name="`languages[${item}]`" name="languages" label="{!! __('collaborators.language_proficiency.title') !!}" required />
        <x-ui.input :tomselect="false" type="select" x-bind:name="`proficiency[${item}]`" name="proficiency" :label="__('collaborators.language_proficiency.proficiency')" :options="\App\Enums\Collaborators\LanguageProficiency::forSelector()" />
      </div>

      <x-ui.button styling="outline" theme="negative" class="flex-shrink-0 ml-4 mb-1" @click="items.splice(index, 1)">
        <x-ui.icon name="trash-alt" />
      </x-ui.button>
    </div>
  </template>

</div>
