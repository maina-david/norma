<x-customer.libryo.my.settings.layout :libryo="$libryo">
  <div class="flex justify-end">
    @if(auth()->user()->isMySuperUser())
      <x-ui.form :action="route('my.settings.libryo.clone', ['libryo' => $libryo])" method="POST">

        <x-ui.button type="submit" theme="primary" styling="outline" class="mr-4">
          {{ __('actions.clone') }}
        </x-ui.button>
      </x-ui.form>
    @endif

    <x-ui.button :href="route('my.settings.libryos.edit', ['libryo' => $libryo])" type="link" theme="primary" styling="outline">{{ __('actions.edit') }}
    </x-ui.button>
  </div>
  <div class="text-center">
    <div class="mx-auto inline-block my-5">
      @if ($libryo->location)
        <x-ui.country-flag class="w-48 h-48 items-center flex justify-center text-xl p-10 bg-libryo-gray-100 rounded-full"
                           :country-code="$libryo->location->flag" />
      @endif

    </div>
    <div class="my-2 text-lg">{{ $libryo->title }}</div>
    <div class="text-sm ">{{ $libryo->location?->title }}</div>
    <div class="text-sm">{{ $libryo->address }}</div>
  </div>
</x-customer.libryo.my.settings.layout>
