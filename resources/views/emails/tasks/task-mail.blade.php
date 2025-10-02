@component('mail::message', [
'whitelabel' => $whitelabel,
'baseClientUrl' => $baseClientUrl,
])
{{-- Greeting --}}
@if (! empty($user))
# {{ __('mail.hi') }} {{ $user->fname }},
@else
# {{ __('mail.hello') }},
@endif
<br>

{{ $slot }}

@component('mail::task-table', ['url' => $url, 'item' => $item, 'task' => $task, 'due_on' => $due_on])
@endcomponent

<br>

@component('mail::view-task', ['id' => $task->id, 'normaId' => $task->place_id])
@endcomponent

<!-- Salutation -->
@if (! empty($salutation))
{{ $salutation }}
@else
<br>
{{ __('mail.until_next_time') }},<br>{{ !empty($whitelabel) ? $whitelabel->title : config('app.name') }}
@endif

@endcomponent
