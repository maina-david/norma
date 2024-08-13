<x-ui.input {{ $attributes }} @change="$dispatch('changed', $el.value)" :name="$name" type="select"
            :options="['' => '--'] + $me + $users"
            :label="$label ?? ''"
            :multiple="$multiple ?? false"
            :value="$value" />
