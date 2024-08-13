<x-ui.input
            @change="$dispatch('changed', $el.value)"
            name="whitelabel_id"
            type="select"
            :required="$required ?? false"
            :label="$label ?? __('partners.white_label.whitelabel')"
            :value="$value ?? null"
            :options="$options"
            {{ $attributes }} />
