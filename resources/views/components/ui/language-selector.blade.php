@props(['name' => '', 'required', 'label', 'value', 'tomselect' => true, 'withDefault' => false])

<x-ui.input
  :name="$name"
  type="select"
  :required="$required ?? false"
  :label="$label"
  :value="$value ?? null"
  :options="($withDefault ? ['' => '-'] : []) + \App\Support\Languages::forSelector()"
  :tomselect="$tomselect"
  {{ $attributes }}
  @change="$dispatch('changed', $el.value)"
/>
