<x-ui.input :value="old('name', $resource->bank_name ?? '')" name="bank_name"
            label="{{ __('collaborators.team.bank_name') }}" required />

<x-geonames.country-code-selector :value="old('name', $resource->bank_country ?? '')" name="bank_country"
                                  label="{{ __('collaborators.team.bank_country') }}" required no-code />

<x-ui.input :value="old('name', $resource->branch_code ?? '')" name="branch_code"
            label="{{ __('collaborators.team.branch_code') }}" />

<x-ui.input :value="old('name', $resource->account_name ?? '')" name="account_name"
            label="{{ __('collaborators.team.account_name') }}" required />

<x-ui.input :value="old('name', $resource->account_number ?? '')" name="account_number"
            label="{{ __('collaborators.team.account_number') }}" required />

<x-geonames.currency-selector :value="old('account_currency', $resource->account_currency ?? '')" name="account_currency"
                              label="{{ __('collaborators.team.account_currency') }}" required />

<x-ui.input :value="old('name', $resource->iban ?? '')" name="iban" label="{{ __('collaborators.team.iban') }}" />

<x-ui.input :value="old('name', $resource->swift_code ?? '')" name="swift_code"
            label="{{ __('collaborators.team.swift_code') }}" />

<x-ui.input :value="old('name', $resource->other_billing_instructions ?? '')" name="other_billing_instructions"
            label="{{ __('collaborators.team.other_billing_instructions') }}" />

<x-ui.input :value="old('name', $resource->billing_address ?? '')" name="billing_address"
            label="{{ __('collaborators.team.billing_address') }}" />
{{-- <x-ui.input :value="old('name', $resource->bank_details_plain ?? '')" name="bank_details_plain"
            label="{{ __('collaborators.team.bank_details_plain') }}" /> --}}
