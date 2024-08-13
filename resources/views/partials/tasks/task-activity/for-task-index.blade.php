<div>
  <ul role="list" class="divide-y divide-libryo-gray-200">
    @forelse ($activities as $activity)
      <li class="py-4">
        <div class="flex space-x-3">
          @if ($activity->user)
            <div>
              <x-ui.user-avatar :user="$activity->user" />
            </div>

          @endif
          <div class="flex-1 space-y-1">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium">{{ $activity->user?->fullName ?? '' }}</h3>
              <p class="text-sm text-libryo-gray-500">
                <x-ui.timestamp type="diff" :timestamp="$activity->created_at" />
              </p>
            </div>
            <div>
              <p class="text-sm text-libryo-gray-500">
                <span class="inline-block mr-4">
                  <x-tasks.task-activity.activity-icon :activity-type="$activity->activity_type" />
                </span>
                {{ $activity->toText() }}
              </p>
            </div>

          </div>
        </div>
      </li>
    @empty
      <x-ui.empty-state-icon icon="analytics" :title="__('interface.activity_no_activities')"
                             :subline="__('interface.when_something_happens')" />
    @endforelse

  </ul>

  {{ $activities->links() }}
</div>
