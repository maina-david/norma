<x-ui.input
    :label="__('collaborators.collaborator.contract_type')"
    name="contract_type"
    type="select"
    :options="\App\Enums\Collaborators\ContractType::forSelector(true)"
    :value="$resource->profile->contract->subtype ?? null"
/>

<div class="mt-4">
  <x-ui.input type="checkbox" name="contract_signed" :label="__('collaborators.contract_signed')" />
</div>
