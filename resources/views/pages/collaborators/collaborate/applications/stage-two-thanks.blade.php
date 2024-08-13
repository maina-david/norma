<x-layouts.guest>
  <div class="mt-4 flex justify-center grow max-w-4xl w-full px-2">
    <x-ui.card class="xl:max-h-full">

      <p class="text-center text-lg">
        {{ __('collaborators.thank_you_we_will_review', ['name' => $application->fname]) }}
      </p>

    </x-ui.card>
  </div>
</x-layouts.guest>
