<x-layouts.collaborate fluid>
  <x-ui.card>

    @include('pages.auth.collaborate.profile.partials.header')

    <div class="border-b border-norma-gray-200 mt-8 mb-6 -mx-4 sm:-mx-10"></div>

    <div class="text-lg font-semibold">{{ __('nav.activity') }}</div>

    <div class="border-b border-norma-gray-200 my-6 -mx-4 sm:-mx-10"></div>

    @include('pages.collaborators.collaborate.collaborator.partials.activities')


  </x-ui.card>

</x-layouts.collaborate>
