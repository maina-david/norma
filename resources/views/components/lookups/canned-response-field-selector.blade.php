@props(['name', 'label'])
<x-ui.input
            {{ $attributes }}
            type="select"
            :name="$name"
            :label="$label ?? null"
            @change="$dispatch('changed', $el.value)"
            :options="['' => '--'] + \App\Enums\Lookups\CannedResponseField::lang()" />
