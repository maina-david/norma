<dl class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
  <div
       class="relative bg-white py-5 px-4 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden flex flex-col justify-between">
    <p class="text-sm font-medium text-libryo-gray-500 mb-4 text-center">{{ __('auth.user.days_active') }}</p>

    <div class="flex items-center justify-center">
      <dt>
        <div class="rounded-md">
          <x-ui.icon name="heartbeat" class="text-primary" size="8" />
        </div>
      </dt>
      <dd class="ml-4 flex">
        <p class="text-2xl font-semibold text-libryo-gray-900">
          {{ $daysActive }}
        </p>
      </dd>
    </div>
  </div>

  <div
       class="relative bg-white py-5 px-4 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden flex flex-col justify-between">
    <p class="text-sm font-medium text-libryo-gray-500 mb-4 text-center">
      {{ __('notify.legal_update.updates_received_this_month') }}
    </p>

    <div class="flex items-center justify-center">
      <dt>
        <div class="rounded-md p-1">
          <x-ui.icon name="bell" class="text-secondary" size="8" />
        </div>
      </dt>
      <dd class="ml-4 flex items-center">
        <p class="text-2xl font-semibold text-libryo-gray-900">
          {{ $updatesCount }}
        </p>
      </dd>
    </div>
  </div>

  <div
       class="relative bg-white py-5 px-4 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden flex flex-col justify-between">
    <p class="text-sm font-medium text-libryo-gray-500 mb-4 text-center">{{ __('customer.libryo.libryo_streams') }}</p>

    <div class="flex items-center justify-center">
      <dt>
        <div class="rounded-md p-1">
          <x-ui.icon name="map-marker" class="text-tertiary" size="8" />
        </div>
      </dt>
      <dd class="ml-4 flex items-center">
        <p class="text-2xl font-semibold text-libryo-gray-900">
          {{ $libryoStreamsCount }}
        </p>
      </dd>
    </div>
  </div>
</dl>
