<x-layouts.guest>
  <div class="mt-4 flex justify-center grow max-w-4xl w-full px-2">
    <x-ui.card class="xl:max-h-full">

      <div class="font-semibold mb-6">
        {{ __('collaborators.collaborator_application.permission_to_work') }}
      </div>

      <x-ui.form method="PUT" action="{{ route('collaborate.collaborator-application.stage-two.update', ['application' => $application->id]) }}" enctype="multipart/form-data">
        <div class="grid lg:grid-cols-2 gap-4">
          <div>
            <x-ui.input name="address_line_1" label="{{ __('collaborators.collaborator_application.physical_address') }}" required />
          </div>
          <div>
            <x-ui.input name="postal_address" label="{{ __('collaborators.postal_address') }}" />
          </div>

          <div>
            <x-ui.label for="">
              <span class="text-red-400">*</span>
              <span>{{ __('collaborators.identity_document') }}</span>
            </x-ui.label>
            <x-ui.file-selector name="identity" label="{{ __('collaborators.identity_document') }}" required />
          </div>

          <div>
            <x-ui.label for="">
              <span>{{ __('collaborators.visa_or_work_permit') }}</span>
            </x-ui.label>
            <x-ui.file-selector name="visa" label="{{ __('collaborators.visa_or_work_permit') }}" />
          </div>
        </div>

        <div class="mt-8">
          <x-ui.input name="permission_to_work" type="checkbox" required label="{{ __('collaborators.permission_to_work') }}" />
        </div>

        <p class="py-8">{{ __('collaborators.permission_to_process_description') }}</p>

        <div>
          <x-ui.input name="process_personal_data" type="checkbox" required label="{{ __('collaborators.permission_to_process') }}" />
        </div>

        <div class="mt-4 flex justify-end">
          <x-ui.button type="submit" styling="outline" theme="primary">
            {{ __('collaborators.collaborator_application.complete_application') }}
          </x-ui.button>
        </div>
      </x-ui.form>

    </x-ui.card>
  </div>
</x-layouts.guest>
