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

<p>{{ __('notifications.collaborate.new_task_application_message', ['number' => $application->task_id, 'name' => $application->task->title]) }}</p>

@component('mail::button', ['color' => 'green', 'url' => route('collaborate.task-applications.index', ['search' => $application->task_id]) ])
{{ __('notifications.collaborate.view_application') }}
@endcomponent

</div>
</div>
</div>
</div>

@endcomponent
