<tr style="background-color: #fff;">
<td style="padding-bottom: 25px;">
<table align="center" width="600" cellpadding="0" cellspacing="0" style="padding: 0 85px;">
<tr>
<br>
<td width="50%" align="left" valign="top">
<a href="https://www.norma.com/" target="_blank" class="clean" style="color: black; font-weight: bold">norma<span style="color: #45a8ba;">.</span>com</a>
<br>
<a href="http://blog.norma.com/" target="_blank" class="em clean">blog.norma.com</a>
<img src="{{ config('app.url') }}/img/norma.png" alt="" width="250" height="auto" style="margin-top: 50px;">
</td>
<td width="50%" align="right" valign="bottom" >
@if(!empty($whitelabel))
<img src="{{ config('app.url') }}/img/whitelabels/email/{{ $whitelabel->shortname}}.png" width="140" height="auto">
@else
<a href="https://www.linkedin.com/company/norma/" title="linkedin Norma" alt="linkedin Norma">
<img src="{{config('app.url') }}/img/linkedin_original_black.png" alt="" width="24" height="24">
</a>
<img src="{{ config('app.url') }}/img/trees.png" alt="" width="160" height="auto">
@endif
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td>
<table class="footer" align="center" width="400" cellpadding="0" cellspacing="0">
<tr>
<td class="" align="center" style="padding: 20px 0;">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
