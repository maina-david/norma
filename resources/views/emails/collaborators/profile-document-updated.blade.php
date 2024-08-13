@component('mail::collaborate-message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-heading" style="font-weight: bold">
{{ __('mail.hi') }},
</div>
<br>
<div class="panel-body">
<p>
{!! __('collaborators.collaborator.document_updated_body', ['name' => $document->profile->collaborator->full_name]) !!}
</p>

@component('mail::button', ['url' => route('collaborate.collaborators.show', ['collaborator' => $document->profile->user_id, 'tab' => 'documents'])])
{{ __('collaborators.collaborator.review') }}
@endcomponent

</div>
</div>
</div>
</div>
@endcomponent
