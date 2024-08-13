<div>
  <input type="hidden" name="assessment_item_id" value="{{ $item->id }}" />

  <x-geonames.location.location-selector
      :location="$resource->location ?? null"
      :value="old('primary_location_id', $resource->location_id ?? null)"
      :label="__('geonames.location.country')"
      name="location_id"
  />

  <x-ui.textarea
      wysiwyg="minimal"
      :label="__('compilation.context_question_description.explanation')"
      name="description"
      :value="old('description', $resource->description ?? '')" />

</div>
<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.assessment-items.show', ['assessment_item' => $item->id])" />
    <x-ui.button
        type="submit"
        theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
