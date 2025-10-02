<x-customer.norma.my.settings.layout :norma="$norma">
  <div class="flex justify-end">
    @if(auth()->user()->isMySuperUser())
      <x-ui.form :action="route('my.settings.norma.clone', ['norma' => $norma])" method="POST">

        <x-ui.button type="submit" theme="primary" styling="outline" class="mr-4">
          {{ __('actions.clone') }}
        </x-ui.button>
      </x-ui.form>
    @endif

    <x-ui.button :href="route('my.settings.normas.edit', ['norma' => $norma])" type="link" theme="primary" styling="outline">{{ __('actions.edit') }}
    </x-ui.button>
  </div>
  <div class="text-center">
    <div class="mx-auto inline-block my-5">
      @if ($norma->location)
        <x-ui.country-flag class="w-48 h-48 items-center flex justify-center text-xl p-10 bg-norma-gray-100 rounded-full"
                           :country-code="$norma->location->flag" />
      @endif

    </div>
    <div class="my-2 text-lg">{{ $norma->title }}</div>
    <div class="text-sm ">{{ $norma->location?->title }}</div>
    <div class="text-sm">{{ $norma->address }}</div>
  </div>
</x-customer.norma.my.settings.layout>
