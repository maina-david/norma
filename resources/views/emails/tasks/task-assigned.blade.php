@component('mail::message', [
'whitelabel' => $whitelabel,
'baseClientUrl' => $baseClientUrl,
])
{{-- Greeting --}}
@if (! empty($user))
# {{ __('mail.hi') }} <span style="color: #014E20; font-weight: 600;">{{ $user->fname }}</span>,
@else
# {{ __('mail.hello') }},
@endif
<br>

<p style="text-align: center;font-size: 18px; color: #014E20;margin-bottom: 35px;">
{{__('tasks.assigned_you_task', ['name' => $assigner])}}
</p>

<p style="text-align: center;color: #2F3133;font-size: 21px;margin-bottom: 10px">
<b>{{$task->title}}</b>
<p style="text-align: center;font-size: 17px;">
<b style="font-size: 15px; line-height: 26px; color: #1a2434">{!! $task->description !!}</b>
</p>
</p>
<br>

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
<span style="font-size: 15px; line-height: 26px; color: #1a2434">
{{ __('mail.until_next_time') }},<br><br><p class="accent">{{ !empty($whitelabel) ? $whitelabel->title : __('mail.norma') }}</p>
{{--{{ __('mail.until_next_time') }},<br>{{ !empty($whitelabel) ? $whitelabel->title : config('app.name') }}--}}
</span>
@endif

@endcomponent
