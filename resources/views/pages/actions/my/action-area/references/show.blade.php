<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.back-referrer-button fallback="{{ route('my.actions.action-areas.subject.index') }}"/>

      <x-ui.icon name="clipboard-list" class="mr-3 ml-5 text-libryo-gray-400" size="8" />
      <div>
        {{ $reference->refPlainText->plain_text ?? '' }}
      </div>
    </div>
  </x-slot>


  <div class="px-2">
    <h2 class="font-semibold mb-8">{{ __('corpus.reference.associated_tasks') }}</h2>

    <livewire:actions.action-areas.tasks-for-action-areas
      lazy
      sort="title"
      :visible="['title',  'impact', 'priority', 'created_at', 'due_on', 'assignee', 'task_status']"
      :reference-id="$reference->id"
    />
  </div>

  <div class="px-2 mt-10">
    <h2 class="font-semibold mb-8">{{ __('requirements.requirement_details') }}</h2>

    <livewire:actions.action-areas.references-for-action-areas
      wire:key="references"
      lazy
      sort="title"
      :reference-id="$reference->id"
    />
  </div>

</x-layouts.app>
