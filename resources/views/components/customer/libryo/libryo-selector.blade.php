@props(['id', 'title', 'label' => null, 'name' => 'libryo_id', 'required' => false, 'payload' => [], 'multiple' => false])

<x-ui.input
    name="{{ $name }}"
    :required="$required"
    label="{{ $label ?? __('settings.nav.libryo_streams') }}"
    :value="old($name, $id ?? '')"
    type="select"
    :options="isset($title) && isset($id) ? [$id => $title] : []"
    class="remote-select"
    :data-load="route('my.settings.libryos.index', $payload)"
    :multiple="$multiple"
    @change="$dispatch('changed', $el.value)"
/>
