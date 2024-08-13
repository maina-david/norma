@props(['label', 'name' => 'project_id', 'required' => false, 'value' => null, 'multiple' => false])

@php
    $options = \App\Models\Workflows\Project::orderBy('title')->get(['title', 'id']);
    $options = ['' => '-'] + $options->pluck('title', 'id')->toArray();
@endphp

<x-ui.input
            :multiple="$multiple"
            :name="$name"
            :required="$required"
            label="{{ $label ?? __('workflows.project.index_title') }}"
            :value="$value ?? ''"
            type="select"
            :options="$options"
            @tom-changed="$dispatch('changed', $event.detail)"
            {{ $attributes }} />
