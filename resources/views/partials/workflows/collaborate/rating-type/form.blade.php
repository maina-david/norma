<x-ui.input
    :value="old('name', $resource->name ?? '')"
    name="name"
    label="{{ __('workflows.rating_type.name') }}"
    required
/>

<x-ui.input
    :value="old('description', $resource->description ?? '')"
    name="description"
    label="{{ __('workflows.rating_type.description') }}"
/>

<x-ui.input
    :value="old('icon', $resource->icon ?? '')"
    name="icon"
    label="{{ __('workflows.rating_type.icon') }}"
    required
/>

<x-ui.input
    :value="old('course_link', $resource->course_link ?? '')"
    name="course_link"
    label="{{ __('workflows.rating_type.course_link') }}"
/>

<x-slot name="footer">
    <div></div>
    <div>
        <x-ui.back-button :fallback="route('collaborate.rating-types.index')" />
        <x-ui.button
            type="submit"
            theme="primary"
        >{{ __('actions.save') }}</x-ui.button>
    </div>
</x-slot>
