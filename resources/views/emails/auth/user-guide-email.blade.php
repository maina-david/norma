@component('mail::message', [
  'whitelabel' => $whitelabel,
  'baseClientUrl' => $baseClientUrl,
  ])
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel panel-default">
<div class="panel-block">
<h1 style="color: #0b7e58">{{ __('mail.norma_user_guide') }}</h1>
@component('mail::button', ['color' => 'green', 'url' => $userGuideUrl])
{{ __('mail.download_user_guide') }}
@endcomponent
</div>
<div class="panel-body panel-border">
<br>
<p class="em" style="text-align: center;">{{ __('mail.your_new_system') }}</p>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.hey_there') }}</p>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.user_guide_description_1') }}</p>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.user_guide_description_2') }}</p>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">
{!! __('mail.download_user_guide_here', ['anchor' => '<a href="' . $userGuideUrl . '" target="_blank" class="em clean">', 'anchor-close' => '</a>']) !!}
</p>
@component('mail::hr')
@endcomponent
<div style="font-size: 20px; line-height: 35px; color: #014e20">
<strong>{{ __('mail.have_questions') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">
{!! __('mail.have_questions_email_us', ['anchor' => '<a href="mailto:info@norma.com" class="clean">', 'anchor-close' => '</a>']) !!}
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
