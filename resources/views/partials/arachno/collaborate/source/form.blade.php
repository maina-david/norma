@php
    use App\Models\Auth\User;$locations = isset($resource) ? $resource->locations()->pluck('title', 'id')->toArray() : [];
@endphp
<div class="grid md:grid-cols-2 gap-4">
    <div>
        <x-ui.input
            :value="old('title', $resource->title ?? '')"
            name="title"
            label="{{ __('arachno.source.title') }}"
            required
        />

        <x-ui.input
            :value="old('source_url', $resource->source_url ?? '')"
            name="source_url"
            label="{{ __('arachno.source.source_url') }}"
        />

        <x-ui.language-selector
            :value="old('primary_language', $resource->primary_language ?? '')"
            name="primary_language"
            label="{{ __('arachno.source.primary_language') }}"
        />

        <x-lookups.canned-response-selector
            field="source_rights"
            name="rights"
            :value="old('rights', $resource->rights ?? '')"
            @change="document.querySelector('#rights').value = $el.value"
            label="{{ __('arachno.source.rights') }}"
        />

        <x-workflows.tasks.task-assignment-selector
            name="owner_id"
            :value="old('owner_id', $resource->owner_id ?? null)"
            :label="__('arachno.source.owner')"
            :user="isset($resource->owner_id) ? User::find($resource->owner_id) : null"
        />

        <x-workflows.tasks.task-assignment-selector
            name="manager_id"
            :value="old('manager_id', $resource->manager_id ?? null)"
            :label="__('arachno.source.manager')"
            :user="isset($resource->manager_id) ? User::find($resource->manager_id) : null"
        />

        <x-arachno.source.consolidation-frequency-selector
            :value="old('consolidation_frequency', $resource->consolidation_frequency ?? null)"
            required
        />

        <div class="mt-4">
            <x-ui.input
                type="checkbox"
                :value="(bool) old('hide_content', $resource->hide_content ?? false)"
                name="hide_content"
                label="{{ __('arachno.source.hide_content') }}"
            />
        </div>

        <div class="mt-4">
            <x-ui.input
                type="checkbox"
                :value="(bool) old('permission_obtained', $resource->permission_obtained ?? false)"
                name="permission_obtained"
                label="{{ __('arachno.source.permission_obtained') }}"
            />
        </div>

        <div class="mt-4">
            <x-ui.input
                type="checkbox"
                :value="(bool) old('monitoring', $resource->monitoring ?? false)"
                name="monitoring"
                label="{{ __('arachno.source.monitoring') }}"
            />
        </div>

        <div class="mt-4">
            <x-ui.input
                type="checkbox"
                :value="(bool) old('ingestion', $resource->ingestion ?? false)"
                name="ingestion"
                label="{{ __('arachno.source.ingestion') }}"
            />
        </div>

        <div class="mt-4">
            <x-ui.input
                type="checkbox"
                :value="(bool) old('has_script', $resource->has_script ?? false)"
                name="has_script"
                label="{{ __('arachno.source.has_script') }}"
            />
        </div>

    </div>

    <div>
        <x-ui.textarea
            wysiwyg="basic"
            :value="old('source_content', $resource->source_content ?? '')"
            name="source_content"
            label="{{ __('arachno.source.source_content') }}"
            required
        />

        <div>
            <x-ui.textarea
                wysiwyg="basic"
                :value="old('permission_note', $resource->permission_note ?? '')"
                name="permission_note"
                label="{{ __('arachno.source.permission_notes') }}"
            />
        </div>
    </div>
</div>

<x-slot name="footer">
    <div></div>
    <div>
        <x-ui.back-button :fallback="route('collaborate.sources.index')"/>
        <x-ui.button
            type="submit"
            theme="primary"
        >{{ __('actions.save') }}</x-ui.button>
    </div>
</x-slot>
