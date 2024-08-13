<x-ui.input @change="$dispatch('changed', $el.value)" name="status" type="select"
            :options="['' => '--',1 => __('auth.user.active_yes'), 0 => __('auth.user.active_no')]"
            :value="$value" />
