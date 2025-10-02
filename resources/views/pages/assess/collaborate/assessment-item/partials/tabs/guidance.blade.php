<div>
  @can('collaborate.assess.guidance-note.create')
    <x-ui.modal>
      <x-slot name="trigger">
        <div class="flex justify-end mt-4">
          <x-ui.button @click="open = true" theme="primary">{{ __('actions.add') }}</x-ui.button>
        </div>
      </x-slot>

      <div>
        <div class="text-lg">{{ __('assess.guidance_note.create_title') }}</div>
        <x-ui.form method="POST" :action="route('collaborate.guidance-notes.store', ['item' => $resource->id])">

          @include('partials.assess.collaborate.guidance-note.form', ['resource' => null, 'close' => true])


        </x-ui.form>

      </div>
    </x-ui.modal>
  @endcan

  <div>
    <table class="w-full">
      <tbody>
        @if ($resource->guidanceNotes->count() < 1)
          <tr>
            <x-ui.td>
              <div class="text-center">
                {{ __('interface.no_data') }}
              </div>
            </x-ui.td>
          </tr>
        @endif

        @foreach ($resource->guidanceNotes as $note)
          <x-ui.tr :loop="$loop">

            <x-ui.td>
              <div class="text-norma-gray-500 text-xs">
                {{ $note->location->title ?? __('assess.guidance_note.all_countries') }}
              </div>

              <div>{!! $note->description !!}</div>
            </x-ui.td>

            <td>
              <x-ui.table-action-buttons with-delete
                                         with-update
                                         base-route="collaborate.guidance-notes"
                                         base-permission="collaborate.assess.guidance-note"
                                         :resource-id="['item' => $resource->id, 'guidance_note' => $note->id]" />
            </td>

          </x-ui.tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
