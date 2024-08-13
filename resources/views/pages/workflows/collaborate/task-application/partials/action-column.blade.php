<div class="flex space-x-8">
  <x-ui.confirm-button :route="route('collaborate.task-applications.update', $row->id)"
                       :label="__('workflows.task_application.approve')" method="PUT"
                       confirmation="
                       {{ $row->applicant->profile->documents->isNotEmpty() ? __('workflows.task_application.approve_confirmation_pending_documents')
                       : __('workflows.task_application.approve_confirmation') }}
                       ">
    {{ __('workflows.task_application.approve') }}
  </x-ui.confirm-button>

  <x-ui.confirm-button danger :route="route('collaborate.task-applications.destroy', $row->id)"
                       :label="__('workflows.task_application.reject')" method="DELETE"
                       :confirmation="__('workflows.task_application.reject_confirmation')">
    {{ __('workflows.task_application.reject') }}</x-ui.confirm-button>
</div>
