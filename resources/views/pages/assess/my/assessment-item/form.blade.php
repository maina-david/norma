@php
  use App\Enums\Assess\ReassessInterval;
  use App\Enums\Assess\RiskRating;

  $isEdit = (bool) ($resource->id ?? false);
@endphp
<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.back-referrer-button fallback="{{ route('my.assess.assessment-item-responses.index') }}"/>
      <div class="ml-4">{{ $isEdit ? __('assess.assessment_item.edit_title') : __('assess.assessment_item.create_title') }}</div>
    </div>
  </x-slot>

  <div>
    <x-ui.card
      class="shadow-md max-w-5xl"
      x-data="{ reassess: {{ json_encode(old('needs_reassessment', (bool) ($resource->frequency ?? false))) }} }"
    >

      <x-ui.form method="{{ $isEdit ? 'PUT' : 'POST' }}" action="{{ $isEdit ? route('my.assess.update', ['assess' => $resource->hash_id]) : route('my.assess.store') }}">
        <x-ui.input
            :value="old('title', $resource->title ?? '')"
            name="title"
            label="{{ __('arachno.source.title') }}"
            required
        />

        <x-ui.input
            type="select"
            :value="old('legal_domain_id', $resource->legal_domain_id ?? '')"
            name="legal_domain_id"
            label="{{ __('ontology.legal_domain.category') }}"
            :options="$legalDomains"
        />

        <x-ui.input
            type="select"
            :value="old('admin_category_id', $resource->admin_category_id ?? '')"
            name="admin_category_id"
            label="{{ __('assess.assessment_item.control_topic') }}"
            :options="$controlTopics"
        />

        <div class="mt-4">
          <x-ui.label for="risk_rating">
            <span class="text-red-400">*</span>
            <span>{{ __('assess.risk_rating') }}</span>
          </x-ui.label>

          <div class="flex items-center space-x-6 mt-2">
            @foreach(RiskRating::forSelector() as $item)
              @if($item['value'] != 0)
                <div class="flex items-center">
                  <x-ui.input
                      type="radio"
                      name="risk_rating"
                      id="risk_rating_{{ $item['value'] }}"
                      :checkbox-value="$item['value']"
                      :value="old('risk_rating', $resource->risk_rating ?? '') == $item['value']"
                      required
                  />

                  <x-ui.label for="risk_rating_{{ $item['value'] }}">
                    {{ $item['label'] }}
                  </x-ui.label>
                </div>
              @endif
            @endforeach
          </div>
        </div>


        <div class="mt-8">
          <x-ui.input
              type="checkbox"
              x-model="reassess"
              name="needs_reassessment"
              label="{{ __('assess.assessment_item.needs_reassessment') }}"
          />
        </div>

        <template x-if="reassess">
          <div class="flex space-x-4">
            <x-ui.input
                type="number"
                min="1"
                name="frequency"
                label="{{ __('assess.assessment_item.repeats_every') }}"
                :value="old('frequency', $resource->frequency ?? '')"
            />

            <x-ui.input
                :tomselect="false"
                :options="ReassessInterval::forSelector()"
                type="select"
                name="frequency_interval"
                label="&nbsp;"
                :value="old('frequency_interval', $resource->frequency_interval ?? ReassessInterval::MONTH->value)"
            />
          </div>
        </template>


        <div class="flex justify-end">
          @if(!($resource->id ?? false))
            <x-ui.modal>

              <x-slot name="trigger">
                <x-ui.button theme="primary" @click="open = true">
                  {{ __('actions.save') }}
                </x-ui.button>
              </x-slot>

              <div class="mb-4 max-w-xl px-4">
                <div class="font-semibold text-xl mb-2">{{ __('assess.assessment_item.confirmation') }}</div>
                <p class="py-1">{{ __('assess.assessment_item.create_confirmation_line_1') }}</p>
                <p class="py-1">{{ __('assess.assessment_item.create_confirmation_line_2') }}</p>
                <p class="py-1">{{ __('actions.confirmation') }}</p>
              </div>

              <x-slot name="footer">
                <div class="px-4 pb-4 flex justify-end flex-grow space-x-4">
                  <button @click="open = false" type="button"
                          class="mt-3 w-full inline-flex justify-center rounded-md border border-libryo-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-libryo-gray-700 hover:bg-libryo-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">
                    {{ __('actions.cancel') }}
                  </button>

                  <button type="submit"
                          class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto sm:text-sm bg-primary hover:bg-primary focus:ring-primary">
                    {{ __('assess.assessment_item.confirm') }}
                  </button>
                </div>
              </x-slot>

            </x-ui.modal>
          @else
            <x-ui.button theme="primary" type="submit">
              {{ __('actions.save') }}
            </x-ui.button>
          @endif

        </div>


      </x-ui.form>

    </x-ui.card>
  </div>
</x-layouts.app>
