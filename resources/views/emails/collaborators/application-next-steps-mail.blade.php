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
  <p>
    {!! __('collaborators.collaborator_application.contract_thank_you') !!}
  </p>

  <p>
    {!! __('collaborators.collaborator_application.accessing_norma_learn_heading') !!}<br/>
  </p>
  <p>
    {!! __('collaborators.collaborator_application.accessing_norma_learn') !!}
  </p>

  <p>
    {!! __('collaborators.collaborator_application.contract_completing_courses_heading') !!}<br/>
  </p>
  <p>
    {{ __('collaborators.collaborator_application.contract_completing_courses')}}
  </p>

  <p>
    {!! __('collaborators.collaborator_application.contract_completing_courses_list') !!}
  </p>

  <p>
    {!! __('collaborators.collaborator_application.contract_completing_courses_access') !!}
  </p>

  <p>
    {!! __('collaborators.collaborator_application.contract_course_badge_heading') !!}<br/>
  </p>
  <p>
    {!! __('collaborators.collaborator_application.contract_course_badge') !!}
  </p>

  <p>
    {!! __('collaborators.collaborator_application.applying_for_task_heading') !!}<br/>
  </p>
  <p>
    {!! __('collaborators.collaborator_application.applying_for_task') !!}
  </p>

  <p>
    <b style="font-weight: bold;">{!! __('collaborators.collaborator_application.contract_support_header') !!}</b><br/>
  </p>
  <p>
    {!! __('collaborators.collaborator_application.contract_support',['url' => config('collaborate.feedback_form_url') ]) !!}
  </p>
</div>
</div>
</div>
</div>
@endcomponent
