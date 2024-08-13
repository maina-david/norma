<div>
  @if($editable)
    <div class="flex justify-end mb-4">
      <x-ui.modal>
        <x-slot:trigger>
          <x-ui.button theme="primary" @click="open = true">{{ __('corpus.work.add_requirements') }}</x-ui.button>
        </x-slot:trigger>

        <div class="max-w-4xl w-screen">
          <x-turbo::frame loading="lazy" id="requirements-for-assessment-item-response-{{ $response->id }}-form" src="{{ route('my.references.for.assessment-item-response.create', ['response' => $response]) }}">
            <x-ui.skeleton />
          </x-turbo::frame>
        </div>

      </x-ui.modal>
    </div>
  @endif

  @if (!$empty)
    <x-corpus.reference.reference-data-table :base-query="$baseQuery"
                                             :route="$route"
                                             :fields="[$editable ? 'detailed_with_unlink' : 'detailed']"
                                             :headings="false" />
  @else
    <x-ui.empty-state-icon icon="gavel"
                           :title="__('corpus.no_requirements')" />
  @endif

</div>
