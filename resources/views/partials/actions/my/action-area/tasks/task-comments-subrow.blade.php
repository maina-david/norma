<x-ui.tabs x-cloak>
  <x-slot name="nav">
    @ifOrgHasModule('comments')
    <x-ui.tab-nav name="comments">
      <x-ui.icon name="comments" class="mr-2"/>
      {{ __('comments.comments') }}
    </x-ui.tab-nav>
    @endifOrgHasModule

    <x-ui.tab-nav name="activities">
      <x-ui.icon name="analytics" class="mr-2"/>
      {{ __('tasks.activity') }}
    </x-ui.tab-nav>

    <x-ui.tab-nav name="reminders">
      <x-ui.icon name="alarm-clock" class="mr-2"/>
      {{ __('reminders.reminders') }}
    </x-ui.tab-nav>

  </x-slot>


  @ifOrgHasModule('comments')
  <x-ui.tab-content name="comments">
    <div class="px-4 py-8">
      <turbo-frame
        wire:key="{{ Str::random() }}"
        src="{{ route('my.comments.for.commentable', ['type' => 'task', 'id' => $row->id]) }}"
        loading="lazy"
        id="comments-for-task-{{ $row->id }}"
      >
        <x-ui.skeleton/>
      </turbo-frame>
    </div>
  </x-ui.tab-content>
  @endifOrgHasModule

  <x-ui.tab-content name="reminders">
    <div class="px-4 py-8">
      <turbo-frame
        wire:key="{{ Str::random() }}"
        loading="lazy"
        src="{{ route('my.notify.reminders.for.related.index', ['relation' => 'task', 'id' => $row->id]) }}"
        id="reminders-for-task-{{ $row->id }}"
      >
        <x-ui.skeleton/>
      </turbo-frame>
    </div>
  </x-ui.tab-content>

  <x-ui.tab-content name="activities">
    <div class="px-4 py-8">
      <turbo-frame
        wire:key="{{ Str::random() }}"
        loading="lazy"
        src="{{ route('my.tasks.task-activities.index', ['task' => $row]) }}"
        id="activities-for-task-{{ $row->id }}"
      >
        <x-ui.skeleton/>
      </turbo-frame>
    </div>
  </x-ui.tab-content>
</x-ui.tabs>
