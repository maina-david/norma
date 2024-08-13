<div>
  <input type="hidden" name="category_id" value="{{ $category->id }}" />

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
    <x-ui.back-button :fallback="route('collaborate.categories.descriptions.index', ['category' => $category->id])" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
