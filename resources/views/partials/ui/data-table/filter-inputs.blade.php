@php
  $aside = $aside ?? false;
@endphp
<div class="p-6 w-80{{ $aside ? '' : ' overflow-auto custom-scroll' }}" style="{{ $aside ? '' : 'max-height:60vh;' }}">
  <div class="mb-4 font-bold flex items-center justify-between flex-wrap space-y-2">
    <div>{{ __('interface.filter_by') }}:</div>
    @if ($submitToFilter && !$noTopFilter)
      <div class="flex justify-between">
        <x-ui.button class="mr-4" styling="outline" type="link" href="{{ url()->current() }}">
          {{ __('interface.reset_filters') }}
        </x-ui.button>

        <x-ui.button styling="outline" type="button" @click="submitFilterForm()">
          {{ __('interface.apply_filters') }}
        </x-ui.button>
      </div>
    @endif
  </div>
  @foreach ($selectedFilters as $key => $filter)
    @if (isset($filter['render']) && !is_null($filter['render']))
      <div class="font-medium text-sm mb-1 filter-label">{{ ucwords(strtolower($filter['label'])) }}</div>
      <div @changed="$dispatch('filtered', { value: $event.detail, filter: '{{ $key }}' })" class="mb-5">
        {!! $renderFilter($key) !!}
      </div>
    @endif
  @endforeach

  @if ($submitToFilter)
    <div class="flex justify-between">
      <x-ui.button class="mr-4" styling="outline" type="link" href="{{ url()->current() }}">
        {{ __('interface.reset_filters') }}
      </x-ui.button>

      <x-ui.button styling="outline" type="button" @click="submitFilterForm()">
        {{ __('interface.apply_filters') }}
      </x-ui.button>
    </div>
  @endif
</div>
