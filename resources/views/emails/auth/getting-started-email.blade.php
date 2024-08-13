@component('mail::message', [
  'whitelabel' => $whitelabel,
  'baseClientUrl' => $baseClientUrl,
  ])
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel panel-default">

<div class="panel-block">
<br>
<h1 style="color: #0b7e58">{{ __('mail.getting_started') }}</h1>
<br>
</div>
<div class="panel-body panel-border">

<br>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.hey_there') }}</p>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.getting_started_intro') }}</p>

@component('mail::hr')
@endcomponent

<table>
<tr>
<td align="center" valign="top" width="180"><img src="{{ config('app.url') }}/img/success_portal_img.png"
alt="Success Portal" width="100" height="100"></td>
<td style="padding-bottom: 20px;">
<h2 style="font-size: 22px;margin-bottom: 0;">{{ __('mail.success_portal') }}</h2>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{!! __('mail.success_portal_text', ['anchor' => '<a href="https://success.libryo.com/knowledge" target="_blank" class="clean accent">', 'anchor-close' => '</a>']) !!}</p>
</td>
</tr>
<tr>
<td align="center" valign="top" width="180"><img
src="{{ config('app.url') }}/img/erm_libryo_support.png" alt="Libryo Support" width="100"
height="100"></td>
<td style="padding-bottom: 20px;">
<h2 style="font-size: 22px;margin-bottom: 0;">{{ __('mail.libryo_support') }}</h2>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{!! __('mail.libryo_support_text', ['anchor' => '<a href="https://success.libryo.com/knowledge/getting-started-with-libryo/have-a-question/live-chat" target="_blank" class="clean accent">', 'anchor-close' => '</a>']) !!}</p>
</td>
</tr>
<tr>
<td align="center" valign="top" width="180"><img src="{{ config('app.url') }}/img/training_webinars_img.png"
alt="Training Webinars" width="100" height="100"></td>
<td style="padding-bottom: 20px;">
<h2 style="font-size: 22px;margin-bottom: 0;">{{ __('mail.training_webinars') }}</h2>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">{!! __('mail.training_webinars_text', ['anchor' => '<a href="https://info.libryo.com/training" target="_blank" class="clean accent">', 'anchor-close' => '</a>']) !!}</p>
</td>
</tr>
</table>

@component('mail::hr')
@endcomponent


<div style="font-size: 20px; line-height: 35px; color: #014e20">
<strong>{{ __('mail.have_questions') }}</strong>
</div>
<br>
<p style="font-size: 15px; line-height: 26px; color: #1a2434">
{!! __('mail.have_questions_email_us', ['anchor' => '<a href="mailto:info@libryo.com" class="clean">', 'anchor-close' => '</a>']) !!}
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
