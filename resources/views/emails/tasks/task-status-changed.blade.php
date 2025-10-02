@component('mail::message', [
    'whitelabel' => $whitelabel,
    'baseClientUrl' => $baseClientUrl,
])
{{-- Greeting --}}
@if (! empty($user))
# {{ trans('mail.hi') }} <span style="color: #014E20; font-weight: 600;">{{ $user->fname }}</span>,
@else
# {{ trans('mail.hello') }},
@endif
<br>

@component('mail::task-mail-description', [
    'introLine' => trans('tasks.status_changed_task', ['title' => $task->title]),
    'main' => trans('tasks.status_changed_from', ['from' => __('tasks.task.status.' . $fromStatus), 'to' => __('tasks.task.status.' . $toStatus)])
])
@endcomponent

@component('mail::task-table', ['url' => $url, 'item' => $item, 'task' => $task, 'changedBy' => $changedBy, 'due_on' => $due_on])
@endcomponent

<br>

@component('mail::view-task', ['id' => $task->id, 'normaId' => $task->place_id])
@endcomponent

<!-- Salutation -->
@if (! empty($salutation))
{{ $salutation }}
@else
<br>
<span style="font-size: 15px; line-height: 26px; color: #1a2434">
{{ __('mail.until_next_time') }},<br><br><p class="accent">{{ !empty($whitelabel) ? $whitelabel->title : __('mail.norma') }}</p>
{{--    {{ trans('mail.until_next_time') }},<br>{{ !empty($whitelabel) ? $whitelabel->title : config('app.name') }}--}}
</span>
@endif

@endcomponent
