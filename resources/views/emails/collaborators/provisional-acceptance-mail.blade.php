@component('mail::collaborate-message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-heading" style="font-weight: bold">
  {{ __('mail.hi') . ' ' . $application->fname }},
</div>
<br>
<div class="panel-body">
<p>{{ __('collaborators.collaborator_application.provisional_acceptance_thank_you') }}</p>
<p>{!! __('collaborators.collaborator_application.provisional_acceptance_stage_two_link', ['link' => $link]) !!}</p>
<p>{{ __('collaborators.collaborator_application.provisional_acceptance_contract_info')}}</p>
<p>{!! __('collaborators.collaborator_application.provisional_acceptance_faqs', ['support_link' => 'mailto:' . config('collaborate.get_in_touch_email')]) !!}</p>
</div>
</div>
</div>
</div>
@endcomponent
