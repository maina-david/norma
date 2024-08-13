<x-ui.input @change="$dispatch('changed', $el.value)" :name="$name" type="select"
            :options="['' => '--'] + $projects"
            :label="$label ?? ''"
            :value="$value" />
