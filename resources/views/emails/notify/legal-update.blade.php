@component('mail::message', [
    'whitelabel' => $whitelabel,
    'baseClientUrl' => $baseClientUrl,
    'unsubscribe_token' => $unsubscribe_token,
    'altHeader' => $altHeader,
    'unsubscribe' => empty($user) ? '' : 'If you do not wish to receive these emails, please click here to <a href="'. ($baseClientUrl ?? config('app.url_client')) . route('my.notifications.unsubscribe', ['user' => $user->id, 'token' => $unsubscribe_token], false) . '" target="_blank">unsubscribe.</a>'
])


{{-- Greeting --}}
@if (! empty($user))
# <span style="color: #014E20; font-weight: 600;">{{ __('mail.hi') }} {{ $user->fname }}</span>
@else
# {{ __('mail.hello') }}!
@endif

{{-- Intro Lines --}}
@if (! empty($introLines))
@foreach ($introLines as $line)
<span style="font-size: 15px; line-height: 26px; color: #1a2434">{!! $line !!}</span>
@endforeach
@endif

<hr class="divider" />

@component('mail::button-full', ['color' => 'green', 'url' => ($baseClientUrl ?? config('app.url_client')) . route('my.notify.legal-updates.index', [], false) ])
{{ __('notify.legal_update.view_notifications') }}
@endcomponent

<hr class="divider" />

<div style="padding-top: 16px">
<p style="font-size: 15px; line-height: 26px; color: #1a2434">See all your updates below:</p>
</div>

@foreach ($notifications as $key => $notification)
@component('mail::notification-table', ['notification' => $notification, 'baseClientUrl' => $baseClientUrl, 'appName' => $appName, 'index' => $key + 1])

@endcomponent

<br>

@endforeach

@if (! empty($outroLines))
{{-- Outro Lines --}}
@foreach ($outroLines as $line)
<p style="font-size: 16px">{!! $line !!}</P>

@endforeach
@endif

<!-- Salutation -->
@if (! empty($salutation))
<p style="font-size: 16px">{{ $salutation }}</p>
@else
<br>
<div style="font-size: 15px; line-height: 26px; color: #1a2434">
{{ __('mail.until_next_time') }},
<br>
<br>
{{--{{ !empty($whitelabel) ? $whitelabel->title : config('app.name') }}--}}
<p class="accent">{{ !empty($whitelabel) ? $whitelabel->title : __('mail.libryo') }}</p>

</div>
@endif

@endcomponent
