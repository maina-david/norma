@php
use App\Enums\Auth\LifecycleStage;
$getColor = function ($stage) {
    return match ($stage) { LifecycleStage::active()->value => 'positive',  LifecycleStage::deactivated()->value => 'negative',  LifecycleStage::deactivatedInactivity()->value => 'negative',  default => 'warning' };
};
@endphp

<div class="flex flex-col mt-5">
  <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
      <div class="shadow overflow-hidden border-b border-norma-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-norma-gray-200">
          <thead class="bg-norma-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-norma-gray-500 uppercase tracking-wider">
                {{ __('log.user_lifecycle_activity.from') }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-norma-gray-500 uppercase tracking-wider">
                {{ __('log.user_lifecycle_activity.to') }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-norma-gray-500 uppercase tracking-wider">
                {{ __('timestamps.date') }}
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach ($activities as $activity)
              <tr class="bg-white">
                <td
                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-norma-gray-900 text-{{ $getColor($activity->from_lifecycle) }}">
                  {{ LifecycleStage::lang()[$activity->from_lifecycle] ?? '' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-norma-gray-500 text-{{ $getColor($activity->to_lifecycle) }}"">
                  {{ LifecycleStage::lang()[$activity->to_lifecycle] ?? '' }}
                </td>
                <td class="  px-6 py-4 whitespace-nowrap text-sm text-norma-gray-500">
                  <x-ui.timestamp type="datetime" :timestamp="$activity->created_at" />
                </td>
              </tr>
            @endforeach

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
