@props(['name', 'label', 'required' => false, 'value' => null])

<x-ui.input {{ $attributes }}
            type="select"
            :options="\App\Support\Currencies::all()"
            :name="$name"
            :value="$value"
            :label="$label"
            :required="$required" />
