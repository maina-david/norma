@component('mail::message', [
    'whitelabel' => $whitelabel,
    'baseClientUrl' => $baseClientUrl,
    'unsubscribe_token' => $unsubscribe_token,
    'unsubscribe' => empty($user) ? '' : 'If you do not wish to receive these emails, please click here to <a href="'. ($baseClientUrl ?? config('app.url_client')) . route('my.notifications.unsubscribe', ['user' => $user->id, 'token' => $unsubscribe_token], false) . '" target="_blank">unsubscribe.</a>'
])
{{-- Greeting --}}
@if (! empty($user))
  # {{ __('mail.hi') }} {{ $user->fname }},
@else
  # {{ __('mail.hello') }},
@endif
<br>
<p style="text-align: center;font-size: 19px; color: #E05672">
  @if ($user->id === $author->id)
    {{ __('notify.reminder.you_set_reminder') }}
  @else
    {{ __('notify.reminder.user_set_reminder', [ 'user' => $author->name ]) }}
  @endif
</p>

<p style="text-align: center;color: #2F3133;font-size: 25px;margin-bottom: 10px">
  <b>{{$title}}</b>
</p>

<p style="text-align: center;font-size: 20px;">
  <b>{!! $description !!}</b>
</p>
<br>

<p style="text-align: center;">
  {{ $set_on }} <span style="font-style: italic; color: #6EB4B9">{{ $remindable_title }}</span>
</p>
<p style="text-align: center;">
  {{ __('notify.reminder.set_to_go_off', ['date' => $date]) }}
</p>

<br>
<p style="text-align: center; font-size: 14px">{{ __('notify.reminder.click_view_in_app') }}</p>

@component('mail::button', ['color' => 'pink', 'url' => ($baseClientUrl ?? config('app.url_client')) . $url ])
  {{ __('notify.reminder.view_reminder') }}
@endcomponent

<p style="text-align: center; font-size: 14px">
  {{ __('notify.reminder.or_use_browser') }} <a href="{{ ($baseClientUrl ?? config('app.url_client')) }}/app/notifications">{{ ($baseClientUrl ?? config('app.url_client')) }}/app/notifications</a> {{ __('notify.reminder.now') }}.
  <br>
</p>

<!-- Salutation -->
@if (! empty($salutation))
  {{ $salutation }}
@else
  <br>
  {{ __('mail.until_next_time') }},<br>{{ !empty($whitelabel) ? $whitelabel->title : config('app.name') }}
@endif

@endcomponent
