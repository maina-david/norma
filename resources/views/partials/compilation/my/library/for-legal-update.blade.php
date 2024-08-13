<x-compilation.library.library-data-table
  :base-query="$baseQuery"
  :route="route('my.settings.compilation.library.for.legal-update.index', ['update' => $update])"
  :paginate="50"
  searchable
  :actionable="auth()->user()->can('my.notify.legal-update.update')"
  :actions="['remove_from_update']"
  :actions-route="route('my.settings.legal-updates.actions', ['update' => $update])"
>
</x-compilation.library.library-data-table>
