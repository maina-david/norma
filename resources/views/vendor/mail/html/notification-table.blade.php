<table class="notification-table" width="100%" cellpadding="5" cellspacing="3">
<th style="background-color: #ffffff; text-align: left" colspan="2">
<div style="display: flex; flex-direction: row">
    <div class="notice-count-indicator">
        <span class="notice-count-indicator-text">{{ $index }}</span>
    </div>
@if(!empty($notification->first_applicable_place_id))
    <a style="font-size: 16px" class="notification-link" href="{{ $baseClientUrl ?? config('app.url_client') }}{{ route('my.normas.activate.redirect', ['norma' => $notification->first_applicable_place_id], false) }}?redirect={{ route('my.notify.legal-updates.show', ['update' => $notification->id], false) }}" target="_blank">{{ $notification->title }}</a>
@else
    <a style="font-size: 16px" class="notification-link" href="{{ $baseClientUrl ?? config('app.url_client') }}{{ route('my.notify.legal-updates.show', ['update' => $notification->id], false) }}" target="_blank">{{ $notification->title }}</a>
@endif
</div>
</th>
<!-- Section Break -->
<tr>
    <td colspan="2">
        <hr style="width: 80%" class="divider" />
    </td>
</tr>
<!-- Section Break -->

<tr>
<td style="padding: 0px; width: 50%; font-size: 16px; vertical-align: top; padding-top: 5px">{{ __('mail.jurisdictions') }}</td>
<td >
@if($notification->primaryLocation && !empty($notification->primaryLocation->country))
<div style="display: flex; flex-direction: row; align-items: flex-start;">
    <img src="{{ $baseClientUrl ?? config('app.url_client') }}/img/flags_rounded/small/{{ $notification->primaryLocation->country->flag}}.png" alt="{{ $notification->primaryLocation->country->title }}" title="{{ $notification->primaryLocation->country->title }}" width="20px" height="20px" style="vertical-align:middle; margin-right: 5px; height: 20px; width: 20px;"/>
    <span  style="padding: 0px; height: auto; font-size: 16px">
        {{ $notification->primaryLocation->jurisdictions }}
    </span>
</div>
@endif
</td>
</tr>
<!-- Section Break -->
@if(!empty($notification->notifiable))
<tr>
    <td  style="padding: 0px; width: 50%; font-size: 16px; vertical-align: top; padding-top: 5px">{{ __('notify.legal_update.published_in_terms_of') }}</td>
<td style="font-size: 16px">
{{ $notification->notifiable->title }}
{{$notification->notifiable->title_translation}}
@if(!empty($notification->notifiable->title_translation))
[{{ $notification->notifiable->title_translation }}]
@endif
</td>
</tr>
@endif

@if(!empty($notification->publication_number))
<tr>
<td style="padding: 0px; width: 50%; font-size: 16px">{{ __('corpus.work.gazette_number') }}</td>
<td style="font-size: 16px">{{ $notification->publication_number }}</td>
</tr>
@endif
@if(!empty($notification->publication_document_number))
<tr>
<td style="padding: 0px; width: 50%; font-size: 16px">{{ __('corpus.work.notice_number') }}</td>
<td style="font-size: 16px">{{ $notification->publication_document_number }}</td>
</tr>
@endif
@if(!empty($notification->publication_date))
<tr>
<td style="padding: 0px; width: 50%; font-size: 16px">{{ __('corpus.work.publication_date') }}</td>
<td style="font-size: 16px">{{ $notification->publication_date->format('j F Y') }}</td>
</tr>
@endif
@if(!empty($notification->effective_date))
<tr>
<td style="padding: 0px; width: 50%; font-size: 16px">{{ __('corpus.work.effective_date') }}</td>
<td style="font-size: 16px">{{ $notification->effective_date->format('j F Y') }}</td>
</tr>
@endif
<!-- Highlights -->
@if(!empty($notification->update_report))
<tr>
<td colspan="2">
<hr style="width: 80%" class="divider" />
</td>
</tr>
<tr>
<th colspan="2" style="font-size: 16px">{{ __('corpus.work.highlights') }}</th>
</tr>
<tr>
<td style="padding: 18px 0px 0px 0px; font-size: 16px" colspan="2">{!! $notification->highlights ?? $notification->update_report !!}</td>
</tr>
@endif
<!-- End Of Highlights -->
<!-- Changes to requirements -->
<tr>
    <td colspan="2">
        <hr style="width: 80%" class="divider" />
    </td>
</tr>
<tr>
<th style="font-size: 16px" colspan="2">{{ __('notify.legal_update.changes_to_platform') }}</th>
</tr>
@if(empty($notification->changes_to_register))
<tr>
<td  style="padding: 18px 0px 0px 0px; font-size: 16px" colspan="2">{{ __('notify.legal_update.no_changes_needed', ['app-name' => $appName]) }}</td>
</tr>
@else
<tr>
<td style="padding: 18px 0px 0px 0px; font-size: 16px" colspan="2">{!! $notification->changes_to_register !!}</td>
</tr>
@endif
<!-- End Of Changes to requirements -->
<tr>
<td style="padding: 18px 0px 0px 0px;" colspan="2">
<hr class="blue-divider" />
</td>
</tr>
</table>
