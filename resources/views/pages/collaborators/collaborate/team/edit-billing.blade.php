<x-layouts.collaborate>
  <x-collaborators.team.billing-layout :team="$resource">

    <x-ui.form method="put" :action="route('collaborate.collaborators.teams.billing.update')">
      @include('partials.collaborators.collaborate.team.bank-details-form')
      <div class="flex mt-5 justify-end">
        <x-ui.button type="submit" theme="primary">
          {{ __('actions.save') }}
        </x-ui.button>
      </div>
    </x-ui.form>
  </x-collaborators.team.billing-layout>
</x-layouts.collaborate>
