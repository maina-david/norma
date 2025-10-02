@component('mail::onboard_message', [
'whitelabel' => $whitelabel,
'baseClientUrl' => $baseClientUrl,
])
<div class="container" style="margin-top: -48px;">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel panel-default">
<div class="panel-block">
<div style="background-image: url('{{ asset('/img/600x200_Header.png')}}');
background-repeat: no-repeat;
background-position: center;
background-color: #014E20;
background-size: cover;
">
<h1 style="padding: 100px 0">{{ __('mail.welcome_norma_platform') }}</h1>
</div>
</div>
<br>
<div class="panel-heading" style="color: #1A2434">
{{ __('mail.hi') }} <span style="color: #014E20; font-weight: 600;">{{ $fname }}</span>,
</div>
<br>
<div class="panel-body">
<div class="granted-access" >
@if (empty($whitelabel))
{{ __('mail.you_have_been_granted_access') }}
@else
{{ __('mail.you_have_been_granted_access_whitelabel', ['app-name' => $appName]) }}
@endif
<br>
<br>
{{ __('mail.click_to_set_password') }}
</div>
@component('mail::button', ['color' => 'green', 'url' => config('app.url') . '/reset-password/' . $token])
{{ __('mail.join_my_norma') }}
@endcomponent
<img style="width: 250px;display: block;margin: 0 auto;"
    src="{{ config('app.url') }}/img/welcome_email_graphic.png">
<br>
<div style="font-size: 20px; line-height: 35px; color: #014e20">
<strong>{{ __('mail.what_is_norma_platform') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">
{{ __('mail.norma_platform_description') }}
</p>
<div style="font-size: 15px; line-height: 26px; color: #1a2434">
<br>
<strong style="color: #014e20;">{{ __('mail.with_norma_you_can') }}:</strong>
</div>
<ul style="font-size: 15px; line-height: 26px; color: #1a2434">
<li>{{ __('mail.know_without_clutter') }}</li>
<li>{{ __('mail.search_by_topics') }}</li>
<li>{{ __('mail.stay_up_to_date') }}</li>
</ul>
<br>
<div style="font-size: 15px; line-height: 26px; color: #1a2434">
{{ __('mail.your_account_is_ready') }}
</div>
@component('mail::button', ['color' => 'green', 'url' => ($baseClientUrl ?? config('app.url')) .'/reset-password/' . $token])
{{ __('mail.join_my_norma') }}
@endcomponent
    <div style="font-size: 20px; line-height: 35px; color: #014e20">
        <strong>{{ __('mail.have_questions') }}</strong>
    </div>
    <br>
    <p style="font-size: 15px; line-height: 26px; color: #1a2434">
        {!! __('mail.email_customer_success_team', ['anchor' => '<a href="mailto:info@norma.com" style="color: #018219; font-weight: 600;">', 'anchor-close' => '</a>']) !!}
    </p>
@component('mail::subcopy')
{{ __('mail.trouble_clicking_button', ['button' => __('mail.join_my_norma')]) }}
[{{ ($baseClientUrl ?? config('app.url')) . '/reset-password/' . $token }}]({{ ($baseClientUrl ?? config('app.url')) . '/reset-password/' . $token }})
@endcomponent
</div>
</div>
</div>
</div>
</div>
@endcomponent
