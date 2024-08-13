<div class="">
  <div>
    <x-ui.input
                :value="old('title', $resource->title ?? '')"
                name="title"
                label="{{ __('arachno.crawler.title') }}"
                required />

    <x-ui.input
                :value="old('slug', $resource->slug ?? '')"
                name="slug"
                label="{{ __('arachno.crawler.slug') }}"
                required />

    <x-ui.input type="textarea" :value="old('description', $resource->description ?? '')" name="description"
                label="{{ __('interface.description') }}" />

    <x-ui.input
                :value="old('class_name', $resource->class_name ?? '')"
                name="class_name"
                label="{{ __('arachno.crawler.class_name') }}" />


    <x-arachno.source.source-selector
                                      name="source_id"
                                      :label="__('arachno.crawler.source')"
                                      :value="old('source_id', $resource->source_id ?? null)"
                                      required
                                      allow-empty />

    <x-ui.input
                :value="old('schedule_new_works', $resource->schedule_new_works ?? '')"
                name="schedule_new_works"
                label="{{ __('arachno.crawler.schedule_new_works') }}" />

    <x-ui.input
                :value="old('schedule_changes', $resource->schedule_changes ?? '')"
                name="schedule_changes"
                label="{{ __('arachno.crawler.schedule_changes') }}" />

    <x-ui.input
                :value="old('schedule_updates', $resource->schedule_updates ?? '')"
                name="schedule_updates"
                label="{{ __('arachno.crawler.schedule_updates') }}" />

    <x-ui.input
                :value="old('schedule_full_catalogue', $resource->schedule_full_catalogue ?? '')"
                name="schedule_full_catalogue"
                label="{{ __('arachno.crawler.schedule_full_catalogue') }}" />

    <div class="mt-4">
      <x-ui.input
                  type="checkbox"
                  :value="(bool) old('needs_browser', $resource->needs_browser ?? false)"
                  name="needs_browser"
                  label="{{ __('arachno.crawler.needs_browser') }}" />
    </div>

    <div class="mt-4">
      <x-ui.input
                  type="checkbox"
                  :value="(bool) old('enabled', $resource->enabled ?? false)"
                  name="enabled"
                  label="{{ __('arachno.crawler.enabled') }}" />
    </div>

    <div class="mt-4">
      <x-ui.input
                  type="checkbox"
                  :value="(bool) old('can_fetch', $resource->can_fetch ?? false)"
                  name="can_fetch"
                  label="{{ __('arachno.crawler.can_fetch') }}" />
    </div>

  </div>
</div>

<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.arachno.crawlers.index')" />
    <x-ui.button
                 type="submit"
                 theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
