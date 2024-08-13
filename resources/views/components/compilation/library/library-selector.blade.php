@props(['id', 'title', 'label'])

<x-ui.input name="library_id"
            required
            label="{{ $label ?? __('compilation.library.library') }}"
            :value="old('library_id', $id ?? '')"
            type="select"
            :options="isset($title) && isset($id) ? [$id => $title] : []"
            class="remote-select"
            :data-load="route('my.settings.libraries.index')" />
