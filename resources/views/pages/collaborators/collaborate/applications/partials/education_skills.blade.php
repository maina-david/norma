@php use App\Enums\Collaborators\LanguageProficiency; @endphp
<div class="flex flex-col md:flex-row">
  <div class="grow md:mr-4 w-full md:w-1/2">
    <x-ui.input name="university" label="{{ __('collaborators.university') }}" required/>

    <x-ui.input name="year_of_study" label="{{ __('collaborators.year_of_study') }}" type="select" :options="$study"
                required/>

    <x-ui.input name="linkedin_url" label="{{ __('collaborators.linked_in_profile') }}" type="url"/>

  </div>

  <div class="grow md:ml-4 w-full md:w-1/2">
    <x-ui.input name="education_transcript" label="{{ __('collaborators.education_transcript') }}" type="file"
                required/>

    <x-ui.input name="curriculum_vitae" label="{{ __('collaborators.curriculum_vitae') }}" type="file" required/>
  </div>
</div>

<div class="mt-4">
  <x-ui.label for="skills" class="mb-1"><span
        class="text-red-400">*</span>{{ __('collaborators.skill_to_develop') }}</x-ui.label>
  <div class="flex flex-wrap">
    <div class="w-full mb-2 sm:w-1/2 md:w-1/3">
      <x-ui.input name="skills[Legal writing]" label="{{ __('collaborators.legal_writing') }}" type="checkbox"/>
    </div>
    <div class="w-full mb-2 sm:w-1/2 md:w-1/3">
      <x-ui.input name="skills[Legal editing]" label="{{ __('collaborators.legal_editing') }}" type="checkbox"/>
    </div>
    <div class="w-full mb-2 sm:w-1/2 md:w-1/3">
      <x-ui.input name="skills[Legal blogging]" label="{{ __('collaborators.legal_blogging') }}" type="checkbox"/>
    </div>
    <div class="w-full mb-2 sm:w-1/2 md:w-1/3">
      <x-ui.input name="skills[Legal analysis]" label="{{ __('collaborators.legal_analysis') }}" type="checkbox"/>
    </div>
    <div class="w-full mb-2 sm:w-1/2 md:w-1/3">
      <x-ui.input name="skills[Legal research]" label="{{ __('collaborators.legal_research') }}" type="checkbox"/>
    </div>
    <div class="w-full mb-2 sm:w-1/2 md:w-1/3">
      <x-ui.input name="skills[Legal data capture]" label="{{ __('collaborators.legal_data_capture') }}"
                  type="checkbox"/>
    </div>
  </div>
</div>

<div class="mt-4" x-data="{ languages: []}">
  <div class="flex items-center">
    <div class="font-semibold mr-4">{{ __('collaborators.collaborator.languages') }}</div>
    <div>
      <x-ui.button type="button" styling="outline" size="sm" @click="languages.push(Math.random())">
        <x-ui.icon name="plus" />
      </x-ui.button>
    </div>
  </div>

  <div class="grid grid-cols-5 gap-2">
    <div class="md:col-span-3">
      <x-ui.language-selector
          name="language[0]"
          label="{!! __('collaborators.language_proficiency.title') !!}"
          required
          :tomselect="false"
      />
    </div>

    <div class="md:col-span-2">
      <x-ui.input
          :label="__('collaborators.language_proficiency.proficiency')"
          required
          name="proficiency[0]"
          type="select"
          :options="LanguageProficiency::forSelector()"
          :tomselect="false"
      />
    </div>
  </div>

  <template x-for="(language, index) in languages" :key="language">
    <div class="grid grid-cols-5 gap-2">
    <div class="md:col-span-3">
      <x-ui.language-selector
          name="languages[]"
          x-bind:name="'language[' + (index + 1) +']'"
          label="{!! __('collaborators.language_proficiency.title') !!}"
          :tomselect="false"
          required
      />
    </div>

    <div class="md:col-span-2 flex items-end">
      <div class="flex-grow">
        <x-ui.input
            :label="__('collaborators.language_proficiency.proficiency')"
            name="proficiency"
            x-bind:name="'proficiency[' + (index + 1) +']'"
            type="select"
            :options="LanguageProficiency::forSelector()"
            :tomselect="false"
            required
        />
      </div>

      <div class="ml-2 pb-0.5">
        <x-ui.button styling="outline" type="button" theme="negative" @click="languages.splice(index, 1)">
          <x-ui.icon name="trash" />
        </x-ui.button>
      </div>
    </div>
    </div>
  </template>

</div>
