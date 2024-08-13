<x-layouts.guest>
  <div class="mt-4 flex justify-center grow max-w-4xl w-full px-2">
    <x-ui.card class="xl:max-h-full">

      <p class="text-center text-lg">
        {{ __('collaborators.thank_you') }} {{ $name }}!
      </p>

      <p class="text-center text-lg">
        {{ __('collaborators.application_number', ['number' => $number]) }}
      </p>

    </x-ui.card>
  </div>
</x-layouts.guest>
