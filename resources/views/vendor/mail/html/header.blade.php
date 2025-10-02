<tr>
@if(empty($whitelabel))
<td class="header">
<a href="{{ $url }}" title="{{ $slot }}" alt="{{ $slot }}">
@if(empty($app_version) || $app_version != 1)
<img src="{{ !empty($baseClientUrl) ? $baseClientUrl : config('app.url_client') }}/img/{{ $imageName ?? '1400x160_Header.png' }}">
@endif
</a>
</td>
@endif
@if(!empty($whitelabel))
<td style="background: #eeeeee">
<table width="540" cellpadding="0" cellspacing="0" align="center">
<tr>
<td width="50%">
<a href="{{ $url }}" title="{{ $slot }}" alt="{{ $slot }}">
<img width="160" height="auto" src="{{ config('app.url_client') }}/img/whitelabels/small-{{ $whitelabel->shortname}}.png">
</a>
</td>
<td class="social" width="50%" align="right">
<img src="{{ config('app.url_client') }}/img/whitelabels/small-app.png" alt="" width="130" height="auto">
</td>
<td class="social" width="50%" align="right">
    <a href="https://www.linkedin.com/company/norma/" title="linkedin Norma" alt="linkedin Norma">
        <img src="{{config('app.url') }}/img/linkedin_original_black.png" alt="" width="24" height="24">
    </a>
</td>
</tr>
</table>
</td>
@endif
</tr>
