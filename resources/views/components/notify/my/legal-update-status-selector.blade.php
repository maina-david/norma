@php
use App\Enums\Notify\LegalUpdateStatus;
@endphp

@props(['name', 'value', 'label' => '', 'multiple' => false])

<x-ui.input {{ $attributes }}
            type="select"
            :value="$value"
            :name="$name"
            :label="$label"
            :options="['' => __('notify.legal_update.status.all')] + LegalUpdateStatus::lang()"
            :multiple="$multiple" />
