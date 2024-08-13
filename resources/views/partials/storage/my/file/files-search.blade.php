<div>

  <x-ui.alert-box class="mb-3">
    <div class="font-semibold">{{ __('storage.drives.search_help_text') }}</div>
    <div>{{ __('storage.drives.search_help_text_2') }}</div>
  </x-ui.alert-box>

  <x-storage.my.file.file-data-table :base-query="$baseQuery"
                                     :route="route('my.drives.files.index')"
                                     searchable
                                     :fields="$tableFields"
                                     :search-placeholder="__('storage.drives.search_drives') . '...'"
                                     :focus-search="true"
                                     :paginate="15" />
</div>
