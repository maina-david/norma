@if($forTurbo)
  <x-ui.card class="xl:max-h-full">
    @include('general.stepper')
  </x-ui.card>
@else
  <x-layouts.collaborate>
    <div class="mt-4 flex justify-center grow w-full px-2">
      <x-ui.card class="xl:max-h-full">

        @include('general.stepper')

      </x-ui.card>
    </div>
  </x-layouts.collaborate>
@endif
