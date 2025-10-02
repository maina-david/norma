<x-ui.input @change="$dispatch('changed', $el.value)" name="status" type="select"
            :options="['' => '--',0 => __('interface.yes'), 1 => __('interface.no')]"
            :value="$value" />
