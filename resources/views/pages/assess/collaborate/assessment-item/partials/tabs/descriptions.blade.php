<div>
  @can('collaborate.assess.assessment-item-description.create')
    <div class="flex justify-end mt-4">
      <x-ui.button type="link" href="{{ route('collaborate.assessment-items.descriptions.create', ['item' => $resource->id]) }}" theme="primary">{{ __('actions.add') }}</x-ui.button>
    </div>
  @endcan

  <div>
    <table class="w-full">
      <tbody>
        @if ($resource->descriptions->count() < 1)
          <tr>
            <x-ui.td>
              <div class="text-center">
                {{ __('interface.no_data') }}
              </div>
            </x-ui.td>
          </tr>
        @endif

        @foreach ($resource->descriptions as $description)
          <x-ui.tr :loop="$loop">

            <x-ui.td>
              <div class="text-libryo-gray-500 text-xs">
                {{ $description->location->title ?? __('assess.guidance_note.all_countries') }}
              </div>

              <div>{!! $description->description !!}</div>
            </x-ui.td>

            <td>
              <x-ui.table-action-buttons
                with-delete
                with-update
                base-route="collaborate.assessment-items.descriptions"
                base-permission="collaborate.assess.assessment-item-description"
                :resource-id="['item' => $resource->id, 'description' => $description->id]"
              />
            </td>

          </x-ui.tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
