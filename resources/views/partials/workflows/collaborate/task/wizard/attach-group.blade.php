@php
  $groupId = old('group_id', 7);
  $group = \App\Models\Collaborators\Group::find($groupId);
@endphp
<div>
<x-collaborators.group-selector
  name="group_id"
  required
  :label="__('workflows.task.wizard_steps.group_assignment')"
  :group="$group"
  value="{{ $groupId }}"
/>
</div>
