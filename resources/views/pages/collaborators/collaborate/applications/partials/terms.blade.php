<div class="space-y-6">
  <p>{!! __('collaborators.terms.storage_and_processing') !!}</p>

  <x-ui.input name="process_personal_data" type="checkbox" :label="__('collaborators.terms.storage_and_processing_consent')" />

  <p>{!! __('collaborators.terms.communication') !!}</p>

  <x-ui.input name="allows_communication" type="checkbox" :label="__('collaborators.terms.communication_consent')" />

  <p>{!! __('collaborators.terms.unsubscribe_and_policy') !!}</p>
</div>

