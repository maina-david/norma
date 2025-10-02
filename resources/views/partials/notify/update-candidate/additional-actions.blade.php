@php use App\Enums\Corpus\UpdateCandidateActionStatus; @endphp
@if(!empty($actions))
  <x-ui.form method="post"
             action="{{ route('collaborate.docs-for-update.additional-actions.sync', ['doc' => $resource->id]) }}"
             class="p-4">
    <x-ui.input
        name="additional_actions[]"
        type="select"
        :options="$actions"
        :label="__('corpus.doc.select_additional_actions')"
        multiple
    />

    <x-slot name="footer">
      <div></div>
      <div>
        <x-ui.button type="submit" theme="primary">{{ __('actions.update') }}</x-ui.button>
      </div>
    </x-slot>
  </x-ui.form>

  <div class="border-b border-norma-gray-300"></div>
@endif


@if(!$resource->updateCandidate->notificationActions->isEmpty())

  <x-ui.form
    id="delete_candidate_action_form{{ $resource->id }}"
    method="DELETE"
    action="{{ route('collaborate.docs-for-update.additional-actions.store', ['doc' => $resource->id]) }}"
    data-action="{{ route('collaborate.docs-for-update.additional-actions.store', ['doc' => $resource->id]) }}"
  />

  <x-ui.form method="post"
             action="{{ route('collaborate.docs-for-update.additional-actions.store', ['doc' => $resource->id]) }}"
             class="p-4">

    @foreach($resource->updateCandidate->notificationActions as $action)

      <div>
        <x-ui.input
            name="action{{ $action->id }}"
            type="select"
            :options="UpdateCandidateActionStatus::forSelector()"
            :value="$action->pivot->status"
            :label="$action->text"
            :placeholder="__('auth.user.status')"
        />
      </div>

      <div class="flex justify-end mt-4">
        <x-ui.button styling="outline" theme="secondary" @click="document.querySelector('#delete_candidate_action_form{{ $resource->id }}').setAttribute('action', document.querySelector('#delete_candidate_action_form{{ $resource->id }}').getAttribute('data-action') + '/{{ $action->id }}');document.querySelector('#delete_candidate_action_form{{ $resource->id }}').requestSubmit()">
          {{ __('actions.delete') }}
        </x-ui.button>
      </div>

    @endforeach

    <x-slot name="footer">
      <div></div>
      <div>
        <x-ui.button type="button" theme="secondary" @click="open = false">{{ __('actions.cancel') }}</x-ui.button>
        <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
      </div>
    </x-slot>
  </x-ui.form>

@endif

