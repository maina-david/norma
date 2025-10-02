@component('mail::layout')
@php

use App\Services\Partners\WhitelabelsForUser;

if (!empty($user) && empty($whitelabel)) {
$whitelabel = app(WhitelabelsForUser::class)->getDefaultForUser($user);
}

if (!empty($user) && empty($baseClientUrl)) {
$baseClientUrl = app(WhitelabelsForUser::class)->getBaseUrlForUser($user);
}

@endphp

{{-- Header --}}
@slot('header')
@component('mail::header', [
'url' => $baseClientUrl ?? config('app.url'),
'baseClientUrl' => $baseClientUrl ?? null,
'whitelabel' => $whitelabel ?? null,
'altHeader' => $altHeader ?? false,
])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@if (isset($subcopy))
@slot('subcopy')
@component('mail::subcopy')
{!! $subcopy !!}
@endcomponent
@endslot
@endif

{{-- Footer --}}
@slot('footer')
@component('mail::footer', [
'whitelabel' => $whitelabel ?? null,
'baseClientUrl' => $baseClientUrl ?? null,
])
<div>
<p style="text-align: left; color: #74787E">
{{ !empty($whitelabel) ? $whitelabel->email_address : 'info@norma.com' }} | powered by <a
href="{{ config('norma.website_address') }}" target="_blank">{{ config('norma.website_address') }}</a>
<br />
&copy; {{ date('Y') }} {{ config('app.name') }} Limited. All rights reserved.
</p>
<p style="text-align: left;  color: #74787E">
@if (isset($unsubscribe))
{!! $unsubscribe !!}
@endif
</p>
<br />
</div>
<tr>
<td style="padding: 0px 0px 9px 0px;" colspan="2">
<hr class="blue-divider" />
</td>
</tr>
@endcomponent
<div style="width: 100%;">
@component('mail::disclaimer')
<p style="text-align: left">
{{ __('mail.disclaimer_part_1') }}
</p>
@endcomponent
@component('mail::disclaimer')
<p style="text-align: left; margin-bottom: 30px;">
{{ __('mail.disclaimer_part_2') }}
</p>
@endcomponent
</div>
@endslot
@endcomponent
