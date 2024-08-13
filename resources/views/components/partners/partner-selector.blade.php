<x-ui.input
            @change="$dispatch('changed', $el.value)"
            name="partner_id"
            type="select"
            :required="$required ?? false"
            :label="$label ?? __('partners.partner.partner_company')"
            :value="$value ?? null"
            :options="$options"
            {{ $attributes }} />
