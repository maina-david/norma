<x-ui.card>
  <x-collaborators.team.layout :team="$team">

    <div class="flex justify-end">

      @can('collaborate.payments.payment.create')
        <x-ui.confirm-button :route="route('collaborate.collaborators.teams.payment-requests.outstanding.pay', ['team' => $team])"
                             :label="__('payments.payment.pay_outstanding')"
                             method="POST"
                             styling="primary"
                             :confirmation="__('payments.payment.confirm_pay_outstanding')">
          {{ __('payments.payment.pay_outstanding') }}
        </x-ui.confirm-button>
      @endcan

    </div>
    @include('pages.crud.index-table')

  </x-collaborators.team.layout>
</x-ui.card>
