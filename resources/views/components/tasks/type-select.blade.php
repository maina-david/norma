@php
use App\Enums\Tasks\TaskType;

@endphp

<x-ui.input @change="$dispatch('changed', $el.value)" name="type" type="select"
            :options="['' => '--'] + TaskType::keysLang()"
            :value="$value"
            class="tomselected" />
