<x-layouts.settings :header="__('settings.nav.legal_updates')">
  <x-notify.legal-update.my.legal-update-data-table
      :base-query="$baseQuery"
      :route="route('my.settings.legal-updates.index')"
      searchable
      filterable
      :filters="['jurisdiction', 'publish-status']"
      :paginate="50"
  />
</x-layouts.settings>
