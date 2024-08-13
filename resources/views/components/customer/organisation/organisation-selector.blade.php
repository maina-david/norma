@props(['id', 'title', 'label' => null, 'name' => 'organisation_id', 'required' => true, 'payload' => [], 'route' => 'my.settings.organisations.index'])

<x-ui.input name="{{ $name }}"
            :required="$required"
            label="{{ $label ?? __('customer.organisation.organisation') }}"
            :value="old($name, $id ?? '')"
            type="select"
            :options="isset($title) && isset($id) ? [$id => $title] : []"
            class="remote-select"
            :data-load="route($route, $payload)"
            @change="$dispatch('changed', $el.value)" />
