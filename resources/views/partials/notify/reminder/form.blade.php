@php
use App\Enums\Tasks\TaskUser;
@endphp

<input type="hidden" name="remindable_type" value="{{ $remindableType }}" />
@if ($remindableType !== 'task' || ($remindableType === 'task' && !$isCreateForm))
  <input type="hidden" name="remindable_id" value="{{ $remindableId }}" />
@endif

@if ($remindableType !== 'task')
  <x-ui.input :value="old('title', $resource->title ?? '')"
              name="title"
              label="{{ __('interface.title') }}" />
  <x-ui.input type="textarea" :value="old('description', $resource->description ?? '')"
              name="description"
              label="{{ __('interface.description') }}" />
@endif

<div class="flex flex-row">
  <div class="w-44">
    <x-ui.input type="date"
                :value="old('remind_on_date', isset($resource) ? ($resource->remind_on_date?->format('Y-m-d') ?? '') : '')"
                name="remind_on_date"
                label="{{ __('notify.reminder.remind_on_date') }}" />

  </div>
  <div class="w-32 ml-5">

    <x-ui.input type="time"
                :value="old('remind_on_time', $resource->remind_on_time ?? '08:00')"
                name="remind_on_time"
                label="{{ __('notify.reminder.remind_on_time') }}" />
  </div>

</div>



@if ($remindableType === 'task')
  <x-ui.input type="select"
              multiple
              :options="TaskUser::lang()"
              :value="$remindWhomValue"
              name="notification_config[]"
              label="{{ __('notify.reminder.remind_whom') }}" />
@else
  <x-ui.input type="select"
              :options="$whomOptions"
              :value="old('remind_whom', $remindWhomValue)"
              name="remind_whom"
              label="{{ __('notify.reminder.remind_whom') }}" />

@endif
