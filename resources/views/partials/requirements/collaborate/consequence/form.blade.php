<div
     x-data="{ cType: {{ old('consequence_type', $resource->consequence_type ?? \App\Enums\Requirements\ConsequenceType::fine()) }}, aType: {{ old('amount_type', $resource->amount_type ?? \App\Enums\Requirements\ConsequenceAmountType::currency()) }} }">
  <x-ui.input type="select" x-model="cType"
              :value="old('consequence_type', $resource->consequence_type ?? '')"
              name="consequence_type"
              label="{{ __('requirements.consequence.consequence_type') }}"
              :options="\App\Enums\Requirements\ConsequenceType::lang()" />

  <div x-show="cType != {{ \App\Enums\Requirements\ConsequenceType::other() }}">

    <x-ui.input type="select"
                :value="old('amount_prefix', $resource->amount_prefix ?? '')"
                name="amount_prefix"
                label="{{ __('requirements.consequence.amount_prefix_label') }}"
                :options="\App\Enums\Requirements\ConsequenceAmountPrefix::lang()" />

    <div class="flex" x-show="cType == {{ \App\Enums\Requirements\ConsequenceType::fine() }}">
      <div class="sm:w-full pr-1 md:w-1/3">
        <x-ui.input type="number"
                    step="0.01"
                    :value="old('amount', $resource->amount ?? '')"
                    name="amount"
                    label="{{ __('requirements.consequence.amount') }}" />
      </div>

      <div class="grow flex md:w-2/3">
        <div class="grow">
          <x-ui.input x-model="aType"
                      type="select"
                      :value="old('amount_type', $resource->amount_type ?? '')"
                      name="amount_type"
                      label="{{ __('requirements.consequence.amount_type_label') }}"
                      :options="\App\Enums\Requirements\ConsequenceAmountType::lang()" />

        </div>

        <div class="grow pl-2"
             x-show="aType == {{ \App\Enums\Requirements\ConsequenceAmountType::currency() }}">
          <x-geonames.currency-selector
                                        name="currency"
                                        :value="old('currency', $resource->currency ?? null)"
                                        label="{{ __('requirements.consequence.currency') }}" />
        </div>
      </div>
    </div>


    <div class="flex" style="display:none;"
         x-show="cType == {{ \App\Enums\Requirements\ConsequenceType::prison() }}">
      <div class="sm:w-full pr-1 md:w-1/2">
        <x-ui.input type="number"
                    :value="old('sentence_period', $resource->sentence_period ?? '')"
                    name="sentence_period"
                    label="{{ __('requirements.consequence.sentence_period') }}" />
      </div>

      <div class="sm:w-full pl-1 md:w-1/2">
        <x-ui.input type="select"
                    :value="old('sentence_period_type', $resource->sentence_period_type ?? '')"
                    name="sentence_period_type"
                    label="{{ __('requirements.consequence.period_label') }}"
                    :options="\App\Enums\Requirements\ConsequencePeriod::lang()" />
      </div>
    </div>

  </div>

  <div style="display:none;" x-show="cType == {{ \App\Enums\Requirements\ConsequenceType::other() }}">
    <x-ui.input
                :value="old('consequence_other_detail', $resource->consequence_other_detail ?? false)"
                name="consequence_other_detail"
                label="{{ __('requirements.consequence.details') }}" />
  </div>

  <x-ui.input :value="old('severity', $resource->severity ?? '')"
              name="severity"
              label="{{ __('requirements.consequence.severity') }}" />


  <div class="mt-4" x-show="cType != {{ \App\Enums\Requirements\ConsequenceType::other() }}">
    <x-ui.input type="checkbox"
                :value="old('per_day', $resource->per_day ?? false)"
                name="per_day"
                label="{{ ucwords(__('requirements.consequence.per_day')) }}" />
  </div>


  <x-slot name="footer">
    <div></div>
    <div>
      <x-ui.back-button :fallback="route('collaborate.assessment-items.index')" />
      <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
    </div>
  </x-slot>

</div>
