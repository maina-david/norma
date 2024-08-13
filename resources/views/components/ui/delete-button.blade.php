@props(['route', 'styling', 'buttonTheme', 'size', 'dataTurboFrame', 'confirmation' => __('actions.delete_confirmation')])

<x-ui.confirm-button {{ $attributes }} :route="$route" :button-theme="$buttonTheme ?? 'negative'"
                     :styling="$styling ?? 'outline'"
                     :label="__('actions.delete')" method="DELETE"
                     :data-turbo-frame="$dataTurboFrame ?? ''"
                     :confirmation="$confirmation" danger :size="$size ?? 'md'">
  <x-slot:formBody>{{ $formBody ?? null }}</x-slot:formBody>



  {{ $slot ?? __('actions.delete') }}
</x-ui.confirm-button>
