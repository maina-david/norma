@component('mail::collaborate-message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-heading" style="font-weight: bold">
{{ __('mail.hi') }} {{ $application->fname }},
</div>
<br>
<div class="panel-body">
<p>
{!! __('collaborators.collaborator_application.application_received_body_line_1') !!}
</p>
<p>
{!! __('collaborators.collaborator_application.application_received_body_line_2') !!}
</p>

</div>
</div>
</div>
</div>
@endcomponent
