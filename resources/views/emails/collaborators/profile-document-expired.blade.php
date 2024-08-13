@component('mail::collaborate-message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-heading" style="font-weight: bold">
{{ __('mail.hi') . ' ' . $document->profile->user->fname }},
</div>
<br>
<div class="panel-body">
@if(now()->isSameDay($document->valid_to))
  <p>{{ __('notifications.document_expired_body', ['document' =>  $document->type->label()]) }}</p>
@else
  <p>{{ __('notifications.document_expiring_body', ['document' => $document->type->label()]) }}</p>
@endif

</div>
</div>
</div>
</div>

@endcomponent
