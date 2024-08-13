<div class="px-4 py-4 sm:px-0" id="sharable-app">
  <div class="rounded-lg {{ $tableClass ?? '' }}">

    <x-ui.collaborate.crud-data-table
        :filterable="!empty($filters)"
        :actionable="!empty($actions)"
        :fields="array_keys($fields)"
        :filters="array_keys($filters)"
        :actions="array_keys($actions)"
        :available-actions="$actions"
        :available-filters="$filters"
        :current-sort="$currentSort ?? null"
        :sortable="!empty($availableSorts ?? [])"
        :available-sorts="$availableSorts ?? []"
        :searchable="$searchable"
        :baseQuery="$baseQuery"
        :route="route($baseRoute . '.index', $baseRouteParams ?? [])"
        :actionsRoute="$actionsRoute"
        :available-fields="$fields"
        :no-data="$noData ?? false"
        :headings="$headings ?? true"
        :paginate="$paginate ?? 25"
        :no-filter-scroll="$noFilterScroll ?? false"
        submit-to-filter
    />

  </div>
</div>
