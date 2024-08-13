<div>
  <div class="">
    <x-ui.modal>
      <x-slot name="trigger">
        <div class="grid justify-items-end mb-5">
          <x-ui.button theme="primary" @click="open = true">
            <x-ui.icon name="plus" size="3" class="mr-3" />
            {{ __('notify.reminder.create_reminder') }}
          </x-ui.button>
        </div>
      </x-slot>

      <turbo-frame src="{{ route('my.notify.reminders.for.related.create', [
          'relation' => $relation,
          'id' => $related->id,
      ]) }}"
                   loading="lazy"
                   id="create-reminder-for-related-{{ $relation }}-{{ $related->id }}">
        <div class="w-96">
          <x-ui.skeleton :rows="4" />
        </div>
      </turbo-frame>
    </x-ui.modal>
  </div>

  @if ($reminders->count() === 0)
    <x-ui.empty-state-icon icon="alarm-clock"
                           :title="__('notify.reminder.no_reminders_added')"
                           :subline="__('interface.when_something_happens')" />
  @else
    <ul role="list" class="divide-y divide-libryo-gray-200">
      @foreach ($reminders as $reminder)
        <li>
          <div class="block bg-white">
            <div class="px-4 py-4 flex items-center sm:px-6">
              <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
                {{-- <div class="grow">
                  <div class="flex text-sm">
                    <p class="font-medium truncate">{{ $reminder->title }}</p>
                  </div>
                  <div class="mt-2 flex">
                    <div class="flex items-center text-sm text-libryo-gray-500">
                      {{ $reminder->description }}
                    </div>
                  </div>
                </div> --}}
                <div class="mt-4 shrink-0 sm:mt-0 sm:ml-5">
                  <div class="text-libryo-gray-500">
                    <x-ui.timestamp :timestamp="$reminder->remind_on" />
                  </div>
                </div>
                <div class="shrink-0 ml-5">
                  <x-ui.confirm-modal :route="route('my.notify.reminders.destroy', ['reminder' => $reminder])">
                    <x-ui.icon name="trash-alt" class="cursor-pointer" />
                  </x-ui.confirm-modal>
                </div>
              </div>
            </div>
          </div>
        </li>
      @endforeach
    </ul>

  @endif
</div>
