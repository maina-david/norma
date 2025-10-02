<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="clipboard-list" class="mr-3 ml-5  text-norma-gray-400" size="8" />
      <div>
        {{ __('assess.assess_dashboard') }}
        <span class="text-xs text-norma-gray-500 italic ml-3">{{ $subTitle }}</span>
      </div>
    </div>
  </x-slot>

  @if ($noResponses && !$isFiltered)
    <div class="mt-20">
      <x-ui.empty-state-icon icon="clipboard-list"
                             :title="__('assess.no_responses', ['title' => $subTitle])"
                             :subline="userIsOrgAdmin() ? __('assess.setup.head_over_to_setup') : __('assess.setup.contact_your_admin')" />
    </div>
    @if ($isSingleMode && userIsOrgAdmin())
      <div class="mt-20 flex flex-col items-center">
        <x-ui.button type="link" :href="route('my.settings.normas.show', ['norma' => $norma, 't' => 'assess'])"
                     theme="secondary" size="xl">{{ __('settings.nav.assess_setup') }}</x-ui.button>
      </div>
    @endif
  @else
    <div class="print:hidden">
      <x-assess.assessment-item-response.response-data-table
                                                             :route="route('my.assess.dashboard')"
                                                             filterable
                                                             :filters="['domains', 'rating', 'answered', 'controls', 'answer']"
                                                             :no-data="true" />
    </div>

    @if(!$noResponses)

      <div>
        <div class="mt-2 grid grid-cols-1 gap-8 md:grid-cols-2">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <x-assess.risk-rating-widget :risk="$risk->value" />
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <x-assess.responses-chart :data="$responsesChartData" />
            </div>
          </div>
        </div>
      </div>

      <div>
        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          @foreach ($stats as $key => $stat)
            @if (!in_array($key, ['no', 'high', 'medium', 'percentage']))
              <div class="flex flex-col justify-center text-center px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-norma-gray-500 ">
                  {{ $stat['label'] }}
                </dt>
                <dd
                    class="mt-1 text-3xl font-semibold text-norma-gray-700 {{ isset($stat['color']) ? 'text-' . $stat['color'] : '' }}">
                  @if (isset($stat['last_answered']))
                    @if ($stat['value'])
                      <x-ui.timestamp :timestamp="$stat['value']" />
                    @endif
                  @else
                    {{ $stat['value'] }}
                  @endif

                </dd>
              </div>
            @endif
          @endforeach

          <div class="text-center px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6 col-span-2 md:col-span-3 lg:col-span-2">
            <dt class="text-sm font-medium text-norma-gray-500 ">
              {{ $stats['no']['label'] }}
            </dt>
            <dd class="mt-1 text-3xl font-semibold text-norma-gray-700 {{ isset($stats['no']['color']) ? 'text-' . $stats['no']['color'] : '' }}">

              <div>{{ $stats['no']['value'] }}</div>

              <div>
                <div class="flex justify-between text-sm font-medium text-norma-gray-500">
                  <div>{{ $stats['high']['label'] }}</div>
                  <div>{{ $stats['high']['value'] }}</div>
                </div>
                <div class="flex justify-between text-sm font-medium text-norma-gray-500">
                  <div>{{ $stats['medium']['label'] }}</div>
                  <div>{{ $stats['medium']['value'] }}</div>
                </div>
                <div class="flex justify-between text-sm font-medium text-norma-gray-500">
                  <div>{{ $stats['percentage']['label'] }}</div>
                  <div>{{ $stats['percentage']['value'] }}</div>
                </div>

              </div>


            </dd>
          </div>

        </dl>
      </div>

      <div class="mt-10">
        <turbo-frame id="assess-metrics-dashboard" src="{{ route('my.assess.assessment-item-responses.metrics') }}" loading="lazy">
          <x-ui.skeleton />
        </turbo-frame>
      </div>

    @endif

  @endif

</x-layouts.app>
