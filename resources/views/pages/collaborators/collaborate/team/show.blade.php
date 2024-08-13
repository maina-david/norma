<x-collaborators.team.layout :team="$resource">
  <x-ui.show-field :label="__('collaborators.team.title')" :value="$resource->title" />
  <div class="grid md:grid-cols-2 gap-4">

    <x-ui.show-field :label="__('collaborators.team.currency')" :value="$resource->currency" />

    <x-ui.show-field :label="__('collaborators.team.bank_name')" :value="$resource->bank_name" />

    <x-ui.show-field :label="__('collaborators.team.bank_country')" :value="$resource->bank_country" />

    <x-ui.show-field :label="__('collaborators.team.branch_code')" :value="$resource->branch_code" />

    <x-ui.show-field :label="__('collaborators.team.account_name')" :value="$resource->account_name" />

    <x-ui.show-field :label="__('collaborators.team.account_number')" :value="$resource->account_number" />

    <x-ui.show-field :label="__('collaborators.team.account_currency')" :value="$resource->account_currency" />

    <x-ui.show-field :label="__('collaborators.team.iban')" :value="$resource->iban" />

    <x-ui.show-field :label="__('collaborators.team.swift_code')" :value="$resource->swift_code" />

    <x-ui.show-field :label="__('collaborators.team.other_billing_instructions')"
                     :value="$resource->other_billing_instructions" />

    <x-ui.show-field :label="__('collaborators.team.billing_address')" :value="$resource->billing_address" />

    <x-ui.show-field :label="__('collaborators.team.bank_details_plain')" :value="$resource->bank_details_plain" />

    <x-ui.show-field :label="__('collaborators.team.transferwise_id')" :value="$resource->transferwise_id" />


  </div>

  <x-ui.show-field :label="__('collaborators.team.created_at')">
    <x-ui.timestamp :timestamp="$resource->created_at" />
  </x-ui.show-field>

  <div class="mt-10">
    <x-ui.show-field type="checkbox" :label="__('collaborators.team.auto_pay')" :value="$resource->auto_pay" />
  </div>
</x-collaborators.team.layout>
