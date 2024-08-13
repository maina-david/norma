@component('mail::message', [
  'whitelabel' => $whitelabel,
  'baseClientUrl' => $baseClientUrl,
  ])
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel panel-default">
<div class="panel-block" style="padding-bottom: 30px;">
<h1 style="color: #0b7e58">{{ __('mail.introducing_libryo') }}</h1>
</div>
<div class="panel-body panel-border">
<br>
<p class="em" style="text-align: center;">
{{ __('mail.your_new_legal_register_system') }}</p>
<br>
<p  style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.welcome') }}</p>
<p  style="font-size: 15px; line-height: 26px; color: #1a2434">{!! __('mail.you_receive_email_setup_password', ['anchor' => '<a href="' . ($baseClientUrl ?? config('app.url')) . '/forgot-password">', 'anchor-close' => '</a>']) !!}</p>
@component('mail::button', ['color' => 'green', 'url' => $baseClientUrl ?? config('app.url')])
{{ __('mail.log_into') }}
@endcomponent
@component('mail::hr')
@endcomponent
<div class="heading">
<strong>{{ __('mail.what_is_libryo_platform') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.libryo_platform_description_onboard') }}</p>
<div class="heading">
<strong>{{ __('mail.how_can_libryo_help') }}</strong>
</div>
<ul style="font-size: 15px; line-height: 26px; color: #1a2434">
<li>{{ __('mail.how_help_legislation') }}</li>
<li>{{ __('mail.how_help_plain_language') }}</li>
<li>{{ __('mail.how_help_download_register') }}</li>
<li>{{ __('mail.how_help_legal_updates') }}</li>
<li>{{ __('mail.how_help_search') }}</li>
<li>{{ __('mail.how_help_reminders') }}</li>
</ul>
<br>
<div class="heading">
<strong>{{ __('mail.so_whats_next') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.over_the_next_few_days') }}</p>
@component('mail::hr')
@endcomponent
<div style="font-size: 20px; line-height: 35px; color: #014e20">
<strong>{{ __('mail.have_questions') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">
{!! __('mail.have_questions_email_us', ['anchor' => '<a href="mailto:info@libryo.com">', 'anchor-close' => '</a>']) !!}
</p>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.best_regards') }}</p>
<p class="accent">{{ __('mail.libryo') }}</p>
</div>
</div>
</div>
</div>
</div>
@endcomponent
