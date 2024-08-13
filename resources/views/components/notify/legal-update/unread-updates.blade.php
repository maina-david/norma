<div>
  <x-ui.collapse show icon="bell" :title="__('notify.legal_update.unread_updates')">
    @if ($query->count() !== 0)
      <div class="-m-8">
        <turbo-frame id="unread-legal-updates={{ $user->id }}">
          <x-notify.legal-update.legal-update-data-table
                                                         :base-query="$query"
                                                         :route="route('my.notify.legal-updates.index')"
                                                         :fields="['simple']"
                                                         :headings="false"
                                                         simple-paginate
                                                         :paginate="$perPage" />
        </turbo-frame>
      </div>
    @else
      <x-ui.empty-state-icon icon="party-horn"
                             :title=" __('notify.legal_update.whoop_whoop')" />
    @endif
  </x-ui.collapse>
</div>
