@props(['selected' => [], 'label' => __('ontology.user_tag.tags')])

@php
use Illuminate\Support\Arr;
@endphp

<x-ui.input name="user_tags[]"
            label="{{ $label }}"
            placeholder="{{ __('ontology.user_tag.select_tag_or_enter') }}"
            :value="old('user_tags', Arr::pluck($selected, 'id'))"
            type="select"
            multiple
            data-load-create="true"
            :options="!empty($selected) ? $selected->pluck('title', 'id')->toArray() : []"
            class="remote-select"
            :data-load="route('my.user-tags.index.json')"
            data-load-start-searching="1" />
