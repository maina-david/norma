@props(['name', 'type', 'required', 'label', 'value', 'nullable'])
@php
use App\Enums\Customer\OrganisationType;
@endphp

<x-ui.input
            @change="$dispatch('changed', $el.value)"
            name="type"
            type="select"
            :required="$required ?? false"
            :label="$label ?? __('customer.organisation.type')"
            :value="$value ?? null"
            :options="isset($nullable) && $nullable ?['' => '--'] + OrganisationType::lang() : OrganisationType::lang()"
            {{ $attributes }} />
