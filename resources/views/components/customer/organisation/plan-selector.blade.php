@props(['name', 'type', 'required', 'label', 'value', 'nullable'])
@php
use App\Enums\Customer\OrganisationPlan;
@endphp

<x-ui.input @change="$dispatch('changed', $el.value)"
            name="plan"
            type="select"
            :required="$required ?? false"
            :label="$label ?? __('customer.organisation.plan')"
            :value="$value ?? null"
            :options="isset($nullable) && $nullable ?['' => '--'] + OrganisationPlan::lang() : OrganisationPlan::lang()"
            {{ $attributes }} />
