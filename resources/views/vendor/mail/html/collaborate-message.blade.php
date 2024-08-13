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
'url' => str_replace('my.', 'collaborate.', config('app.url')),
'baseClientUrl' => null,
'whitelabel' => null,
'altHeader' => false,
'imageName' => '1400x160_Collaborate_Header.png'
])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}
<p>{{ __('mail.best_wishes') }}</p>
<p class="text-green">{{ __('mail.libryo') }}</p>

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

@component('mail::collaborate-footer')
<div>
<p style="text-align: left; color: #74787E">
{{ !empty($whitelabel) ? $whitelabel->email_address : config('collaborate.get_in_touch_email') }} | powered by
<a href="{{ config('collaborate.website_address') }}" target="_blank"> {{ config('collaborate.website_address') }}</a>
<br />
&copy; {{ date('Y') }} Libryo Limited. All rights reserved.
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

<table style="width:100%; background-color: #D9DBD9;">
  <tr>
    <td style="width: 100%;">
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
    </td>
  </tr>
</table>

<div>

</div>
@endslot
@endcomponent
