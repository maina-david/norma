@component('mail::message', [
'whitelabel' => $whitelabel,
'baseClientUrl' => $baseClientUrl,
])
{{-- Greeting --}}
@if (!empty($user))
# {{ __('mail.hi') }} <span style="color: #014E20; font-weight: 600;">{{ $user->fname }},</span>
@else
# {{ __('mail.hello') }},
@endif
<br>

@component('mail::task-mail-description', [
'introLine' => __('tasks.assignee_changed_task', ['title' => $task->title]),
'main' => __('tasks.assignee_changed_from', ['from' => $fromUser, 'to' => $toUser]),
])
@endcomponent

@component('mail::task-table', ['url' => $url, 'item' => $item, 'task' => $task, 'due_on' => $due_on, 'changedBy' => $changedBy])
@endcomponent

<br>

@component('mail::view-task', ['id' => $task->id, 'libryoId' => $task->place_id])
@endcomponent

<!-- Salutation -->
@if (!empty($salutation))
{{ $salutation }}
@else
<br>
<span style="font-size: 15px; line-height: 26px; color: #1a2434">{{ __('mail.until_next_time') }},<br><br><p class="accent">{{ !empty($whitelabel) ? $whitelabel->title : __('mail.libryo') }}</p></span>
{{--{{ __('mail.until_next_time') }},<br>{{ !empty($whitelabel) ? $whitelabel->title : config('app.name') }}--}}
@endif

@endcomponent
