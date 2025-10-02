<div>
  <a data-turbo-frame="_top" href="{{ route('my.notify.legal-updates.show', ['update' => $update]) }}"
     class="block">
    <div class="flex items-center ">
      <div class="min-w-0 flex-1 flex items-center max-w-screen-90">
        <div class="min-w-0 flex-1 px-4">
          <div>
            <p class="text-sm flex flex-row items-center">
              <span class="flex -space-x-2 overflow-hidden mr-3">
                @if ($update->primaryLocation)
                  <div class="w-5 h-5 inline-block mr-2 mb-1">
                    <x-ui.country-flag class="inline-block rounded-full ring-2 ring-white"
                                       :country-code="$update->primaryLocation->flag" />
                  </div>
                @endif
              </span>
              <span class="font-medium text-primary ">{{ $update->title }}</span>

            </p>
            <p class="mt-2 flex items-center text-sm text-norma-gray-500">
              <span class="truncate">
                {{ $update->legalDomains->implode('title', ', ') ?? '' }}
              </span>
            </p>
          </div>
        </div>
      </div>
      <div>
        <x-ui.icon name="chevron-right" size="3" />
      </div>
    </div>
  </a>
</div>
