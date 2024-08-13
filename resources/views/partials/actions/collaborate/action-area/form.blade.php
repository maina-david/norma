@php use App\Enums\Ontology\CategoryType; @endphp
<div class="">
  <div class="w-full">
    <x-ontology.category-selector
      :value="old('control_category_id', $resource->control_category_id ?? null)"
      name="control_category_id"
      label="{{ __('actions.action_area.control') }}"
      :types="[CategoryType::CONTROL->value]"
    />
  </div>

  <div class="w-full">
    <x-ontology.category-selector
      :value="old('subject_category_id', $resource->subject_category_id ?? null)"
      name="subject_category_id"
      label="{{ __('actions.action_area.subject') }}"
      :types="[CategoryType::SUBJECT->value, CategoryType::ACTIVITY->value]"
    />
  </div>
</div>


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.action-areas.index')"/>
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
