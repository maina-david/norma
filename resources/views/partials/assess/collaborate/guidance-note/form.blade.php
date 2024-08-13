@php
if ($resource) {
    $resource->location = isset($resource->location) ? $resource->location : null;

    if (isset($resource->location_id) && !isset($resource->location)) {
        $resource->location = $resource->load('location');
    }
}

@endphp

<x-geonames.location.location-selector :route="route('collaborate.locations.json.index')"
                                       :location="old('location_id', $resource->location ?? null)" />

<x-ui.textarea wysiwyg="basic"
               :value="old('description', $resource->description ?? '')"
               name="description"
               label="{{ __('assess.guidance_note.description') }}" />

<div class="flex justify-end w-full mt-4">
  <div></div>
  <div>

    @if ($close ?? false)
      <x-ui.button class="mr-4" @click="open = false">{{ __('actions.close') }}</x-ui.button>
    @else
      <x-ui.back-button :fallback="route('collaborate.assessment-items.index')" />
    @endif
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</div>
