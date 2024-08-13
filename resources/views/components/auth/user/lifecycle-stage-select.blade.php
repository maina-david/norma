@php
use App\Enums\Auth\LifecycleStage;
@endphp

<x-ui.input @change="$dispatch('changed', $el.value)" name="status" type="select"
            :options="array_merge(['' => '--'], LifecycleStage::lang())"
            :value="$value" />
