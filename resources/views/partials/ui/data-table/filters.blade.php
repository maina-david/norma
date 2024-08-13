@if($aside ?? false)
  @include('partials.ui.data-table.filter-inputs')
@else

  <x-ui.dropdown position="left" class="w-80">
    <x-slot name="trigger">
      <x-ui.button theme="tertiary" theme="gray"
                   size="px-4 py-2.5"
                   class="tippy mt-1 rounded-md {{ $searchable ? ($sideFilters ? 'rounded-l-none lg:rounded-l-md' : 'rounded-l-none') : '' }} {{ $sideFilters ? 'lg:hidden' : '' }}"
                   data-tippy-content="{{ __('interface.filters') }}">
        <x-ui.icon name="filter" class="text-base" />
      </x-ui.button>
    </x-slot>

    @include('partials.ui.data-table.filter-inputs', ['aside' => false])
  </x-ui.dropdown>
@endif


