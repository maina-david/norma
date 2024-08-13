@php
  $forApplication = $forApplication ?? false;
  $routeParam = $forApplication ? 'application' : 'collaborator';
  $attachmentRoute = $forApplication ? 'collaborate.collaborator-applications.attachments' : 'collaborate.collaborators.attachments';
@endphp
<div x-data="{restricted:{{ old('restricted_visa', 0) }}}">
  <x-ui.input
      type="select"
      name="restricted_visa"
      x-model="restricted"
      :label="__('collaborators.restricted_visa')"
      :options="[1 => __('interface.yes'), 0 => __('interface.no')]"
  />

  <div x-show="restricted == 1">

    <div>
      <x-ui.input x-bind:required="restricted == 1" name="restriction_type" :label="__('collaborators.restriction_type')" />
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <x-ui.input x-bind:required="restricted == 1" type="number" name="visa_hours" :label="__('collaborators.hours_restricted')" />
      </div>

      <div>
        <x-ui.input x-bind:required="restricted == 1" type="number" name="visa_available_hours" :label="__('collaborators.hours_available_for_restricted')" />
      </div>

    </div>
  </div>
</div>
