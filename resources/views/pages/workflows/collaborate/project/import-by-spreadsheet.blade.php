<x-layouts.collaborate>
  <x-ui.card>
    @include('pages.workflows.collaborate.project.partials.project-nav')

    <div class="mt-20">

      <x-ui.form method="POST" :action="route('collaborate.projects.ingest.import.excel', ['project' => $project->id])" enctype="multipart/form-data">
        <div class="">
          <div>
            Import into: {{ $project->title }}
          </div>

          <div class="my-5">
            <x-ui.input required :label="__('arachno.crawler.source')" name="source_id" type="select" :options="$sourceOptions"/>
          </div>

          <x-ui.language-selector
            :value="old('language_code', $project->language_code)"
            name="language_code"
            :label="__('workflows.project.language_code')"
            required
          />

          <x-geonames.location.location-selector
            data-tomselect-no-reinitialise
            required
            :location="$project->location"
            :value="old('location_id', $project->location->id ?? null)"
            :label="__('geonames.location.jurisdiction')"
            name="location_id"
            class="w-44"
          />

          <x-ui.input required :label="__('storage.setup.upload_file')" name="file" type="file" accept=".xlsx"/>

          <div class="mt-5">
            <x-ui.button type="submit" theme="primary">{{ __('actions.import') }}</x-ui.button>
          </div>

      </x-ui.form>
    </div>
  </x-ui.card>


</x-layouts.collaborate>
