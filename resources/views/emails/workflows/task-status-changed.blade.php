@component('mail::collaborate-message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-heading" style="font-weight: bold">
{{ __('mail.hi') . ' ' . $task->assignee->fname }},
</div>
<br>
<div class="panel-body">

<p>{{ __('notifications.collaborate.task_status_changed_message') }}</p>

<p><b>#{{ $task->id }} - {{ $task->title }}</b></p>

<p>{{ __('notifications.collaborate.new_status') }}: <b>{{ \App\Enums\Workflows\TaskStatus::fromValue($task->task_status)->label() }}</b></p>

@component('mail::button', ['color' => 'green', 'url' => route('collaborate.tasks.show', ['task' => $task->id]) ])
{{ __('notifications.collaborate.have_a_look') }}
@endcomponent

</div>
</div>
</div>
</div>

@endcomponent
