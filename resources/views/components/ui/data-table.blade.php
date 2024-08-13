@props(['actionable', 'filterable', 'sortable', 'searchable', 'baseQuery', 'route', 'actionsRoute', 'focusSearch'])

{{-- submitForm function first disables any filter inputs that have
    empty value so it doesn't get added as query param --}}
<div x-data="{
    search: '',
    selected: typeof selected === 'undefined' ? {} : selected,
    selectAllNone: false,
    actionsDisabled: true,
    rows: {{ !is_null($rows) ? str_replace('"', '\'', json_encode($rows->pluck('id')->toArray())) : '[]' }},
    submitFilterForm: function() {
        $refs['filters-form']
            .querySelectorAll('input')
            .forEach((inp) => inp.value === '' ? inp.setAttribute('disabled', true) : '');
        $refs['filters-form'].requestSubmit();
    },
    submitActionForm: function(action) {
        $refs['data-table-actions-action-{{ $attributes->id ?? '' }}'].value = action;
        $refs['data-table-actions-form-{{ $attributes->id ?? '' }}'].requestSubmit();
    },
    handleFilter: function($event) {
        var el = $refs['filter-inputs-' + $event.detail.filter];
        if (el.type === 'select-multiple') {
            if (Array.isArray($event.detail.value)) {
                Array.from(el.children).forEach(function(n) { n.remove(); })
                $event.detail.value.forEach(function(selectedValue) { this.populateFilter(el, selectedValue); }.bind(this));
            } else {
                this.populateFilter(el, $event.detail.value);
            }

            Array.from(el.options).forEach(function(opt) {
                opt.selected = true;
            });
        } else {
            el.value = $event.detail.value;
        }

        @if(!$submitToFilter)
        this.submitFilterForm();
        @endif
    },
    populateFilter(el, value) {
        var exists = Array.from(el.querySelectorAll('option')).some(function(item) { return item.value == value; });

        if (!exists) {
            var opt = document.createElement('option');
            opt.value = value;
            el.add(opt);
        }
    },
    handleSort: function($event) {
        if (!$event.target.selectedOptions[0]) {
            return;
        }
        var filt = document.createElement('input');
        filt.setAttribute('type', 'hidden');
        filt.setAttribute('name', 'sort')
        filt.setAttribute('value', $event.target.selectedOptions[0].value)

        var form = $refs['filters-form'];
        form.appendChild(filt);
        this.submitFilterForm();
    },
    removeMultipleFilter: function(filter, value) {
        var el = $refs['filter-inputs-' + filter];
        Array.from(el.options).forEach(function(opt) {
            if (opt.value == value) {
                opt.selected = false;
            }
        });

        this.submitFilterForm();
    },
}"
     x-effect="
      found = false;
      Object.keys(selected).forEach(function(k) {
        if(selected[k]) { found = true; actionsDisabled = false; }
      });
      if(found===false) { actionsDisabled = true };"
     @if ($focusSearch)
  x-init="$refs.searchContainer.querySelector('input').focus();"
  @endif
  class="flex flex-col"
>
  <div class="-my-2">
    <div class="py-2 align-middle inline-block min-w-full w-full">
      <div class="shadow border-b border-libryo-gray-200 rounded-lg w-full ">
        @if ($primaryFilter !== '')
          <div class="flex flex-row justify-between bg-white px-5 pt-3">
            <div>
              <a class="inline-block text-primary border-primary mr-5 p-2 {{ request()->has($primaryFilter) ? '' : 'border-b' }}"
                 href="{{ route(request()->route()->getName(),request()->except([$primaryFilter, 'page'])) }}">
                {{ __('corpus.location_type.all') }}
              </a>

              @foreach ($primaryFilterOptions as $filterOption)
                <a class="inline-block text-primary border-primary mr-5 p-2 {{ request()->query($primaryFilter) == $filterOption['value'] ? 'border-b' : '' }}"
                   href="{{ route(request()->route()->getName(),array_merge(request()->except(['page', 'perPage']), [$primaryFilter => $filterOption['value']])) }}">
                  {{ $filterOption['label'] }}
                </a>
              @endforeach
            </div>
            <div>
              {{-- $inlineWithPrimaryFilter slot --}}
              {{ $inlineWithPrimaryFilter ?? '' }}
            </div>
          </div>
        @endif

        {{-- HEADER - Filters, actions search --}}
        <div class="bg-white w-full flex flex-col lg:flex-row px-2 print:hidden">
          <div>
            {{-- $header slot --}}
            {{ $header ?? '' }}
          </div>

          {{-- Form for filters and search with hidden inputs for each filter --}}
          <x-ui.form x-ref="filters-form" method="get" action="{!! $getFilterRoute() !!}">
            @php
              $extraQuery = parse_url($getFilterRoute())['query'] ?? '';
              parse_str($extraQuery, $extraItems);
            @endphp

            @foreach ($extraItems as $itemName => $itemValue)
              @if ($itemName !== 'search')
                <input type="hidden" name="{{ $itemName }}" value="{{ $itemValue }}">
              @endif
            @endforeach

            @foreach ($selectedFilters as $key => $filter)
              @if ($key === 'search')
                @continue
              @endif
              @if (is_array($getFilterValue($key)))
                <select x-ref="filter-inputs-{{ $key }}" class="hidden tomselected" multiple
                        name="{{ $key }}[]">
                  @foreach ($getFilterValue($key) as $v)
                    <option value="{{ $v }}" selected></option>
                  @endforeach
                </select>
              @else
                <input x-ref="filter-inputs-{{ $key }}" type="hidden" name="{{ $key }}"
                       value="{{ $getFilterValue($key) }}" />
              @endif
            @endforeach

            @if ($searchable)
              <input type="hidden" x-ref="filter-inputs-search" name="search"
                     value="{{ $getFilterValue('search') }}" />
            @endif
          </x-ui.form>
          {{-- END form --}}

          {{-- Buttons and search bar --}}
          <div class="flex flex-col lg:flex-row items-center justify-between flex-grow p-2">

            {{-- LEFT --}}
            <div class="w-full lg:w-1/4">
              <div class="flex justify-start">
                @if ($actionable && count($selectedActions) > 0 && ($noData || count($rows) > 0))
                  @if($sideFilters)
                    <div class="hidden 2xl:block w-72 max-w-sm flex-shrink-0 mr-3"></div>
                  @endif
                  <x-ui.dropdown class="w-56">
                    <x-slot name="trigger">
                      <x-ui.button x-show="!actionsDisabled" class="mt-1" styling="outline" theme="primary">
                        <x-ui.icon name="square-check" />
                        <x-ui.icon name="angle-down" class="ml-4" />
                      </x-ui.button>
                    </x-slot>

                    {{-- ACTIONS LIST --}}
                    <div class="py-1" role="none">
                      @foreach ($selectedActions as $actionName => $action)
                        <button type="button"
                                @click="submitActionForm('{{ $actionName }}')"
                                form="data-table-actions-form-{{ $attributes->id ?? '' }}"
                                class="text-libryo-gray-700 block px-4 py-2 text-sm hover:bg-libryo-gray-200 hover:text-libryo-gray-900 w-full text-left"
                                role="menuitem" tabindex="-1">
                          {{ $action['label'] }}
                        </button>
                      @endforeach
                    </div>
                  </x-ui.dropdown>

                  <div id="actions-modal-target"></div>
                @endif

                {{-- leftActions slot --}}
                <div class="ml-3 mt-1">
                  {{ $leftActions ?? '' }}
                </div>
              </div>
            </div>
            {{-- END LEFT --}}

            {{-- MIDDLE --}}
            <div class="w-full lg:w-1/2 flex justify-center" @filtered="handleFilter($event)">
              @if ($searchable)
                <div class="flex items-center flex-grow justify-center">
                  @include('partials.ui.data-table.search-field')

                  @if ($filterable)
                    @include('partials.ui.data-table.filters')
                  @endif
                </div>
              @endif
            </div>
            {{-- END MIDDLE --}}

            {{-- RIGHT --}}
            <div @filtered="handleFilter($event)" class="w-full md:w-1/4 flex justify-end">

              @if ($filterable && !$searchable)
                @include('partials.ui.data-table.filters')
              @endif

              {{-- actionButton slot --}}
              <div class="ml-3 mt-1">
                {{ $actionButton ?? '' }}
              </div>


              @if ($sortable && !empty($availableSorts))
                <x-ui.dropdown position="left">
                  <x-slot name="trigger">
                    <x-ui.button theme="tertiary" styling="flat"
                                 class="mt-1 {{ $filterable ? 'rounded-l-none' : '' }}">
                      <x-ui.icon name="sort-alt" class="text-base" />
                    </x-ui.button>
                  </x-slot>

                  <div class="px-4 py-2 w-72 max-w-screen-75">
                    <x-ui.input
                                type="select"
                                :options="$availableSorts"
                                :value="$currentSort"
                                name="sort"
                                :label="__('interface.sort')"
                                @change="handleSort($event)" />
                  </div>
                </x-ui.dropdown>
              @endif
            </div>
            {{-- END RIGHT --}}

          </div>
          {{-- END HEADER --}}

        </div>

        {{-- Active Filters --}}
        @if (!$sideFilters || count($getActiveFilters()) > 0)
          <div
               class="flex justify-center py-4 px-3 items-center w-full bg-white {{ $noFilterScroll ? 'flex-wrap' : 'overflow-x-auto' }}">
            @foreach ($getActiveFilters() as $filter => $value)
              @if ($sideFilters && $filter !== 'search')
                @continue
              @endif
              @if (is_array($value))
                @foreach ($value as $val)
                  <x-ui.badge class="ml-1 whitespace-nowrap mt-1"
                              closable
                              @closed="removeMultipleFilter('{{ $filter }}', {{ is_string($val) ? '\'' . $val . '\'' : $val }} )">
                    {{ $selectedFilters[$filter]['label'] ? $selectedFilters[$filter]['label'] . ':' : '' }} <span
                          class="ml-1 bg-white text-secondary px-3 rounded-full max-w-xs truncate tippy"
                          data-tippy-content="{{ $getActiveFilterLabel($filter, $val) }}">{{ $getActiveFilterLabel($filter, $val) }}</span>
                  </x-ui.badge>
                @endforeach
              @else
                <x-ui.badge class="ml-1 whitespace-nowrap mt-1"
                            closable
                            @closed=" search.value='' ; $refs['filter-inputs-{{ $filter }}'].value=''; submitFilterForm(); ">
                  {{ $selectedFilters[$filter]['label'] ? $selectedFilters[$filter]['label'] . ':' : '' }} <span
                        class=" ml-1 bg-white text-secondary px-3 rounded-full max-w-xs truncate tippy"
                        data-tippy-content="{{ $getActiveFilterLabel($filter, $value) }}">
                    {{ $getActiveFilterLabel($filter, $value) }}</span>
                </x-ui.badge>
              @endif
            @endforeach
          </div>
        @endif
        {{-- END Buttons and search bar --}}

        {{-- END Header --}}
        @if (isset($prepend) || (!is_null($rows) && ($sideFilters || count($rows) > 0)))
          <x-ui.form
            id="data-table-actions-form-{{ $attributes->id ?? '' }}"
            x-ref="data-table-actions-form-{{ $attributes->id ?? '' }}"
            method="POST"
            action="{{ $actionable ? $actionsRoute : '' }}"
            class="w-full overflow-x-auto bg-white"
          >
            <input type="hidden" x-ref="data-table-actions-action-{{ $attributes->id ?? '' }}" value="" name="action" />
          </x-ui.form>
          @isset($body)
            {{ $body }}
          @else
            <div class="flex max-w-full min-w-full h-full bg-white border-t border-libryo-gray-200">
              @if ($filterable && $sideFilters)
                <div class="flex-shrink-0 max-w-sm side-filter hidden lg:block border-r border-libryo-gray-200" @filtered="handleFilter($event)">
                  @include('partials.ui.data-table.filters', ['aside' => true])
                </div>
              @endif

              <div class="flex-grow overflow-hidden">
                <div class="flex-grow overflow-x-auto custom-scroll table-wrapper">
                  @if(isset($tableBody))
                    {{ $tableBody }}
                  @else
                    <table class="w-full divide-y divide-libryo-gray-200 bg-white table-auto">
                      <thead class="bg-white {{ $stickyHeadings ? 'sticky' : '' }}">
                      <tr>
                        @if ($actionable)
                          <th>
                            <x-ui.input @click="selectAllNone = !selectAllNone; rows.forEach((rId) => selected[rId] = selectAllNone ? true : false)"
                                        type="checkbox" name="select-all-checkbox"
                                        class="mx-3" />
                          </th>
                        @endif
                        @if ($headings)
                          @foreach ($selectedFields as $key => $field)
                            <x-ui.th class="{{ isset($field['align']) ? 'text-' . $field['align'] : '' }}">
                              {{ $field['heading'] }}
                            </x-ui.th>
                          @endforeach
                        @endif
                      </tr>
                      </thead>
                      <tbody>
                      {!! $prepend ?? '' !!}
                      @foreach ($rows as $row)
                        <x-ui.tr :loop="$loop" :striped="$striped ?? true" data-id="{{ $row['id'] }}">
                          @if ($actionable)
                            <td>
                              <x-ui.input x-model="selected[{{ $row['id'] }}]"
                                          type="checkbox"
                                          form="data-table-actions-form-{{ $attributes->id ?? '' }}"
                                          name="actions-checkbox-{{ $row['id'] }}" class="mx-3" />
                            </td>
                          @endif
                          @foreach ($selectedFields as $key => $field)
                            <td
                                {{ isset($field['colspan']) ? "colspan={$field['colspan']}" : ''  }}
                                class="{{ $field['classes'] ?? 'px-4 py-2 print:py-1 whitespace-normal text-sm font-medium text-libryo-gray-900' }} {{ isset($field['align']) ? ' text-' . $field['align'] : '' }}">
                              {!! $renderField($key, $row) !!}
                            </td>
                          @endforeach
                        </x-ui.tr>
                      @endforeach
                      </tbody>

                    </table>
                  @endif
                </div>

                @if(isset($customPagination))
                  {{ $customPagination }}
                @else
                  <div class="p-4">
                    @if ($rows->hasPages())
                      {{ $rows->links() }}
                    @endif
                  </div>
                @endif
              </div>
            </div>
          @endisset
        @endif

        <div>
          {{-- $footer slot --}}
          {{ $footer ?? '' }}
        </div>

      </div>
    </div>
  </div>
</div>
