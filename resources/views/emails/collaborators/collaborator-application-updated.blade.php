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
{!! __('collaborators.collaborator_application.application_updated_body', ['number' => $application->id]) !!}
</p>

@component('mail::button', ['url' => route('collaborate.collaborator-applications.show', ['collaborator_application' => $application->id])])
{{ __('requirements.consequence.view_details') }}
@endcomponent
</div>
</div>
</div>
</div>
@endcomponent
