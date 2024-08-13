<x-layouts.collaborate>
  <x-ui.card class="no-shadow">
    <x-collaborators.team.billing-layout :team="$team">

      <turbo-frame id="collaborate.collaborators.teams.payments.index"
                   src="{{ route('collaborate.collaborators.teams.payments.index', ['team' => $team->id]) }}">
      </turbo-frame>
      {{-- @include('pages.crud.index-table') --}}

    </x-collaborators.team.billing-layout>
  </x-ui.card>
</x-layouts.collaborate>
