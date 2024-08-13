<tr>
<td>
<table class="footer" align="center" width="100%" cellpadding="0" cellspacing="0" style="width:100%;">
<tr>
<td class="" align="center">
<br>
<br>
<p style="text-align: left; font-size: .85em; padding: none">
{!! __('mail.here_to_help', ['anchor' => sprintf('<a href="%s">', config('collaborate.feedback_form_url')), 'anchor-close' => '</a>']) !!}
</p>
<br>
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
