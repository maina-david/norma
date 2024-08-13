@props(['name', 'type', 'required', 'label', 'value'])
@php
use App\Enums\Customer\OrganisationLevel;
@endphp

<x-ui.input @change="$dispatch('changed', $el.value)"
            name="customer_category"
            type="select"
            :required="$required ?? false"
            :label="$label ?? __('customer.organisation.customer_categorisation')"
            :value="$value ?? null"
            :options="['' => '--'] + OrganisationLevel::lang()"
            {{ $attributes }} />
