@php
use App\Enums\Collaborators\DocumentArchiveOptions;
$revalidate = $revalidate ?? false;
@endphp
<x-ui.modal>
  <x-slot:trigger>
    <x-ui.button styling="outline" :theme="session()->has('errors') ? 'secondary' : 'primary'" @click="open = !open">
      @if(session()->has('errors'))
        {{ __('collaborators.validation_error') }}
      @else
        @if($approved && $revalidate)
        {{ __('collaborators.validate_again') }}
        @else
        {{ __('collaborators.validate') }}
        @endif
      @endif
    </x-ui.button>
  </x-slot:trigger>

  <div class="pt-4 max-w-screen-75">
    <div class="font-semibold text-lg">
      {{ $document->type->label() }}:
      {{ __('collaborators.waiting_for_validation') }}
    </div>

    <div class="mt-4">
      @if($document->attachment_id)
        <embed style="height:calc(100vh * 0.6)" class="w-screen-75"
               src="{{ route("collaborate.profile.document.stream", ['document' => $document->id]) }}">
      @endif
    </div>

    <div class="w-screen max-w-3xl my-4">
      <div class="font-semibold text-sm">{{ __('collaborators.collaborator.reason_for_update') }}</div>
      <p>{{ $document->reason_for_update?->label() ?? '-'  }}</p>
    </div>

    <x-ui.form method="PUT" :action="route('collaborate.profile.document.validate', ['document' => $document->id])">

      @if($document->reason_for_update !== DocumentArchiveOptions::NOT_REQUIRED)
        @if(isset($subtypes))
          <x-ui.input
              type="select"
              name="subtype"
              :label="__('collaborators.document_type')"
              :options="$subtypes"
              required
          />
        @endif

        @if($extra ?? false)
          @include($extra)
        @endif

        <div class="grid gap-4 grid-cols-2">
          <div>
            <x-ui.input type="date" name="valid_from" :label="__('collaborators.valid_from')"/>
          </div>

          <div>
            <x-ui.input type="date" name="valid_to" :label="__('collaborators.valid_to')"/>
          </div>
        </div>
      @endif

      <div>
        <x-ui.textarea name="comment" :label="__('comments.add_comment')" rows="2"></x-ui.textarea>
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
