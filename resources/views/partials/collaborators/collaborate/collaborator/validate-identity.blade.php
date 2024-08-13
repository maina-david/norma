@php
  use App\Enums\Collaborators\DocumentType;
  use App\Enums\Collaborators\IdentityDocumentType;
  $forApplication = $forApplication ?? false;
  $routeParam = $forApplication ? 'application' : 'collaborator';
  $attachmentRoute = $forApplication ? 'collaborate.collaborator-applications.attachments' : 'collaborate.collaborators.attachments';
@endphp
<x-ui.modal>
  <x-slot:trigger>
    <x-ui.button styling="outline" :theme="session()->has('errors') ? 'secondary' : 'primary'" @click="open = !open">
      @if(session()->has('errors'))
        {{ __('collaborators.validation_error') }}
      @else
        {{ __('collaborators.validate') }}
      @endif
    </x-ui.button>

  </x-slot:trigger>

  <div class="pt-4 max-w-screen-75">
    <div class="font-semibold text-lg">{{ __('collaborators.identity_document') }}
      : {{ __('collaborators.waiting_for_validation') }}</div>
    <div class="mt-4">
      <embed
          style="height:calc(100vh * 0.6)"
          class="w-screen-75"
          src="{{ route("{$attachmentRoute}.stream", [$routeParam => $resource->id, 'type' => DocumentType::IDENTIFICATION->field()]) }}"
      >
    </div>

    <x-ui.form
        x-data="{type:'{{ IdentityDocumentType::ID->value }}'}"
        method="PUT"
        :action="route($attachmentRoute . '.validate', [$routeParam => $resource->id, 'type' => DocumentType::IDENTIFICATION->field()])"
    >
      <x-ui.input
          type="select"
          name="identity_document_type"
          x-model="type"
          :label="__('collaborators.identity_document_type')"
          :options="\App\Enums\Collaborators\IdentityDocumentType::forSelector()"
      />

      <div x-show="type !== '{{ IdentityDocumentType::ID->value }}'">
        <x-ui.input type="date" name="identity_document_expiry" :label="__('collaborators.document_expires_on')"/>
      </div>


      <div class="flex justify-end space-x-4 mt-4">
        <x-ui.button @click="open = false" styling="outline">
          {{ __('actions.close') }}
        </x-ui.button>

        <x-ui.button type="submit" styling="outline" theme="primary">
          {{ __('collaborators.validate') }}
        </x-ui.button>
      </div>
    </x-ui.form>
  </div>
</x-ui.modal>
