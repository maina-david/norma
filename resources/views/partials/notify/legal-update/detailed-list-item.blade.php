<div>
  <a data-turbo-frame="_top" href="{{ route('my.notify.legal-updates.show', ['update' => $update]) }}"
     class="block group">
    <div class="flex items-center justify-between">
      <div class="min-w-0 flex-1 flex items-center max-w-screen-90">

        <div class="shrink-0 flex items-center pt-0.5">
          @include('bookmarks.bookmark-icon', ['bookmarks' => $update->bookmarks, 'turboKey' => "legal-update-{$update->id}", 'routePrefix' => 'my.legal-update.bookmarks', 'routePayload' => ['update' => $update->id], 'size' => 4])
        </div>

        <div class="shrink-0">
          <x-notify.legal-update.my.legal-update-read-status :update="$update">
            <x-ui.timestamp :timestamp="$update->notification_date" />
          </x-notify.legal-update.my.legal-update-read-status>
        </div>
        <div class="min-w-0 flex-1 px-4">
          <div>
            <p class="text-sm font-medium text-primary">
              <span class="overflow-hidden inline-flex">
                @if ($update->primaryLocation)
                  <span class="w-5 h-5 inline-block mr-2 mb-1">
                    <x-ui.country-flag class="inline-block rounded-full ring-2 ring-white"
                                       :country-code="$update->primaryLocation->flag" />
                  </span>
                @endif
              </span>
              {{ $update->title }}
            </p>
            <p class="mt-2 flex items-center text-sm text-norma-gray-500">
              <span class="truncate">
                {{ $update->legalDomains->implode('title', ', ') ?? '' }}
              </span>
            </p>
          </div>
        </div>
      </div>
      <div class="hidden md:block">
        @if (isset($update->normas_count))
          <span data-tippy-content="{{ __('customer.norma.applicable_norma_streams_this_many') }}"
                class="tippy inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-norma-gray-100 text-norma-gray-800 mr-3">
            {{ $update->normas_count }}
          </span>
        @endif
        <x-ui.icon name="chevron-right" size="3" />
      </div>
    </div>
  </a>
</div>
