<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0">
<tr>
<td class="" align="center">
@if(!empty($whitelabel))
<img src="{{ config('app.url_client') }}/img/whitelabels/email/{{ $whitelabel->shortname}}.png">
<br>
<br>
@else
<br>
<br>
<p style="text-align: left; font-size: .85em; padding: none">
{!! __('mail.here_to_help', ['anchor' => '<a href="mailto:info@norma.com">', 'anchor-close' => '</a>']) !!}
</p>
<br>
@endif
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
