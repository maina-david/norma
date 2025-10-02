@component('mail::message', [
  'whitelabel' => $whitelabel,
  'baseClientUrl' => $baseClientUrl,
  ])
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel panel-default">
<div class="panel-block" style="padding-bottom: 30px;">
<h1 style="color: #0b7e58">{{ __('mail.lets_log_in') }}</h1>
</div>
<div class="panel-body panel-border">
<br>
<p class="em" style="text-align: center;">{{ __('mail.log_in_to_keep_activated') }}</p>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.we_noticed_you_havent_logged_in') }}</p>

<div class="heading">
<strong>{{ __('mail.what_does_this_mean') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.account_dormant_explanation') }}</p>
<div class="heading">
<strong>{{ __('mail.want_to_keep_account') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.keep_account_explanation') }}</p>
@component('mail::button', ['color' => 'green', 'url' => $baseClientUrl ?? config('app.url_client')])
{{ __('mail.sign_in_to_keep_your_account') }}
@endcomponent
@component('mail::hr')
@endcomponent
<div class="heading">
<strong>{{ __('mail.reasons_to_keep_account') }}</strong>
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
@component('mail::hr')
@endcomponent
<div style="font-size: 20px; line-height: 35px; color: #014e20">
<strong>{{ __('mail.have_questions') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">
{!! __('mail.have_questions_email_us', ['anchor' => '<a href="mailto:info@norma.com">', 'anchor-close' => '</a>']) !!}
</p>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.best_regards') }}</p>
<p class="accent">{{ __('mail.norma') }}</p>
</div>
</div>
</div>
</div>
</div>
@endcomponent
