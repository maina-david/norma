<div
    class="w-full bg-white py-8 px-4 rounded-lg sm:px-6 max-h-[90vh] h-full overflow-hidden flex flex-col"
    x-data="{
      search: '{{ request('search', '') }}',
      submitFilters: function (el) {
        var data = new FormData(el);
        var filters = Object.fromEntries(data);
        (new Set(Array.from(data.keys()))).forEach(function (item) {
          if (item.endsWith('[]')) {
            delete filters[item]
            filters[item.replace('[]', '')] = data.getAll(item);
          }
        });
        $dispatch('filtered', { filters });
      },
      resetFilters: function () {
        document.querySelectorAll('.page-filters input[type=checkbox]').forEach(function (item) { item.checked = false; });
        this.submitFilters(document.querySelector('.page-filter-form'));
      },
      performSearch: function () {
        $dispatch('applySearch', { search: this.search });
      },
      clearSearch: function () {
        this.search = '';
        this.performSearch();
      }
    }"
>
  @if($isSingleMode)
  <div class="flex-shrink-0 flex justify-center pb-2">
    <form class="max-w-2xl w-full" @submit.prevent="performSearch">
      <div class="relative flex items-center">
        <div class="flex-grow">
          <x-ui.input
            type="search"
            name="search"
            x-model="search"
            placeholder="{{ __('corpus.reference.search_requirements') }}"
          />
        </div>

        <div x-show="search.length > 0" class="absolute right-0 top-0 h-full flex items-center">
          <button class="flex items-center px-2 py-1" type="button" @click.prevent="clearSearch">
            <x-ui.icon name="times" />
          </button>
        </div>
      </div>
    </form>
  </div>

  <div class="flex flex-grow overflow-hidden bg-white border-t border-libryo-gray-200">
    <div class="overflow-hidden w-80 flex-shrink-0 pt-4 border-r border-libryo-gray-200">
      <div class="h-full w-full overflow-hidden">
        <form class="page-filter-form h-full w-full pl-1 flex flex-col" @submit.prevent="submitFilters($event.target)">

          <div class="mb-4 font-bold space-y-2 flex-shrink-0 pr-6">
            <div>{{ __('interface.filter_by') }}:</div>
            <div class="flex justify-between">
              <x-ui.button class="mr-4" styling="outline" type="link" @click.prevent="resetFilters" href="{{ url()->current() }}">
                {{ __('interface.reset_filters') }}
              </x-ui.button>

              <x-ui.button styling="outline" type="submit">
                {{ __('interface.apply_filters') }}
              </x-ui.button>
            </div>
          </div>

          <div class="page-filters border-b border-t border-libryo-gray-200 py-4 pl-2 pr-4 divide-y divide-libryo-gray-200 flex-grow overflow-y-auto">
            <livewire:ontology.category.category-tree
                :domains="$domains"
                :locations="$locations"
                :applied="$filters['categories'] ?? []"
            />

            <x-ontology.legal-domain.my.legal-domain-checkbox-filter
                :domains="$domains"
                :applied="$filters['domains'] ?? []"
            />

            <x-geonames.location.my.libryo-organisation-location-filter
                :locations="$locations"
                :applied="$filters['locations'] ?? []"
            />

            <x-compilation.context-question.my.applicability-recommendation-checkbox-filter :applied="$filters" />

            <x-compilation.context-question.my.applicability-inclusion-checkbox-filter :applied="$filters" />
          </div>

{{--          <div class="flex justify-between mt-4 mb-8">--}}
{{--            <x-ui.button class="mr-4" styling="outline" type="link" href="{{ url()->current() }}">--}}
{{--              {{ __('interface.reset_filters') }}--}}
{{--            </x-ui.button>--}}

{{--            <x-ui.button styling="outline" type="submit">--}}
{{--              {{ __('interface.apply_filters') }}--}}
{{--            </x-ui.button>--}}
{{--          </div>--}}

        </form>
      </div>
    </div>

    <div class="overflow-hidden flex-grow">
      <div class="h-full w-full overflow-hidden py-4" x-data="{ checkWorkChildren: function (sel, val) { document.querySelectorAll(sel).forEach(function (item) { item.checked = val; })} }">
        <livewire:compilation.context-question.applicability-requirements-listing
          lazy
          :filters="$filters"
          :key="Str::random(64)"
          :domains="$domains"
          :locations="$locations"
          :libryos="$libryos"
        />
      </div>
    </div>
  </div>
  @else
    <x-ui.empty-state-icon
      icon="up-left"
      :title="__('compilation.applicability.switch_to_single_stream')"
    />
  @endif
</div>
