@php
use App\Models\Ontology\CategoryType;
@endphp

@if (isset($parentId))
  <input type="hidden" name="parent_id" value="{{ $parentId }}" />
@endif

<x-ui.input
            :value="old('label', $resource->label ?? '')"
            name="label"
            label="{{ __('ontology.category.label') }}"
            required />

<x-ui.input
            :value="old('display_label', $resource->display_label ?? '')"
            name="display_label"
            label="{{ __('ontology.category.display_label') }}" />

<x-ui.input name="category_type_id"
            required
            label="{{ __('ontology.category.category_type') }}"
            :value="old('category_type_id', $resource->category_type_id ?? '')"
            type="select"
            :options="CategoryType::all()
                ->pluck('title', 'id')
                ->toArray()" />

<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.ontology.categories.index')" />
    <x-ui.button
                 type="submit"
                 theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
