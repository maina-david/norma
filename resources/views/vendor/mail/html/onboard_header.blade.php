<tr>
<td>
<table width="540" cellpadding="0" cellspacing="0" align="center" style="padding: 0 50px">
<br>
<tr>
<td width="50%">
<a href="{{ $url }}" title="{{ $slot }}" alt="{{ $slot }}">
@if(empty($whitelabel))
<img src="{{ config('app.url') }}/img/libryo.png" alt="" width="200" height="auto">
@endif
@if(!empty($whitelabel))
<img width="160" height="auto" src="{{ config('app.url') }}/img/whitelabels/small-{{ $whitelabel->shortname}}.png">
@endif
</a>
</td>
<td class="social" width="50%" align="right">
@if(empty($whitelabel))
<a href="https://www.linkedin.com/company/libryo/" title="linkedin Libryo" alt="linkedin Libryo">
<img src="{{config('app.url') }}/img/linkedin_original_black.png" alt="" width="24" height="24">
</a>
@else
<img src="{{ config('app.url') }}/img/whitelabels/small-app.png" alt="" width="130" height="auto">
@endif
</td>
</tr>
</table>
</td>
</tr>
