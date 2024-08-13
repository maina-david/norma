<input type="hidden" value="{{ $forResource->id }}" name="team_id" />

<x-ui.input type="select" :options="$taskTypes" name="for_task_type_id"
            :value="old('for_task_type_id', $resource->for_task_type_id ?? '')" label="{{ __('collaborators.team_rate.task_type') }}" />

<x-ui.input :value="old('rate_per_unit', $resource->rate_per_unit ?? '')" name="rate_per_unit"
            label="{{ __('collaborators.team_rate.rate_per_unit') }}" required />

<x-ui.input :value="old('unit_type_singular', $resource->unit_type_singular ?? 'Hour')" name="unit_type_singular"
            label="{{ __('collaborators.team_rate.unit_type_singular') }}" required />

<x-ui.input :value="old('unit_type_plural', $resource->unit_type_plural ?? 'Hours')" name="unit_type_plural"
            label="{{ __('collaborators.team_rate.unit_type_plural') }}" required />


<div class="flex justify-end w-full mt-4">
  <div></div>
  <div>


    <x-ui.back-button :fallback="route($baseRoute . '.store', $baseRouteParams ?? [])" />

    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</div>
